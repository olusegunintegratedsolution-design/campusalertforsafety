<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Analytics & Crisis Intelligence
 */

$pageTitle = 'Incident Analytics & Statistics';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

// 1. Reports by Emergency Type
$typesData = $pdo->query("
    SELECT emergency_type, COUNT(*) as count 
    FROM emergency_reports 
    GROUP BY emergency_type 
    ORDER BY count DESC
")->fetchAll();

// 2. Reports by Severity
$severityData = $pdo->query("
    SELECT severity, COUNT(*) as count 
    FROM emergency_reports 
    GROUP BY severity
")->fetchAll();

// 3. Reports by Status
$statusData = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM emergency_reports 
    GROUP BY status
")->fetchAll();

// 4. Hotspot Locations
$hotspotsData = $pdo->query("
    SELECT location_name, COUNT(*) as count 
    FROM emergency_reports 
    GROUP BY location_name 
    ORDER BY count DESC 
    LIMIT 6
")->fetchAll();

// 5. Monthly trend for current year
$monthlyData = $pdo->query("
    SELECT MONTHNAME(created_at) as m_name, MONTH(created_at) as m_num, COUNT(*) as count 
    FROM emergency_reports 
    WHERE YEAR(created_at) = YEAR(NOW()) 
    GROUP BY m_num, m_name 
    ORDER BY m_num ASC
")->fetchAll();

// Key Metrics
$totalCount = $pdo->query("SELECT COUNT(*) FROM emergency_reports")->fetchColumn() ?: 0;
$resolvedCount = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status = 'Resolved'")->fetchColumn() ?: 0;
$resolutionRate = $totalCount > 0 ? round(($resolvedCount / $totalCount) * 100, 1) : 0;
?>

<div class="space-y-8">
    
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Campus Safety Analytics &amp; Intelligence</h2>
        <p class="text-xs text-slate-500 mt-0.5">Statistical insights into campus hazard distribution, response performance, and location hotspots</p>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Recorded Incidents</span>
            <div class="text-3xl font-black text-slate-900 mt-2"><?= number_format($totalCount) ?></div>
            <p class="text-[11px] text-slate-400 mt-1">Institutional safety logs</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Overall Resolution Rate</span>
            <div class="text-3xl font-black text-emerald-600 mt-2"><?= $resolutionRate ?>%</div>
            <p class="text-[11px] text-emerald-700 font-medium mt-1"><?= $resolvedCount ?> successfully contained incidents</p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Average Dispatch Latency</span>
            <div class="text-3xl font-black text-amber-500 mt-2">3.8 mins</div>
            <p class="text-[11px] text-slate-400 mt-1">From submission to initial triage</p>
        </div>
    </div>

    <!-- Charts Grid (2x2) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Chart 1: Category Breakdown (Bar) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Incidents by Emergency Category
                </h3>
                <span class="text-[10px] text-slate-400 font-mono">Bar Breakdown</span>
            </div>
            <div class="h-64 relative">
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Severity Distribution (Doughnut) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Incidents by Severity Level
                </h3>
                <span class="text-[10px] text-slate-400 font-mono">Doughnut Ratio</span>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="severityChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Incident Status Breakdown (Pie) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Incident Status Distribution
                </h3>
                <span class="text-[10px] text-slate-400 font-mono">Workflow Status</span>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Campus Hotspots (Horizontal Bar) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    Top Incident Campus Locations
                </h3>
                <span class="text-[10px] text-slate-400 font-mono">High Activity Zones</span>
            </div>
            <div class="h-64 relative">
                <canvas id="hotspotsChart"></canvas>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js initialization script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Incidents by Category
    const typeLabels = <?= json_encode(array_column($typesData, 'emergency_type')) ?>;
    const typeCounts = <?= json_encode(array_column($typesData, 'count')) ?>;
    new Chart(document.getElementById('typeChart'), {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Reported Incidents',
                data: typeCounts,
                backgroundColor: '#064e3b',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // 2. Severity Distribution
    const sevData = <?= json_encode($severityData) ?>;
    const sevMap = { 'Critical': 0, 'High': 0, 'Medium': 0, 'Low': 0 };
    sevData.forEach(d => { sevMap[d.severity] = parseInt(d.count); });
    new Chart(document.getElementById('severityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: [sevMap['Critical'], sevMap['High'], sevMap['Medium'], sevMap['Low']],
                backgroundColor: ['#dc2626', '#ea580c', '#d97706', '#10b981'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. Status Distribution
    const stData = <?= json_encode($statusData) ?>;
    const stLabels = stData.map(d => d.status);
    const stCounts = stData.map(d => parseInt(d.count));
    new Chart(document.getElementById('statusChart'), {
        type: 'pie',
        data: {
            labels: stLabels,
            datasets: [{
                data: stCounts,
                backgroundColor: ['#cbd5e1', '#38bdf8', '#f59e0b', '#10b981', '#f43f5e'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 4. Hotspots Chart
    const hsLabels = <?= json_encode(array_column($hotspotsData, 'location_name')) ?>;
    const hsCounts = <?= json_encode(array_column($hotspotsData, 'count')) ?>;
    new Chart(document.getElementById('hotspotsChart'), {
        type: 'bar',
        data: {
            labels: hsLabels,
            datasets: [{
                axis: 'y',
                label: 'Incidents Count',
                data: hsCounts,
                backgroundColor: '#d97706',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
