<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Incident Monitoring & Reports Register
 */

$pageTitle = 'Emergency Reports Register';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

// Filter parameters
$search    = clean_input($_GET['q'] ?? '');
$type      = clean_input($_GET['type'] ?? '');
$severity  = clean_input($_GET['severity'] ?? '');
$status    = clean_input($_GET['status'] ?? '');
$location  = clean_input($_GET['location'] ?? '');
$dateFrom  = clean_input($_GET['date_from'] ?? '');
$dateTo    = clean_input($_GET['date_to'] ?? '');

$sql = "
    SELECT r.*, u.name as reporter_name, u.email as reporter_email, u.role as reporter_role, u.department as reporter_dept 
    FROM emergency_reports r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (r.report_reference LIKE ? OR r.location_name LIKE ? OR r.description LIKE ? OR u.name LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}
if (!empty($type)) {
    $sql .= " AND r.emergency_type = ?";
    $params[] = $type;
}
if (!empty($severity)) {
    $sql .= " AND r.severity = ?";
    $params[] = $severity;
}
if (!empty($status)) {
    $sql .= " AND r.status = ?";
    $params[] = $status;
}
if (!empty($location)) {
    $sql .= " AND r.location_name LIKE ?";
    $params[] = "%$location%";
}
if (!empty($dateFrom)) {
    $sql .= " AND DATE(r.created_at) >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $sql .= " AND DATE(r.created_at) <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY 
    CASE r.status 
        WHEN 'Pending' THEN 1 
        WHEN 'In Progress' THEN 2 
        WHEN 'Acknowledged' THEN 3 
        ELSE 4 
    END, 
    CASE r.severity 
        WHEN 'Critical' THEN 1 
        WHEN 'High' THEN 2 
        WHEN 'Medium' THEN 3 
        ELSE 4 
    END, 
    r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Fetch distinct locations for filter dropdown
$locationsList = $pdo->query("SELECT DISTINCT location_name FROM emergency_reports ORDER BY location_name ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Emergency Reports Register</h2>
            <p class="text-xs text-slate-500 mt-0.5">Filter, investigate, dispatch units, and manage incident lifecycles</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="no-print inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition">
                <i class="fa-solid fa-print"></i> Print Register
            </button>
            <a href="<?= BASE_URL ?>/admin/map.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
                <i class="fa-solid fa-map-location-dot text-amber-400"></i> Map View
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm no-print space-y-4">
        <form action="<?= BASE_URL ?>/admin/reports.php" method="GET" class="space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search term -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Search Keywords</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by Ref ID, reporter name, location or keywords..." class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                </div>

                <!-- Emergency Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Incident Category</label>
                    <select name="type" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none text-xs font-medium text-slate-800">
                        <option value="">All Categories</option>
                        <option value="Fire" <?= $type === 'Fire' ? 'selected' : '' ?>>Fire</option>
                        <option value="Medical Emergency" <?= $type === 'Medical Emergency' ? 'selected' : '' ?>>Medical Emergency</option>
                        <option value="Security Threat" <?= $type === 'Security Threat' ? 'selected' : '' ?>>Security Threat</option>
                        <option value="Accident" <?= $type === 'Accident' ? 'selected' : '' ?>>Accident</option>
                        <option value="Theft" <?= $type === 'Theft' ? 'selected' : '' ?>>Theft</option>
                        <option value="Violence" <?= $type === 'Violence' ? 'selected' : '' ?>>Violence</option>
                        <option value="Gas Leak" <?= $type === 'Gas Leak' ? 'selected' : '' ?>>Gas Leak</option>
                        <option value="Infrastructure Failure" <?= $type === 'Infrastructure Failure' ? 'selected' : '' ?>>Infrastructure Failure</option>
                        <option value="Natural Hazard" <?= $type === 'Natural Hazard' ? 'selected' : '' ?>>Natural Hazard</option>
                        <option value="Other" <?= $type === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <!-- Severity -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Severity Level</label>
                    <select name="severity" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none text-xs font-medium text-slate-800">
                        <option value="">All Severities</option>
                        <option value="Critical" <?= $severity === 'Critical' ? 'selected' : '' ?>>Critical</option>
                        <option value="High" <?= $severity === 'High' ? 'selected' : '' ?>>High</option>
                        <option value="Medium" <?= $severity === 'Medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="Low" <?= $severity === 'Low' ? 'selected' : '' ?>>Low</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3 items-end">
                <!-- Status -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none text-xs font-medium text-slate-800">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="Acknowledged" <?= $status === 'Acknowledged' ? 'selected' : '' ?>>Acknowledged</option>
                        <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Resolved" <?= $status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <!-- Date from -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Date From</label>
                    <input type="date" name="date_from" value="<?= e($dateFrom) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800">
                </div>

                <!-- Date to -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Date To</label>
                    <input type="date" name="date_to" value="<?= e($dateTo) ?>" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800">
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
                        <i class="fa-solid fa-filter mr-1"></i> Apply Filters
                    </button>
                    <a href="<?= BASE_URL ?>/admin/reports.php" class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs transition" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </div>

        </form>
    </div>

    <!-- Reports Table Count Banner -->
    <div class="flex items-center justify-between text-xs text-slate-500 font-semibold px-2">
        <span>Found <strong><?= count($reports) ?></strong> emergency <?= count($reports) === 1 ? 'incident report' : 'incident reports' ?></span>
        <span class="text-slate-400 text-[11px]">Sorted by urgency &amp; date</span>
    </div>

    <!-- Reports Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (!empty($reports)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-4 px-5">Ref ID</th>
                        <th class="py-4 px-5">Category</th>
                        <th class="py-4 px-5">Campus Location</th>
                        <th class="py-4 px-5">Reporter</th>
                        <th class="py-4 px-5">Severity</th>
                        <th class="py-4 px-5">Status</th>
                        <th class="py-4 px-5">Time Reported</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($reports as $r): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-4 px-5 font-mono font-bold text-slate-900 whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/admin/report-view.php?id=<?= $r['id'] ?>" class="text-fpi-800 hover:underline">
                                <?= e($r['report_reference']) ?>
                            </a>
                        </td>
                        <td class="py-4 px-5 whitespace-nowrap">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="<?= get_emergency_icon($r['emergency_type']) ?>"></i>
                                <?= e($r['emergency_type']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-5 text-slate-600 max-w-[200px] truncate">
                            <?= e($r['location_name']) ?>
                        </td>
                        <td class="py-4 px-5 whitespace-nowrap">
                            <?php if (!empty($r['reporter_name'])): ?>
                                <span class="font-bold text-slate-900 block"><?= e($r['reporter_name']) ?></span>
                                <span class="text-[10px] text-slate-400"><?= e($r['reporter_phone']) ?> (<?= ucfirst(e($r['reporter_role'])) ?>)</span>
                            <?php else: ?>
                                <span class="text-slate-400 italic">Anonymous Reporter</span>
                                <?php if (!empty($r['reporter_phone'])): ?>
                                    <span class="block text-[10px] text-slate-400"><?= e($r['reporter_phone']) ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-5 whitespace-nowrap">
                            <?= get_severity_badge($r['severity']) ?>
                        </td>
                        <td class="py-4 px-5 whitespace-nowrap">
                            <?= get_status_badge($r['status']) ?>
                        </td>
                        <td class="py-4 px-5 text-slate-400 whitespace-nowrap font-medium">
                            <?= time_ago($r['created_at']) ?>
                        </td>
                        <td class="py-4 px-5 text-right whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/admin/report-view.php?id=<?= $r['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs transition shadow-xs">
                                <i class="fa-solid fa-sliders text-[10px]"></i> Triage
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="py-16 text-center text-slate-400">
            <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">No Incidents Match Search Criteria</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                Try clearing or loosening your search filters to view emergency records.
            </p>
            <a href="<?= BASE_URL ?>/admin/reports.php" class="inline-block mt-4 text-xs font-bold text-fpi-800 underline">
                Clear Filters
            </a>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
