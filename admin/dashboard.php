<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Administrative Executive Dashboard
 */

$pageTitle = 'Admin Command Center';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

// 1. Executive Metrics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
$totalReports = $pdo->query("SELECT COUNT(*) FROM emergency_reports")->fetchColumn() ?: 0;
$pendingReports = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status = 'Pending'")->fetchColumn() ?: 0;
$activeEmergencies = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status IN ('Pending', 'Acknowledged', 'In Progress')")->fetchColumn() ?: 0;
$resolvedReports = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status = 'Resolved'")->fetchColumn() ?: 0;
$criticalIncidents = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE severity = 'Critical' AND status != 'Resolved'")->fetchColumn() ?: 0;

// 2. Recent Emergency Reports (latest 8)
$reportsStmt = $pdo->query("
    SELECT r.*, u.name as reporter_name, u.role as reporter_role 
    FROM emergency_reports r 
    LEFT JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC 
    LIMIT 8
");
$recentReports = $reportsStmt->fetchAll();

// 3. Active Incident Coordinates for Map
$mapIncidentsStmt = $pdo->query("
    SELECT id, report_reference, emergency_type, location_name, severity, status, latitude, longitude, created_at 
    FROM emergency_reports 
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
    ORDER BY created_at DESC 
    LIMIT 20
");
$mapIncidents = $mapIncidentsStmt->fetchAll();
foreach ($mapIncidents as &$mi) {
    $mi['view_url'] = BASE_URL . '/admin/report-view.php?id=' . $mi['id'];
}
unset($mi);
?>

<div class="space-y-8">
    
    <!-- Admin Top Banner & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Security Operations Active</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-0.5">
                Campus Safety Command Center
            </h2>
            <p class="text-xs text-slate-500">Real-time incident response, live monitoring, and emergency dispatch console</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/admin/alerts.php?action=create" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-xs transition">
                <i class="fa-solid fa-bullhorn"></i> Broadcast Alert
            </a>
            <a href="<?= BASE_URL ?>/admin/map.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
                <i class="fa-solid fa-map-location-dot text-amber-400"></i> Full Incident Map
            </a>
        </div>
    </div>

    <!-- Critical Incidents Alert Notice (if any) -->
    <?php if ($criticalIncidents > 0): ?>
    <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 flex items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg animate-pulse flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="text-xs sm:text-sm font-black text-rose-900">
                    <?= $criticalIncidents ?> Active Critical <?= $criticalIncidents > 1 ? 'Incidents' : 'Incident' ?> Require Immediate Attention!
                </h4>
                <p class="text-[11px] text-rose-700 mt-0.5">Dispatch security or medical response units to the affected campus locations.</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/admin/reports.php?severity=Critical" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs whitespace-nowrap shadow-xs transition">
            View Critical &rarr;
        </a>
    </div>
    <?php endif; ?>

    <!-- 6 Executive KPI Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Total Users -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Total Users</span>
                <i class="fa-solid fa-users text-sm text-sky-500"></i>
            </div>
            <div class="text-2xl font-black text-slate-900"><?= number_format($totalUsers) ?></div>
            <span class="text-[10px] text-slate-400">Students &amp; staff</span>
        </div>

        <!-- Total Reports -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">All Reports</span>
                <i class="fa-solid fa-clipboard-list text-sm text-fpi-700"></i>
            </div>
            <div class="text-2xl font-black text-slate-900"><?= number_format($totalReports) ?></div>
            <span class="text-[10px] text-slate-400">Total logged</span>
        </div>

        <!-- Pending Triage -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Pending</span>
                <i class="fa-solid fa-clock text-sm text-amber-500"></i>
            </div>
            <div class="text-2xl font-black text-amber-600"><?= number_format($pendingReports) ?></div>
            <span class="text-[10px] text-amber-600 font-semibold">Awaiting review</span>
        </div>

        <!-- Active Emergencies -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Active</span>
                <i class="fa-solid fa-spinner text-sm text-orange-500"></i>
            </div>
            <div class="text-2xl font-black text-orange-600"><?= number_format($activeEmergencies) ?></div>
            <span class="text-[10px] text-orange-600 font-semibold">Under response</span>
        </div>

        <!-- Resolved -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Resolved</span>
                <i class="fa-solid fa-circle-check text-sm text-emerald-500"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600"><?= number_format($resolvedReports) ?></div>
            <span class="text-[10px] text-emerald-600 font-semibold">Successfully closed</span>
        </div>

        <!-- Critical -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Critical</span>
                <i class="fa-solid fa-triangle-exclamation text-sm text-rose-500"></i>
            </div>
            <div class="text-2xl font-black text-rose-600"><?= number_format($criticalIncidents) ?></div>
            <span class="text-[10px] text-rose-600 font-semibold">Life hazard</span>
        </div>

    </div>

    <!-- Interactive Live Mini Map & Quick Summary Split -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Live Mini Map -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Live Campus Incident GIS Map</h3>
                    <p class="text-xs text-slate-500">Real-time incident distribution across Federal Polytechnic Ilaro landmarks</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/map.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900 flex items-center gap-1">
                    Fullscreen Map &rarr;
                </a>
            </div>

            <!-- Map Container -->
            <div id="admin-dashboard-map" class="h-80 rounded-2xl border border-slate-200 z-10"></div>
            
            <!-- Marker Legend -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs pt-1 border-t border-slate-100">
                <div class="flex items-center gap-4 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-pulse"></span> Critical</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> High</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Medium</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Low</span>
                </div>
                <span class="text-[11px] text-slate-400 font-mono">Showing <?= count($mapIncidents) ?> incidents</span>
            </div>
        </div>

        <!-- Quick Emergency Broadcast Card -->
        <div class="lg:col-span-4 bg-gradient-to-br from-fpi-900 to-emerald-950 text-white rounded-3xl p-6 shadow-md space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-900">
                <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
                    <i class="fa-solid fa-tower-broadcast"></i> Quick Broadcast
                </h3>
                <span class="text-[10px] bg-emerald-800 text-emerald-200 px-2 py-0.5 rounded font-bold">Instant SMS &amp; Portal</span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Publishing an alert immediately displays a high-priority warning banner on all student and staff dashboards.
            </p>

            <form action="<?= BASE_URL ?>/admin/alerts.php" method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="quick_publish">

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Alert Headline</label>
                    <input type="text" name="title" required placeholder="e.g. LAB CORRIDOR EVACUATION" class="w-full px-3 py-2 rounded-xl bg-emerald-950/80 border border-emerald-800 text-xs text-white focus:outline-none focus:ring-1 focus:ring-amber-400">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Severity</label>
                        <select name="severity" class="w-full px-2.5 py-2 rounded-xl bg-emerald-950/80 border border-emerald-800 text-xs text-white focus:outline-none">
                            <option value="Critical">Critical</option>
                            <option value="High" selected>High</option>
                            <option value="Medium">Medium</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Target</label>
                        <select name="target_audience" class="w-full px-2.5 py-2 rounded-xl bg-emerald-950/80 border border-emerald-800 text-xs text-white focus:outline-none">
                            <option value="Everyone">Everyone</option>
                            <option value="Students">Students Only</option>
                            <option value="Staff">Staff Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Campus Location</label>
                    <input type="text" name="location" required placeholder="e.g. Science Complex Block B" class="w-full px-3 py-2 rounded-xl bg-emerald-950/80 border border-emerald-800 text-xs text-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Instruction Message</label>
                    <textarea name="message" rows="2" required placeholder="Safety directives to students..." class="w-full p-2.5 rounded-xl bg-emerald-950/80 border border-emerald-800 text-xs text-white focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow transition">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> DISPATCH BROADCAST NOW
                </button>
            </form>
        </div>

    </div>

    <!-- Recent Emergency Reports Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4 p-6 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Recent Incident Reports</h3>
                <p class="text-xs text-slate-500">Live feed of emergencies logged across campus</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/reports.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900 flex items-center gap-1">
                <span>View Full Reports Register</span> &rarr;
            </a>
        </div>

        <?php if (!empty($recentReports)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Report ID</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Severity</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Reported</th>
                        <th class="py-3 px-4 text-right">Triage Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentReports as $rep): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                            <a href="<?= BASE_URL ?>/admin/report-view.php?id=<?= $rep['id'] ?>" class="text-fpi-800 hover:underline">
                                <?= e($rep['report_reference']) ?>
                            </a>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            <i class="<?= get_emergency_icon($rep['emergency_type']) ?> mr-1"></i>
                            <?= e($rep['emergency_type']) ?>
                        </td>
                        <td class="py-3 px-4 text-slate-600 max-w-[180px] truncate">
                            <?= e($rep['location_name']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <?= get_severity_badge($rep['severity']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <?= get_status_badge($rep['status']) ?>
                        </td>
                        <td class="py-3 px-4 text-slate-400">
                            <?= time_ago($rep['created_at']) ?>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="<?= BASE_URL ?>/admin/report-view.php?id=<?= $rep['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs transition shadow-xs">
                                <i class="fa-solid fa-sliders text-[10px]"></i> Triage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="py-12 text-center text-slate-400">
            <p class="text-sm font-semibold text-slate-700">No emergency reports found.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Pass incidents to JavaScript for Leaflet mini-map -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const incidents = <?= json_encode($mapIncidents) ?>;
    initIncidentMap('admin-dashboard-map', incidents, 6.8928, 3.0165, 16);
});
</script>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
