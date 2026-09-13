<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Detailed Incident Investigation & Triage View
 */

$pageTitle = 'Incident Triage & Investigation';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();
$reportId = (int)($_GET['id'] ?? 0);

if (!$reportId) {
    set_flash('error', 'Invalid report identifier.');
    header('Location: ' . BASE_URL . '/admin/reports.php');
    exit;
}

// Handle administrative updates (Status, Assignment, Notes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_report') {
    if (validate_csrf()) {
        $newStatus  = clean_input($_POST['status'] ?? '');
        $assignedTo = clean_input($_POST['assigned_to'] ?? '');
        $adminNotes = clean_input($_POST['admin_notes'] ?? '');
        $notifyUser = isset($_POST['notify_reporter']) ? 1 : 0;

        // Fetch current report state
        $currStmt = $pdo->prepare("SELECT * FROM emergency_reports WHERE id = ?");
        $currStmt->execute([$reportId]);
        $current = $currStmt->fetch();

        if ($current) {
            $statusFrom = $current['status'];
            $resolvedAt = ($newStatus === 'Resolved') ? date('Y-m-d H:i:s') : $current['resolved_at'];

            // Update main report
            $upStmt = $pdo->prepare("
                UPDATE emergency_reports 
                SET status = ?, assigned_to = ?, admin_notes = ?, resolved_at = ? 
                WHERE id = ?
            ");
            $upStmt->execute([$newStatus, $assignedTo, $adminNotes, $resolvedAt, $reportId]);

            // Append to report timeline if status changed or notes added
            $timelineText = "Status changed from '$statusFrom' to '$newStatus'.";
            if (!empty($assignedTo) && $assignedTo !== $current['assigned_to']) {
                $timelineText .= " Assigned to: $assignedTo.";
            }
            if (!empty($adminNotes)) {
                $timelineText .= " Remarks: $adminNotes";
            }

            $tlStmt = $pdo->prepare("
                INSERT INTO report_timeline (report_id, action_by, status_from, status_to, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $tlStmt->execute([$reportId, $user['id'], $statusFrom, $newStatus, $timelineText]);

            // Notify reporter if user exists and option checked
            if ($current['user_id'] && $notifyUser) {
                $notifType = ($newStatus === 'Resolved') ? 'success' : (($newStatus === 'Rejected') ? 'warning' : 'status_update');
                send_notification(
                    $current['user_id'],
                    "Status Update: {$current['report_reference']}",
                    "Your reported incident ({$current['emergency_type']}) has been updated to: $newStatus. Assigned: $assignedTo.",
                    $notifType,
                    "student/my-reports.php?ref={$current['report_reference']}"
                );
            }

            log_activity('ADMIN_TRIAGE_UPDATE', "Updated report {$current['report_reference']} to $newStatus", $user['id']);
            set_flash('success', "Incident {$current['report_reference']} triage updated successfully.");
            header('Location: ' . BASE_URL . "/admin/report-view.php?id=$reportId");
            exit;
        }
    }
}

// Fetch complete report details
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.name as reporter_name, 
           u.email as reporter_email, 
           u.role as reporter_role, 
           u.department as reporter_dept, 
           u.id_number as reporter_id_num 
    FROM emergency_reports r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    set_flash('error', 'Incident report not found.');
    header('Location: ' . BASE_URL . '/admin/reports.php');
    exit;
}

// Fetch timeline events
$tlStmt = $pdo->prepare("
    SELECT t.*, u.name as actor_name, u.role as actor_role 
    FROM report_timeline t 
    LEFT JOIN users u ON t.action_by = u.id 
    WHERE t.report_id = ? 
    ORDER BY t.created_at ASC
");
$tlStmt->execute([$reportId]);
$timeline = $tlStmt->fetchAll();

// Predefined Campus Response Squads for easy assignment
$responseSquads = [
    'Campus Security Squad Alpha (Rapid Patrol)',
    'Campus Security Squad Bravo (Hostel Security)',
    'Campus Health Centre Ambulance Crew',
    'FPI Fire Safety Marshals',
    'Ogun State Fire Service (Ilaro Command)',
    'Nigeria Police Force (Ilaro Divisional Hq)',
    'Directorate of Works (Electrical Unit)',
    'Directorate of Works (Plumbing & Water)',
    'Directorate of Student Affairs (DSA)',
    'Chief Security Officer (CSO Direct)'
];
?>

<div class="space-y-6">
    
    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/admin/reports.php" class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 transition" title="Back to Reports Register">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xl sm:text-2xl font-black text-slate-900"><?= e($report['report_reference']) ?></span>
                    <?= get_severity_badge($report['severity']) ?>
                    <?= get_status_badge($report['status']) ?>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Reported <?= time_ago($report['created_at']) ?> &bull; <?= date('l, M j, Y g:i A', strtotime($report['created_at'])) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="no-print inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                <i class="fa-solid fa-print"></i> Print Docket
            </button>
            <?php if ($report['latitude'] && $report['longitude']): ?>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= $report['latitude'] ?>,<?= $report['longitude'] ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> External GPS
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Cols: Incident Dossier -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. Emergency Information Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-fpi-800"></i>
                    Incident Overview &amp; Description
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Incident Type</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5 flex items-center gap-1.5">
                            <i class="<?= get_emergency_icon($report['emergency_type']) ?>"></i>
                            <?= e($report['emergency_type']) ?>
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Campus Location</span>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">
                            <?= e($report['location_name']) ?>
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block text-[10px] uppercase">Assigned Unit</span>
                        <p class="font-bold text-emerald-800 text-sm mt-0.5">
                            <?= e($report['assigned_to'] ?: 'Not Assigned Yet') ?>
                        </p>
                    </div>
                </div>

                <!-- Full Description -->
                <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase mb-1">Description Provided By Reporter</span>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 leading-relaxed text-xs sm:text-sm">
                        <?= nl2br(e($report['description'])) ?>
                    </div>
                </div>

                <!-- Attached Photo Evidence -->
                <?php if (!empty($report['image_path'])): ?>
                <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase mb-1.5">Uploaded Evidence</span>
                    <div class="relative group inline-block">
                        <img src="<?= (strpos($report['image_path'], 'data:') === 0 ? '' : BASE_URL . '/') . e($report['image_path']) ?>" alt="Evidence" class="max-h-72 rounded-2xl border border-slate-200 shadow-md object-contain cursor-zoom-in" onclick="openImageModal(this.src)">
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> Click to enlarge evidence image
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 2. Geographic Location Leaflet Map -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-map-pin text-red-600"></i>
                            Location Coordinates &amp; Campus Map
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5"><?= e($report['location_name']) ?></p>
                    </div>
                    <?php if ($report['latitude'] && $report['longitude']): ?>
                    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
                        <?= e($report['latitude']) ?>, <?= e($report['longitude']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div id="incident-map" class="h-72 rounded-2xl border border-slate-200 z-10"></div>
            </div>

            <!-- 3. Incident History Audit Timeline -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-fpi-800"></i>
                    Triage Audit Trail &amp; Milestones
                </h3>

                <?php if (!empty($timeline)): ?>
                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-300">
                    <?php foreach ($timeline as $tl): ?>
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-emerald-600 ring-4 ring-white"></div>
                        <div class="text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 text-sm"><?= e($tl['status_to']) ?></span>
                                <span class="text-[11px] text-slate-400 font-medium"><?= date('M j, Y g:i A', strtotime($tl['created_at'])) ?> (<?= time_ago($tl['created_at']) ?>)</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Action recorded by: <strong><?= e($tl['actor_name'] ?: 'System / Reporter') ?></strong> (<?= ucfirst(e($tl['actor_role'] ?: 'System')) ?>)</p>
                            <?php if (!empty($tl['notes'])): ?>
                                <div class="mt-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 leading-relaxed font-sans">
                                    <?= nl2br(e($tl['notes'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right 4 Cols: Triage Action Console & Reporter Card -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Administrative Triage Form Card -->
            <div class="bg-white rounded-3xl border-2 border-fpi-700/30 p-6 shadow-md space-y-5">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-fpi-800"></i> Administrative Action
                    </h3>
                    <span class="text-[10px] font-bold text-fpi-800 bg-emerald-50 px-2 py-0.5 rounded">Console</span>
                </div>

                <form action="<?= BASE_URL ?>/admin/report-view.php?id=<?= $report['id'] ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_report">

                    <!-- Status selector -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Update Incident Status
                        </label>
                        <select id="status" name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-bold text-slate-800">
                            <option value="Pending" <?= $report['status'] === 'Pending' ? 'selected' : '' ?>>Pending Review</option>
                            <option value="Acknowledged" <?= $report['status'] === 'Acknowledged' ? 'selected' : '' ?>>Acknowledged</option>
                            <option value="In Progress" <?= $report['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress (Dispatched)</option>
                            <option value="Resolved" <?= $report['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved (Safely Closed)</option>
                            <option value="Rejected" <?= $report['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected (False Alarm)</option>
                        </select>
                    </div>

                    <!-- Assign to unit -->
                    <div>
                        <label for="assigned_to" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Assign Response Squad
                        </label>
                        <select id="assigned_to" name="assigned_to" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                            <option value="">-- Select Response Unit --</option>
                            <?php foreach ($responseSquads as $squad): ?>
                                <option value="<?= e($squad) ?>" <?= ($report['assigned_to'] === $squad) ? 'selected' : '' ?>><?= e($squad) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Administrative notes -->
                    <div>
                        <label for="admin_notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Administrative Remarks &amp; Action Notes
                        </label>
                        <textarea id="admin_notes" name="admin_notes" rows="4" placeholder="Log dispatch time, officers assigned, actions taken, or resolution summary..." class="w-full p-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800 leading-relaxed"><?= e($report['admin_notes'] ?? '') ?></textarea>
                    </div>

                    <!-- In-app Notification option -->
                    <?php if ($report['user_id']): ?>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <label class="cursor-pointer flex items-center gap-2.5 text-xs font-medium text-slate-700">
                            <input type="checkbox" name="notify_reporter" value="1" checked class="rounded text-fpi-800 focus:ring-fpi-700">
                            <span>Notify student/staff of this update</span>
                        </label>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> SAVE TRIAGE UPDATES
                    </button>
                </form>
            </div>

            <!-- Reporter Profile Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                        Reporter Details
                    </h3>
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded <?= $report['allow_contact'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                        <?= $report['allow_contact'] ? 'Callback Permitted' : 'Anonymous/No Contact' ?>
                    </span>
                </div>

                <?php if (!empty($report['reporter_name'])): ?>
                <div class="space-y-3 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-fpi-800 text-white font-black text-sm flex items-center justify-center flex-shrink-0">
                            <?= strtoupper(substr($report['reporter_name'], 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-slate-900 truncate"><?= e($report['reporter_name']) ?></h4>
                            <span class="text-[11px] text-slate-400 uppercase font-semibold"><?= e($report['reporter_role']) ?> &bull; <?= e($report['reporter_id_num']) ?></span>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2 text-slate-600 border-t border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Department</span>
                            <span class="font-semibold text-slate-800"><?= e($report['reporter_dept']) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Email</span>
                            <a href="mailto:<?= e($report['reporter_email']) ?>" class="font-semibold text-fpi-800 hover:underline"><?= e($report['reporter_email']) ?></a>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Phone Number</span>
                            <a href="tel:<?= str_replace(' ', '', $report['reporter_phone']) ?>" class="font-bold text-slate-900 hover:text-fpi-800 flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-phone text-emerald-600"></i> <?= e($report['reporter_phone']) ?>
                            </a>
                        </div>
                    </div>

                    <?php if ($report['allow_contact'] && !empty($report['reporter_phone'])): ?>
                    <a href="tel:<?= str_replace(' ', '', $report['reporter_phone']) ?>" class="w-full mt-2 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                        <i class="fa-solid fa-phone-volume"></i> Direct Call Reporter
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="py-4 text-center text-xs text-slate-400">
                    <p class="font-bold text-slate-600">Reported Anonymously</p>
                    <?php if (!empty($report['reporter_phone'])): ?>
                        <p class="mt-1">Provided Contact: <a href="tel:<?= str_replace(' ', '', $report['reporter_phone']) ?>" class="font-bold text-slate-900 underline"><?= e($report['reporter_phone']) ?></a></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>

<!-- Modal for image zoom -->
<div id="image-modal" class="fixed inset-0 z-50 bg-slate-950/80 hidden flex items-center justify-center p-4 backdrop-blur-xs" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-[90vh] bg-white rounded-3xl p-2 shadow-2xl" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageModal()" class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-900/80 text-white flex items-center justify-center hover:bg-slate-900 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="modal-img-tag" src="#" alt="Enlarged Evidence" class="rounded-2xl max-h-[85vh] object-contain">
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lat = <?= $report['latitude'] ? (float)$report['latitude'] : 6.8928 ?>;
    const lng = <?= $report['longitude'] ? (float)$report['longitude'] : 3.0165 ?>;
    const incidents = [<?= json_encode([
        'latitude' => $report['latitude'] ?: 6.8928,
        'longitude' => $report['longitude'] ?: 3.0165,
        'emergency_type' => $report['emergency_type'],
        'location_name' => $report['location_name'],
        'severity' => $report['severity'],
        'status' => $report['status'],
        'report_reference' => $report['report_reference']
    ]) ?>];
    initIncidentMap('incident-map', incidents, lat, lng, 17);
});

function openImageModal(src) {
    document.getElementById('modal-img-tag').src = src;
    document.getElementById('image-modal').classList.remove('hidden');
}
function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
