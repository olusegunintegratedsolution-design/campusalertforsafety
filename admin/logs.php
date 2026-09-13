<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Security & Audit Activity Logs
 */

$pageTitle = 'Security & Audit Logs';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

$actionFilter = clean_input($_GET['action_filter'] ?? '');
$sql = "SELECT * FROM system_logs WHERE 1=1";
$params = [];

if (!empty($actionFilter)) {
    $sql .= " AND action LIKE ?";
    $params[] = "%$actionFilter%";
}

$sql .= " ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Security &amp; System Audit Logs</h2>
            <p class="text-xs text-slate-500 mt-0.5">Immutable audit trail of authentication events, triage operations, and alert dispatches</p>
        </div>
        <div class="text-xs text-slate-400 font-mono bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-xs">
            Displaying last <?= count($logs) ?> entries
        </div>
    </div>

    <!-- Quick filter bar -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm">
        <form action="<?= BASE_URL ?>/admin/logs.php" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-filter text-xs"></i>
                </span>
                <input type="text" name="action_filter" value="<?= e($actionFilter) ?>" placeholder="Filter by event action (e.g. LOGIN, REPORT, ALERT, TRIAGE)..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
                Filter Logs
            </button>
            <?php if (!empty($actionFilter)): ?>
            <a href="<?= BASE_URL ?>/admin/logs.php" class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs transition" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (!empty($logs)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-5">Timestamp</th>
                        <th class="py-3.5 px-5">User Account</th>
                        <th class="py-3.5 px-5">Action Event</th>
                        <th class="py-3.5 px-5">Event Details</th>
                        <th class="py-3.5 px-5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    <?php foreach ($logs as $l): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-5 text-slate-400 whitespace-nowrap text-[11px]">
                            <?= date('Y-m-d H:i:s', strtotime($l['created_at'])) ?>
                        </td>
                        <td class="py-3.5 px-5 text-slate-800 whitespace-nowrap font-sans font-semibold">
                            <?= e($l['user_email'] ?: 'Guest / System') ?>
                        </td>
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-800 border border-slate-200">
                                <?= e($l['action']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 font-sans text-xs max-w-md">
                            <?= e($l['details']) ?>
                        </td>
                        <td class="py-3.5 px-5 text-slate-400 whitespace-nowrap text-[11px]">
                            <?= e($l['ip_address']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="py-16 text-center text-slate-400 text-xs">
            No system audit logs found.
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
