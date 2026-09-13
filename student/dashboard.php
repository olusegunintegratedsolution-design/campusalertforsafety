<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Student & Staff Dashboard
 */

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../includes/nav-portal.php';

$userId = $user['id'];
$pdo = getDB();

// Statistics
$myTotalReports = $pdo->prepare("SELECT COUNT(*) FROM emergency_reports WHERE user_id = ?");
$myTotalReports->execute([$userId]);
$statTotal = (int)$myTotalReports->fetchColumn();

$myPending = $pdo->prepare("SELECT COUNT(*) FROM emergency_reports WHERE user_id = ? AND status IN ('Pending', 'Acknowledged')");
$myPending->execute([$userId]);
$statPending = (int)$myPending->fetchColumn();

$myResolved = $pdo->prepare("SELECT COUNT(*) FROM emergency_reports WHERE user_id = ? AND status = 'Resolved'");
$myResolved->execute([$userId]);
$statResolved = (int)$myResolved->fetchColumn();

$activeAlertsList = get_active_alerts($user['role'] === 'student' ? 'Students' : ($user['role'] === 'staff' ? 'Staff' : 'Everyone'));
$statAlerts = count($activeAlertsList);

// Recent User Reports (last 5)
$recentStmt = $pdo->prepare("
    SELECT * FROM emergency_reports 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentStmt->execute([$userId]);
$recentReports = $recentStmt->fetchAll();

// Emergency contacts preview
$contactsStmt = $pdo->query("SELECT * FROM emergency_contacts ORDER BY priority_order ASC LIMIT 3");
$quickContacts = $contactsStmt->fetchAll();
?>

<!-- Welcome Banner -->
<div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-fpi-900 via-fpi-800 to-emerald-900 text-white shadow-lg relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-600/20 rounded-full blur-2xl pointer-events-none"></div>
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950/80 text-emerald-300 border border-emerald-700/60">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Federal Polytechnic Ilaro Campus Safety Network
            </span>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">
                Welcome, <?= e($user['name']) ?>
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                Your campus safety portal is actively monitoring. In case of an emergency, report immediately to trigger rapid security intervention.
            </p>
        </div>
        <div class="flex-shrink-0">
            <a href="<?= BASE_URL ?>/student/report-emergency.php" class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-extrabold text-sm shadow-xl shadow-red-600/30 transition transform hover:-translate-y-0.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-300 text-base"></i>
                <span>REPORT EMERGENCY</span>
            </a>
        </div>
    </div>
</div>

<!-- Prominent Active Campus Alert Banner (if any) -->
<?php if (!empty($activeAlertsList)): ?>
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
            Active Campus Safety Broadcast
        </h3>
        <a href="<?= BASE_URL ?>/student/alerts.php" class="text-xs font-bold text-fpi-800 hover:underline">View All (<?= count($activeAlertsList) ?>) &rarr;</a>
    </div>

    <?php $topAlert = $activeAlertsList[0]; ?>
    <div class="p-6 rounded-3xl bg-white border-2 border-rose-200 shadow-md relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <?= get_severity_badge($topAlert['severity']) ?>
                    <span class="text-xs text-slate-500 flex items-center gap-1 font-medium">
                        <i class="fa-solid fa-location-dot text-slate-400"></i> <?= e($topAlert['location']) ?>
                    </span>
                    <span class="text-xs text-slate-400">&bull; <?= time_ago($topAlert['created_at']) ?></span>
                </div>
                <h4 class="text-base font-extrabold text-slate-900 leading-snug">
                    <?= e($topAlert['title']) ?>
                </h4>
                <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                    <?= e($topAlert['message']) ?>
                </p>
            </div>
        </div>
        <div class="flex-shrink-0 self-end md:self-center">
            <a href="<?= BASE_URL ?>/student/alerts.php" class="px-4 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold text-xs transition">
                Safety Details
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Statistics Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    
    <!-- Stat 1: Total Reports -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between text-slate-500 mb-3">
            <span class="text-xs font-bold uppercase tracking-wider">My Reports</span>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-slate-900"><?= $statTotal ?></div>
        <p class="text-[11px] text-slate-400 mt-1">Submitted by your account</p>
    </div>

    <!-- Stat 2: Pending -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between text-slate-500 mb-3">
            <span class="text-xs font-bold uppercase tracking-wider">In Review</span>
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-amber-600"><?= $statPending ?></div>
        <p class="text-[11px] text-slate-400 mt-1">Pending or dispatched</p>
    </div>

    <!-- Stat 3: Resolved -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between text-slate-500 mb-3">
            <span class="text-xs font-bold uppercase tracking-wider">Resolved</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-emerald-600"><?= $statResolved ?></div>
        <p class="text-[11px] text-slate-400 mt-1">Safely contained</p>
    </div>

    <!-- Stat 4: Active Alerts -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between text-slate-500 mb-3">
            <span class="text-xs font-bold uppercase tracking-wider">Campus Alerts</span>
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-base">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
        </div>
        <div class="text-3xl font-black text-rose-600"><?= $statAlerts ?></div>
        <p class="text-[11px] text-slate-400 mt-1">Active institutional advisories</p>
    </div>

</div>

<!-- Quick Action Cards (4 Cards) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <a href="<?= BASE_URL ?>/student/report-emergency.php" class="p-5 rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 text-white shadow-sm hover:shadow-lg transition transform hover:-translate-y-0.5 group">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg mb-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
        </div>
        <h4 class="text-sm font-extrabold uppercase tracking-wide">REPORT EMERGENCY</h4>
        <p class="text-xs text-white/80 mt-1">Fast 60-second incident dispatch</p>
    </a>

    <a href="<?= BASE_URL ?>/student/my-reports.php" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition group">
        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-emerald-100 text-slate-700 group-hover:text-emerald-700 flex items-center justify-center text-lg mb-3 transition">
            <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <h4 class="text-sm font-bold text-slate-900 group-hover:text-fpi-800 transition">VIEW MY REPORTS</h4>
        <p class="text-xs text-slate-500 mt-1">Track response team status</p>
    </a>

    <a href="<?= BASE_URL ?>/student/alerts.php" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition group">
        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-emerald-100 text-slate-700 group-hover:text-emerald-700 flex items-center justify-center text-lg mb-3 transition">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <h4 class="text-sm font-bold text-slate-900 group-hover:text-fpi-800 transition">CAMPUS ALERTS</h4>
        <p class="text-xs text-slate-500 mt-1">Official safety notices</p>
    </a>

    <a href="<?= BASE_URL ?>/safety-guides.php" class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition group">
        <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-emerald-100 text-slate-700 group-hover:text-emerald-700 flex items-center justify-center text-lg mb-3 transition">
            <i class="fa-solid fa-book-medical"></i>
        </div>
        <h4 class="text-sm font-bold text-slate-900 group-hover:text-fpi-800 transition">SAFETY PROTOCOLS</h4>
        <p class="text-xs text-slate-500 mt-1">First aid &amp; evacuation drills</p>
    </a>

</div>

<!-- Recent Reports & Emergency Contacts Split -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- Left: Recent Reports Submitted By User -->
    <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Recent Incident Reports</h3>
                <p class="text-xs text-slate-500 mt-0.5">Status updates from Campus Security Desk</p>
            </div>
            <a href="<?= BASE_URL ?>/student/my-reports.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900">
                View All &rarr;
            </a>
        </div>

        <?php if (!empty($recentReports)): ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($recentReports as $r): ?>
                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50 rounded-xl px-2 transition">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                            <i class="<?= get_emergency_icon($r['emergency_type']) ?>"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-xs font-extrabold text-slate-800"><?= e($r['report_reference']) ?></span>
                                <span class="text-xs font-bold text-slate-900">&bull; <?= e($r['emergency_type']) ?></span>
                                <?= get_severity_badge($r['severity']) ?>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-[11px] text-slate-400"></i>
                                <?= e($r['location_name']) ?>
                                <span class="text-slate-300">&bull;</span>
                                <span><?= time_ago($r['created_at']) ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 self-end sm:self-center">
                        <?= get_status_badge($r['status']) ?>
                        <a href="<?= BASE_URL ?>/student/my-reports.php?ref=<?= urlencode($r['report_reference']) ?>" class="p-2 text-slate-400 hover:text-fpi-800 transition text-xs" title="View Tracking Timeline">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3">
                    <i class="fa-regular fa-clipboard"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">No emergency reports submitted yet.</p>
                <p class="text-xs text-slate-400 mt-1">Reports you submit will appear here with live tracking milestones.</p>
                <a href="<?= BASE_URL ?>/student/report-emergency.php" class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-red-50 text-red-600 font-bold text-xs hover:bg-red-100 transition">
                    <i class="fa-solid fa-bolt"></i> Report New Incident
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right: Quick Hotlines Widget -->
    <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
        <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-slate-900">Direct Hotlines</h3>
            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">24/7 Available</span>
        </div>

        <div class="space-y-3">
            <?php foreach ($quickContacts as $qc): ?>
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                <div class="overflow-hidden">
                    <h4 class="text-xs font-bold text-slate-900 truncate"><?= e($qc['name']) ?></h4>
                    <p class="text-[10px] text-slate-500 truncate"><?= e($qc['department']) ?></p>
                </div>
                <a href="tel:<?= str_replace(' ', '', $qc['phone']) ?>" class="p-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white text-xs font-bold shadow-xs transition flex-shrink-0" title="Dial Hotline">
                    <i class="fa-solid fa-phone"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-2">
            <a href="<?= BASE_URL ?>/contacts.php" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-xs font-bold text-slate-700 transition">
                <span>All Campus Emergency Numbers</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
