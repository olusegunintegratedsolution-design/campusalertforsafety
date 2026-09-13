<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * My Incident Reports History & Progress Tracker
 */

$pageTitle = 'My Emergency Reports';
require_once __DIR__ . '/../includes/nav-portal.php';

$userId = $user['id'];
$pdo = getDB();

$filterStatus = clean_input($_GET['status'] ?? 'all');
$selectedRef = clean_input($_GET['ref'] ?? '');

$sql = "SELECT * FROM emergency_reports WHERE user_id = ?";
$params = [$userId];

if (!empty($filterStatus) && $filterStatus !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// If a reference ID is requested, fetch full details and timeline
$detailReport = null;
$timeline = [];
if (!empty($selectedRef)) {
    $detailStmt = $pdo->prepare("SELECT * FROM emergency_reports WHERE report_reference = ? AND user_id = ?");
    $detailStmt->execute([$selectedRef, $userId]);
    $detailReport = $detailStmt->fetch();

    if ($detailReport) {
        $tlStmt = $pdo->prepare("
            SELECT t.*, u.name as actor_name, u.role as actor_role 
            FROM report_timeline t 
            LEFT JOIN users u ON t.action_by = u.id 
            WHERE t.report_id = ? 
            ORDER BY t.created_at ASC
        ");
        $tlStmt->execute([$detailReport['id']]);
        $timeline = $tlStmt->fetchAll();
    }
}
?>

<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">My Emergency Reports</h2>
            <p class="text-xs text-slate-500 mt-0.5">Track the status, assignment, and resolution of incidents you reported</p>
        </div>
        <a href="<?= BASE_URL ?>/student/report-emergency.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs shadow transition">
            <i class="fa-solid fa-bolt text-amber-300"></i> New Incident Report
        </a>
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap gap-2 p-1.5 bg-white rounded-2xl border border-slate-200 shadow-xs">
        <a href="<?= BASE_URL ?>/student/my-reports.php" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $filterStatus === 'all' ? 'bg-fpi-800 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
            All Reports
        </a>
        <a href="<?= BASE_URL ?>/student/my-reports.php?status=Pending" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $filterStatus === 'Pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
            Pending Review
        </a>
        <a href="<?= BASE_URL ?>/student/my-reports.php?status=In Progress" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $filterStatus === 'In Progress' ? 'bg-orange-500 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
            In Progress
        </a>
        <a href="<?= BASE_URL ?>/student/my-reports.php?status=Resolved" class="px-4 py-2 rounded-xl text-xs font-bold transition <?= $filterStatus === 'Resolved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
            Resolved
        </a>
    </div>

    <!-- Selected Report Detail View (if ref requested) -->
    <?php if ($detailReport): ?>
    <div class="bg-white rounded-3xl border-2 border-emerald-600/30 p-6 sm:p-8 shadow-md space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <span class="text-xs font-mono font-bold text-fpi-800 uppercase tracking-wide">INCIDENT DETAILS</span>
                <h3 class="text-2xl font-black text-slate-900 mt-0.5"><?= e($detailReport['report_reference']) ?></h3>
            </div>
            <div class="flex items-center gap-3">
                <?= get_severity_badge($detailReport['severity']) ?>
                <?= get_status_badge($detailReport['status']) ?>
                <a href="<?= BASE_URL ?>/student/my-reports.php" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg text-sm" title="Close details">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Left Info -->
            <div class="space-y-4 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Incident Type</span>
                    <p class="font-bold text-slate-800 text-sm mt-0.5 flex items-center gap-2">
                        <i class="<?= get_emergency_icon($detailReport['emergency_type']) ?>"></i>
                        <?= e($detailReport['emergency_type']) ?>
                    </p>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Campus Location</span>
                    <p class="font-bold text-slate-800 text-sm mt-0.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-rose-500"></i>
                        <?= e($detailReport['location_name']) ?>
                    </p>
                    <?php if ($detailReport['latitude'] && $detailReport['longitude']): ?>
                    <p class="text-[11px] text-slate-400 font-mono mt-0.5">GPS: <?= e($detailReport['latitude']) ?>, <?= e($detailReport['longitude']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Description Given</span>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 leading-relaxed mt-1 text-xs">
                        <?= nl2br(e($detailReport['description'])) ?>
                    </div>
                </div>

                <?php if (!empty($detailReport['image_path'])): ?>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase text-[10px]">Uploaded Evidence</span>
                    <div class="mt-1">
                        <img src="<?= (strpos($detailReport['image_path'], 'data:') === 0 ? '' : BASE_URL . '/') . e($detailReport['image_path']) ?>" alt="Evidence" class="max-h-48 rounded-2xl border border-slate-200 shadow-sm object-cover">
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Response Progress Timeline -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 space-y-4">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-fpi-800"></i>
                    Response Progress Milestones
                </h4>

                <?php if (!empty($timeline)): ?>
                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-300">
                    <?php foreach ($timeline as $t): ?>
                    <div class="relative group">
                        <div class="absolute -left-[27px] top-0.5 w-3.5 h-3.5 rounded-full bg-emerald-600 ring-4 ring-white"></div>
                        <div class="text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900"><?= e($t['status_to']) ?></span>
                                <span class="text-[10px] text-slate-400"><?= time_ago($t['created_at']) ?></span>
                            </div>
                            <?php if (!empty($t['notes'])): ?>
                                <p class="text-slate-600 text-[11px] mt-1 bg-white p-2 rounded-lg border border-slate-200"><?= e($t['notes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400">Incident logged with Central Security Operations.</p>
                <?php endif; ?>

                <?php if (!empty($detailReport['admin_notes'])): ?>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                    <span class="font-bold block uppercase text-[10px]">Security Unit Remarks:</span>
                    <p><?= nl2br(e($detailReport['admin_notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- Reports Table / List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (!empty($reports)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-6">Reference ID</th>
                        <th class="py-3.5 px-6">Emergency Category</th>
                        <th class="py-3.5 px-6">Location</th>
                        <th class="py-3.5 px-6">Severity</th>
                        <th class="py-3.5 px-6">Current Status</th>
                        <th class="py-3.5 px-6">Reported Time</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($reports as $rep): ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 font-mono font-bold text-slate-900">
                            <?= e($rep['report_reference']) ?>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2 font-bold text-slate-800">
                                <i class="<?= get_emergency_icon($rep['emergency_type']) ?>"></i>
                                <span><?= e($rep['emergency_type']) ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 max-w-[200px] truncate">
                            <i class="fa-solid fa-location-dot text-slate-400 mr-1"></i>
                            <?= e($rep['location_name']) ?>
                        </td>
                        <td class="py-4 px-6">
                            <?= get_severity_badge($rep['severity']) ?>
                        </td>
                        <td class="py-4 px-6">
                            <?= get_status_badge($rep['status']) ?>
                        </td>
                        <td class="py-4 px-6 text-slate-400 font-medium">
                            <?= time_ago($rep['created_at']) ?>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="<?= BASE_URL ?>/student/my-reports.php?ref=<?= urlencode($rep['report_reference']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-fpi-800 hover:bg-emerald-100 font-bold text-xs transition">
                                <i class="fa-solid fa-eye text-[10px]"></i> View Details
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-16 text-center text-slate-400">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fa-regular fa-clipboard"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">No Emergency Reports Found</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                You have not submitted any incident reports matching this filter criteria.
            </p>
            <a href="<?= BASE_URL ?>/student/report-emergency.php" class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow transition">
                <i class="fa-solid fa-bolt"></i> Report An Incident
            </a>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
