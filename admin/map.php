<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Full Interactive Campus Incident Map (Leaflet.js)
 */

$pageTitle = 'Campus Incident Map';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

$filter = clean_input($_GET['filter'] ?? 'all');

$sql = "
    SELECT id, report_reference, emergency_type, location_name, severity, status, latitude, longitude, created_at, description 
    FROM emergency_reports 
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
";
$params = [];

if ($filter === 'active') {
    $sql .= " AND status IN ('Pending', 'Acknowledged', 'In Progress')";
} elseif ($filter === 'critical') {
    $sql .= " AND severity = 'Critical' AND status != 'Resolved'";
} elseif ($filter === 'high') {
    $sql .= " AND severity = 'High' AND status != 'Resolved'";
} elseif ($filter === 'resolved') {
    $sql .= " AND status = 'Resolved'";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$incidents = $stmt->fetchAll();

foreach ($incidents as &$inc) {
    $inc['view_url'] = BASE_URL . '/admin/report-view.php?id=' . $inc['id'];
}
unset($inc);
?>

<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Campus Emergency Incident Map</h2>
            <p class="text-xs text-slate-500 mt-0.5">Geographic visualization of emergency alerts and incidents across Federal Polytechnic Ilaro</p>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-wrap gap-2 p-1 bg-white rounded-2xl border border-slate-200 shadow-xs">
            <a href="<?= BASE_URL ?>/admin/map.php?filter=all" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $filter === 'all' ? 'bg-fpi-800 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                All Incidents
            </a>
            <a href="<?= BASE_URL ?>/admin/map.php?filter=active" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $filter === 'active' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Active Only
            </a>
            <a href="<?= BASE_URL ?>/admin/map.php?filter=critical" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $filter === 'critical' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Critical
            </a>
            <a href="<?= BASE_URL ?>/admin/map.php?filter=high" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $filter === 'high' ? 'bg-orange-500 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                High Severity
            </a>
            <a href="<?= BASE_URL ?>/admin/map.php?filter=resolved" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $filter === 'resolved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                Resolved
            </a>
        </div>
    </div>

    <!-- Map & Incident Sidebar Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Large Interactive Map -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-4 shadow-sm space-y-3">
            <div id="full-incident-map" class="h-[600px] w-full rounded-2xl z-10"></div>
            
            <!-- Map Legend -->
            <div class="flex flex-wrap items-center justify-between gap-4 p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                <div class="flex items-center gap-4 text-[11px] font-semibold text-slate-700">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-600 animate-pulse"></span> Critical</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-500"></span> High</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Medium</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Low</span>
                </div>
                <div class="text-[11px] text-slate-500 font-mono">
                    Center: 6.89280 N, 3.01650 E (FPI Main Campus)
                </div>
            </div>
        </div>

        <!-- Right Side: Plotted Incidents Feed -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Plotted Incidents (<?= count($incidents) ?>)
                </h3>
                <span class="text-[11px] text-slate-400">Click to pan map</span>
            </div>

            <div class="space-y-3 max-h-[530px] overflow-y-auto pr-1">
                <?php if (!empty($incidents)): ?>
                    <?php foreach ($incidents as $idx => $inc): ?>
                    <div onclick="panToIncident(<?= (float)$inc['latitude'] ?>, <?= (float)$inc['longitude'] ?>, <?= $idx ?>)" class="p-3.5 rounded-2xl border border-slate-200 hover:border-fpi-700 hover:bg-slate-50 cursor-pointer transition space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono text-xs font-bold text-slate-900"><?= e($inc['report_reference']) ?></span>
                            <?= get_severity_badge($inc['severity']) ?>
                        </div>
                        <h5 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="<?= get_emergency_icon($inc['emergency_type']) ?>"></i>
                            <?= e($inc['emergency_type']) ?>
                        </h5>
                        <p class="text-[11px] text-slate-500 line-clamp-1 flex items-center gap-1">
                            <i class="fa-solid fa-location-dot text-slate-400"></i>
                            <?= e($inc['location_name']) ?>
                        </p>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] text-slate-400">
                            <span>Status: <strong><?= e($inc['status']) ?></strong></span>
                            <span><?= time_ago($inc['created_at']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-12 text-center text-slate-400 text-xs">
                        No incidents matching current filter.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
let incidentMap = null;
let incidentMarkers = [];

document.addEventListener('DOMContentLoaded', () => {
    const data = <?= json_encode($incidents) ?>;
    const res = initIncidentMap('full-incident-map', data, 6.8928, 3.0165, 16);
    if (res) {
        incidentMap = res.map;
        incidentMarkers = res.markers;
    }
});

function panToIncident(lat, lng, index) {
    if (incidentMap) {
        incidentMap.setView([lat, lng], 18, { animate: true });
        if (incidentMarkers[index]) {
            incidentMarkers[index].openPopup();
        }
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
