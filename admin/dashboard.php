<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Administrator Command Center Dashboard
 */

$pageTitle = 'Admin Command Center';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalStaff = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'staff'")->fetchColumn();
$totalReports = (int)$pdo->query("SELECT COUNT(*) FROM emergency_reports")->fetchColumn();
$pendingReports = (int)$pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status = 'Pending'")->fetchColumn();
$activeAlerts = (int)$pdo->query("SELECT COUNT(*) FROM alerts WHERE is_active = 1")->fetchColumn();
$criticalReports = (int)$pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE severity = 'Critical' AND status NOT IN ('Resolved','Rejected')")->fetchColumn();

$recentReports = $pdo->query("
    SELECT r.id, r.report_reference, r.emergency_type, r.severity,
           r.location_name, r.status, r.created_at,
           COALESCE(u.name, 'Anonymous / Guest') AS reporter_name
    FROM emergency_reports r
    LEFT JOIN users u ON u.id = r.user_id
    ORDER BY r.created_at DESC
    LIMIT 8
")->fetchAll();

$recentLogs = $pdo->query("
    SELECT user_email, action, details, created_at
    FROM system_logs
    ORDER BY created_at DESC
    LIMIT 6
")->fetchAll();

$activeAlertRows = $pdo->query("
    SELECT title, emergency_type, severity, location, start_time, expiry_time
    FROM alerts
    WHERE is_active = 1
    ORDER BY start_time DESC
    LIMIT 5
")->fetchAll();
?>

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-emerald-700">Administrator Command Center</p>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">System Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor campus safety activity, incidents, users and active alerts.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/admin/reports.php"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-fpi-800 text-white text-xs font-bold hover:bg-fpi-900 transition">
                <i class="fa-solid fa-list-check"></i> Review Reports
            </a>
            <a href="<?= BASE_URL ?>/admin/alerts.php"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 text-white text-xs font-bold hover:bg-amber-600 transition">
                <i class="fa-solid fa-tower-broadcast"></i> Broadcast Alert
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <a href="<?= BASE_URL ?>/admin/users.php" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Users</p>
                    <p class="text-3xl font-black text-slate-900 mt-2"><?= number_format($totalUsers) ?></p>
                    <p class="text-xs text-slate-500 mt-1"><?= number_format($totalStudents) ?> students · <?= number_format($totalStaff) ?> staff</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/reports.php" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Emergency Reports</p>
                    <p class="text-3xl font-black text-slate-900 mt-2"><?= number_format($totalReports) ?></p>
                    <p class="text-xs text-amber-700 mt-1"><?= number_format($pendingReports) ?> pending review</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/alerts.php" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active Alerts</p>
                    <p class="text-3xl font-black text-slate-900 mt-2"><?= number_format($activeAlerts) ?></p>
                    <p class="text-xs text-slate-500 mt-1">Currently broadcasting</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>/admin/reports.php?severity=Critical" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Critical Open Cases</p>
                    <p class="text-3xl font-black text-rose-700 mt-2"><?= number_format($criticalReports) ?></p>
                    <p class="text-xs text-slate-500 mt-1">Unresolved critical incidents</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <section class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-black text-slate-900">Recent Emergency Reports</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Latest incidents submitted to the system.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/reports.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900">View all →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-5 py-3">Reference</th>
                            <th class="px-5 py-3">Incident</th>
                            <th class="px-5 py-3">Location</th>
                            <th class="px-5 py-3">Severity</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if (!$recentReports): ?>
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No emergency reports yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentReports as $report): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3">
                                    <a href="<?= BASE_URL ?>/admin/report-view.php?id=<?= (int)$report['id'] ?>" class="font-bold text-fpi-800">
                                        <?= e($report['report_reference']) ?>
                                    </a>
                                    <div class="text-[10px] text-slate-400 mt-0.5"><?= e($report['reporter_name']) ?></div>
                                </td>
                                <td class="px-5 py-3 font-semibold text-slate-700"><?= e($report['emergency_type']) ?></td>
                                <td class="px-5 py-3 text-slate-500"><?= e($report['location_name']) ?></td>
                                <td class="px-5 py-3"><?= get_severity_badge($report['severity']) ?></td>
                                <td class="px-5 py-3"><?= get_status_badge($report['status']) ?></td>
                                <td class="px-5 py-3 text-slate-400 whitespace-nowrap"><?= e(date('M j, H:i', strtotime($report['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-black text-slate-900">Active Broadcasts</h2>
                <p class="text-xs text-slate-500 mt-0.5">Alerts currently visible to users.</p>
            </div>
            <div class="p-4 space-y-3">
                <?php if (!$activeAlertRows): ?>
                    <div class="py-8 text-center text-slate-400 text-xs">No active alerts.</div>
                <?php else: ?>
                    <?php foreach ($activeAlertRows as $alert): ?>
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-xs font-black text-slate-800"><?= e($alert['title']) ?></h3>
                                <?= get_severity_badge($alert['severity']) ?>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2"><?= e($alert['emergency_type']) ?> · <?= e($alert['location']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-black text-slate-900">Recent System Activity</h2>
                <p class="text-xs text-slate-500 mt-0.5">Security and operational events recorded by the system.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/logs.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900">Open audit logs →</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (!$recentLogs): ?>
                <div class="px-5 py-8 text-center text-slate-400 text-xs">No activity has been recorded yet.</div>
            <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                    <div class="px-5 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700"><?= e($log['action']) ?></p>
                            <p class="text-[11px] text-slate-500 truncate"><?= e($log['details'] ?? '') ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] font-semibold text-slate-500"><?= e($log['user_email'] ?? 'System') ?></p>
                            <p class="text-[10px] text-slate-400"><?= e(date('M j, Y H:i', strtotime($log['created_at']))) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
