<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Emergency Alert Management & Broadcast Center
 */

$pageTitle = 'Emergency Broadcast System';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();
$errors = [];

// Handle Publish Alert Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'publish_alert' || $action === 'quick_publish') {
            $title          = clean_input($_POST['title'] ?? '');
            $message        = clean_input($_POST['message'] ?? '');
            $emergencyType  = clean_input($_POST['emergency_type'] ?? 'General Emergency');
            $severity       = clean_input($_POST['severity'] ?? 'High');
            $location       = clean_input($_POST['location'] ?? 'Federal Polytechnic Ilaro Campus');
            $targetAudience = clean_input($_POST['target_audience'] ?? 'Everyone');
            $expiryHours    = (int)($_POST['expiry_hours'] ?? 6);

            if (empty($title) || empty($message)) {
                $errors[] = 'Alert Title and Instructional Message are required.';
            } else {
                $expiryTime = date('Y-m-d H:i:s', strtotime("+$expiryHours hours"));

                $stmt = $pdo->prepare("
                    INSERT INTO alerts (
                        title, message, emergency_type, severity, location, 
                        target_audience, is_active, created_by, start_time, expiry_time, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW(), ?, NOW())
                ");
                $stmt->execute([
                    $title, $message, $emergencyType, $severity, $location, 
                    $targetAudience, $user['id'], $expiryTime
                ]);

                // Create in-app notifications for users in the target group
                $targetRoleCondition = "";
                if ($targetAudience === 'Students') {
                    $targetRoleCondition = "WHERE role = 'student'";
                } elseif ($targetAudience === 'Staff') {
                    $targetRoleCondition = "WHERE role = 'staff'";
                }
                $usersToNotify = $pdo->query("SELECT id FROM users $targetRoleCondition")->fetchAll(PDO::FETCH_COLUMN);

                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'alert', 'student/alerts.php')");
                foreach ($usersToNotify as $uid) {
                    $notifStmt->execute([$uid, "URGENT ALERT: $title", $message]);
                }

                log_activity('ALERT_BROADCAST', "Broadcasted alert '$title' ($severity - $targetAudience)", $user['id']);
                set_flash('success', "Campus Emergency Alert '$title' broadcasted successfully.");
                header('Location: ' . BASE_URL . '/admin/alerts.php');
                exit;
            }
        } elseif ($action === 'toggle_status') {
            $alertId = (int)($_POST['alert_id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE alerts SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$alertId]);
            set_flash('info', 'Alert status updated.');
            header('Location: ' . BASE_URL . '/admin/alerts.php');
            exit;
        } elseif ($action === 'delete_alert') {
            $alertId = (int)($_POST['alert_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM alerts WHERE id = ?");
            $stmt->execute([$alertId]);
            log_activity('ALERT_DELETED', "Deleted alert id $alertId", $user['id']);
            set_flash('info', 'Emergency alert removed.');
            header('Location: ' . BASE_URL . '/admin/alerts.php');
            exit;
        }
    }
}

// Fetch all alerts
$alertsStmt = $pdo->query("
    SELECT a.*, u.name as publisher_name 
    FROM alerts a 
    LEFT JOIN users u ON a.created_by = u.id 
    ORDER BY a.is_active DESC, a.created_at DESC
");
$allAlerts = $alertsStmt->fetchAll();
?>

<div class="space-y-8">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Emergency Alert Broadcast Center</h2>
            <p class="text-xs text-slate-500 mt-0.5">Author authoritative crisis advisories, manage active broadcasts, and warn campus occupants</p>
        </div>
        <a href="#create-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow transition">
            <i class="fa-solid fa-plus"></i> Create New Broadcast
        </a>
    </div>

    <!-- Error notices -->
    <?php if (!empty($errors)): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
        <?php foreach ($errors as $err): ?>
            <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-rose-600"></i> <?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Broadcast Creator Form Card -->
    <div id="create-form" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-tower-broadcast text-rose-600"></i>
                Compose Campus Emergency Broadcast
            </h3>
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                High Priority Channel
            </span>
        </div>

        <form action="<?= BASE_URL ?>/admin/alerts.php" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="publish_alert">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Alert Title -->
                <div class="sm:col-span-2">
                    <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Alert Title / Headline <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required placeholder="e.g. CHEMICAL SPILL ALERT: SCIENCE LAB BLOCK C VACATION ORDER" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-bold text-slate-900">
                </div>

                <!-- Emergency Category -->
                <div>
                    <label for="emergency_type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Incident Category
                    </label>
                    <select id="emergency_type" name="emergency_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                        <option value="Fire Hazard">Fire Hazard</option>
                        <option value="Medical Health Notice">Medical Health Notice</option>
                        <option value="Security Threat / Lockdown">Security Threat / Lockdown</option>
                        <option value="Severe Weather & Storm">Severe Weather &amp; Storm</option>
                        <option value="Gas / Chemical Fume">Gas / Chemical Fume</option>
                        <option value="Hostel & Residential">Hostel &amp; Residential</option>
                        <option value="Campus Gate Traffic Closure">Campus Gate Traffic Closure</option>
                        <option value="General Safety Advisory">General Safety Advisory</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Severity -->
                <div>
                    <label for="severity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Alert Severity Level
                    </label>
                    <select id="severity" name="severity" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-bold text-slate-800">
                        <option value="Critical">CRITICAL (Flashing Red Banner)</option>
                        <option value="High" selected>HIGH (Urgent Warning)</option>
                        <option value="Medium">MEDIUM (Caution Advisory)</option>
                        <option value="Low">LOW (Informational Notice)</option>
                    </select>
                </div>

                <!-- Target Audience -->
                <div>
                    <label for="target_audience" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Target Audience
                    </label>
                    <select id="target_audience" name="target_audience" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                        <option value="Everyone">Everyone (Public &amp; Campus Wide)</option>
                        <option value="Students">Students Only</option>
                        <option value="Staff">Academic &amp; Non-Teaching Staff</option>
                    </select>
                </div>

                <!-- Expiry Duration -->
                <div>
                    <label for="expiry_hours" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Broadcast Active Duration
                    </label>
                    <select id="expiry_hours" name="expiry_hours" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                        <option value="2">2 Hours</option>
                        <option value="6" selected>6 Hours</option>
                        <option value="12">12 Hours</option>
                        <option value="24">24 Hours (Full Day)</option>
                        <option value="48">48 Hours (2 Days)</option>
                    </select>
                </div>
            </div>

            <!-- Affected Location -->
            <div>
                <label for="location" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Affected Campus Location / Zone <span class="text-red-500">*</span>
                </label>
                <input type="text" id="location" name="location" required placeholder="e.g. Science Complex (Block B & C), East Gate Corridor" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
            </div>

            <!-- Instruction Message -->
            <div>
                <label for="message" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                    Instructional Emergency Message <span class="text-red-500">*</span>
                </label>
                <textarea id="message" name="message" rows="3" required placeholder="State clear, actionable instructions: e.g. Students and staff are advised to avoid the Science Laboratory area until further notice. Do not attempt to retrieve personal belongings..." class="w-full p-3.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs sm:text-sm font-medium text-slate-800 leading-relaxed"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full sm:w-auto py-3.5 px-8 rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-red-700 hover:from-red-500 hover:to-rose-600 text-white font-extrabold text-sm shadow-md transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-tower-broadcast text-amber-300"></i>
                    <span>PUBLISH EMERGENCY BROADCAST</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active & Archived Broadcasts List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-7 space-y-4">
        <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">All Published Broadcasts</h3>
                <p class="text-xs text-slate-500">Manage active vs archived emergency announcements</p>
            </div>
            <span class="text-xs font-bold text-slate-400 font-mono"><?= count($allAlerts) ?> Total</span>
        </div>

        <?php if (!empty($allAlerts)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Title &amp; Type</th>
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Audience</th>
                        <th class="py-3 px-4">Broadcast Status</th>
                        <th class="py-3 px-4">Published</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($allAlerts as $alt): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 max-w-[280px]">
                            <span class="font-bold text-slate-900 block leading-snug"><?= e($alt['title']) ?></span>
                            <span class="text-[10px] text-fpi-800 font-semibold"><?= e($alt['emergency_type']) ?></span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <?= get_severity_badge($alt['severity']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-[150px] truncate">
                            <?= e($alt['location']) ?>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-700">
                            <?= e($alt['target_audience']) ?>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <?php if ($alt['is_active']): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    BROADCASTING
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    INACTIVE / ARCHIVED
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 whitespace-nowrap">
                            <?= time_ago($alt['created_at']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-2">
                            <!-- Toggle Active/Inactive -->
                            <form action="<?= BASE_URL ?>/admin/alerts.php" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="alert_id" value="<?= $alt['id'] ?>">
                                <button type="submit" class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 text-xs transition" title="<?= $alt['is_active'] ? 'Deactivate Broadcast' : 'Activate Broadcast' ?>">
                                    <i class="fa-solid <?= $alt['is_active'] ? 'fa-toggle-on text-emerald-600 text-base' : 'fa-toggle-off text-slate-400 text-base' ?>"></i>
                                </button>
                            </form>

                            <!-- Delete -->
                            <form action="<?= BASE_URL ?>/admin/alerts.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this broadcast?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_alert">
                                <input type="hidden" name="alert_id" value="<?= $alt['id'] ?>">
                                <button type="submit" class="p-2 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 text-xs transition" title="Delete Broadcast">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="py-12 text-center text-slate-400 text-xs">
            No broadcast alerts published yet.
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
