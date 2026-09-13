<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Public Landing Page
 */

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// Fetch quick live stats from database
$pdo = getDB();
$totalResolved = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status = 'Resolved'")->fetchColumn() ?: 128;
$totalActiveIncidents = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE status IN ('Pending', 'Acknowledged', 'In Progress')")->fetchColumn() ?: 3;
$totalAlertsCount = count($activeAlerts);
$contacts = $pdo->query("SELECT * FROM emergency_contacts ORDER BY priority_order ASC LIMIT 4")->fetchAll();
?>

<!-- 1. HERO SECTION -->
<section class="relative bg-gradient-to-b from-emerald-950 via-fpi-900 to-fpi-800 text-white overflow-hidden pt-12 pb-24 lg:pt-20 lg:pb-32">
    <!-- Ambient glowing backdrop effect -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Copy -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                
                <!-- Institutional Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-900/80 border border-emerald-700/60 text-emerald-300 text-xs font-semibold backdrop-blur-sm shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Federal Polytechnic Ilaro &bull; Rapid Emergency Response</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                    Campus Safety &amp; <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-200 to-amber-300">
                        Emergency Alert
                    </span> System
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl font-normal leading-relaxed">
                    Report emergencies quickly, receive important safety alerts, and help keep our campus community safe. A unified institutional incident response platform for students, faculty, and security personnel.
                </p>

                <!-- Primary & Secondary CTA Buttons -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl text-base font-bold bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white shadow-xl shadow-red-600/30 hover:shadow-2xl transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-300 text-lg"></i>
                        <span>Report an Emergency</span>
                    </a>
                    <a href="<?= BASE_URL ?>/safety-guides.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-2xl text-base font-semibold bg-white/10 hover:bg-white/15 text-white border border-white/20 backdrop-blur-sm transition">
                        <i class="fa-solid fa-book-medical text-emerald-400"></i>
                        <span>View Safety Information</span>
                    </a>
                </div>

                <!-- Emergency Dispatch Quick Metrics -->
                <div class="pt-6 border-t border-emerald-800/60 grid grid-cols-3 gap-4 text-center sm:text-left">
                    <div>
                        <div class="text-2xl font-extrabold text-amber-400">24/7</div>
                        <div class="text-xs text-slate-400">Security Dispatch</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-emerald-400">&lt; 5 Mins</div>
                        <div class="text-xs text-slate-400">Average Response</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-white">100%</div>
                        <div class="text-xs text-slate-400">Campus Coverage</div>
                    </div>
                </div>

            </div>

            <!-- Hero Visual Card / Interactive Emergency Monitor Panel -->
            <div class="lg:col-span-5">
                <div class="bg-slate-900/90 rounded-3xl p-6 border border-emerald-500/30 shadow-2xl backdrop-blur-md relative overflow-hidden">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-xs font-bold text-slate-200 uppercase tracking-wider">Live Incident Monitoring</span>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-950/80 px-2.5 py-1 rounded-full border border-emerald-800/60">
                            Active Desk
                        </span>
                    </div>

                    <!-- Active Alert Preview Card inside Hero -->
                    <div class="mt-4 p-4 rounded-2xl bg-slate-800/80 border border-slate-700/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-500/20 text-red-400 border border-red-500/30 uppercase tracking-wider">
                                <?= $criticalAlert ? 'BROADCAST ALERT' : 'CAMPUS STATUS: NORMAL' ?>
                            </span>
                            <span class="text-[11px] text-slate-400"><i class="fa-regular fa-clock mr-1"></i> <?= date('g:i A') ?></span>
                        </div>
                        <h3 class="text-sm font-bold text-white">
                            <?= $criticalAlert ? e($criticalAlert['title']) : 'All Academic & Residential Zones Clear' ?>
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            <?= $criticalAlert ? e($criticalAlert['message']) : 'No major emergency disruptions reported across Federal Polytechnic Ilaro campus grounds. Safety patrols on schedule.' ?>
                        </p>
                        <div class="text-[11px] text-emerald-400 flex items-center gap-1.5 font-medium">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?= $criticalAlert ? e($criticalAlert['location']) : 'East & West Gate, Library & Science Complex' ?></span>
                        </div>
                    </div>

                    <!-- Quick Direct Dial Buttons -->
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 text-xs font-bold transition">
                            <i class="fa-solid fa-phone text-red-400"></i> Call Security
                        </a>
                        <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="flex items-center justify-center gap-2 p-3 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition">
                            <i class="fa-solid fa-ambulance text-emerald-400"></i> Call Ambulance
                        </a>
                    </div>

                    <!-- Instant Report Button -->
                    <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="mt-4 w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-sm shadow-md transition">
                        <i class="fa-solid fa-bullhorn text-amber-300"></i> Dispatch Incident Now
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. NEED HELP EMERGENCY ACTION BANNER -->
<section class="bg-amber-500 text-slate-900 py-6 border-y-4 border-amber-600 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4 text-center md:text-left">
            <div class="w-14 h-14 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center text-2xl flex-shrink-0 shadow">
                <i class="fa-solid fa-bell-slash animate-bounce"></i>
            </div>
            <div>
                <h3 class="text-xl font-extrabold tracking-tight">Witnessing an Active Emergency Right Now?</h3>
                <p class="text-sm font-medium text-slate-800">Do not wait. Security and medical personnel are on 24-hour standby.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="px-5 py-3 rounded-xl font-extrabold bg-slate-900 text-white hover:bg-black transition shadow text-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-phone text-red-400"></i> Direct Hotline
            </a>
            <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="px-6 py-3 rounded-xl font-extrabold bg-red-600 text-white hover:bg-red-700 transition shadow text-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> REPORT INCIDENT
            </a>
        </div>
    </div>
</section>

<!-- 3. ABOUT THE SYSTEM SECTION -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-50 text-fpi-800 text-xs font-bold uppercase tracking-wider">
                    Institutional Protection
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Dedicated Campus Emergency Response for Federal Polytechnic Ilaro
                </h2>
                <p class="text-base text-slate-600 leading-relaxed">
                    The Campus Safety &amp; Emergency Alert System is an integrated crisis communication platform established to bridge students, lecturers, and non-teaching staff with the institution's Chief Security Unit, Medical Centre, and Ogun State emergency responders.
                </p>
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0 font-bold mt-0.5">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Precision Location Tagging</h4>
                            <p class="text-xs text-slate-600">Pinpoint incidents across academic complexes, student hostels, and access gates using GPS coordinates.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0 font-bold mt-0.5">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Instant Administrative Triage</h4>
                            <p class="text-xs text-slate-600">Incident reports are assigned immediately to rapid squads with live status tracking and audit trails.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0 font-bold mt-0.5">
                            <i class="fa-solid fa-tower-broadcast"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Campus-Wide Threat Broadcasting</h4>
                            <p class="text-xs text-slate-600">Authorized campus marshals publish real-time safety advisories to prevent secondary risks.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="relative rounded-3xl p-8 bg-gradient-to-br from-emerald-50 to-slate-100 border border-emerald-200/60 shadow-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-6 rounded-2xl bg-white shadow-sm border border-slate-200 text-center">
                            <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 mx-auto flex items-center justify-center text-xl mb-3">
                                <i class="fa-solid fa-fire-extinguisher"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-800">Fire Safety</h4>
                            <p class="text-xs text-slate-500 mt-1">Laboratory &amp; hostel fire monitoring</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white shadow-sm border border-slate-200 text-center">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-xl mb-3">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-800">Medical Care</h4>
                            <p class="text-xs text-slate-500 mt-1">Rapid ambulance deployment</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white shadow-sm border border-slate-200 text-center">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center text-xl mb-3">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-800">Crime Prevention</h4>
                            <p class="text-xs text-slate-500 mt-1">Theft &amp; security containment</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white shadow-sm border border-slate-200 text-center">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 mx-auto flex items-center justify-center text-xl mb-3">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <h4 class="text-base font-bold text-slate-800">Interactive Map</h4>
                            <p class="text-xs text-slate-500 mt-1">Real-time incident GIS tracking</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 4. HOW IT WORKS SECTION -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-fpi-700 bg-emerald-100/60 px-3 py-1 rounded-full">
                Incident Response Lifecycle
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                How Emergency Reporting Works
            </h2>
            <p class="text-base text-slate-600 mt-3">
                Submitting an emergency report triggers a swift, transparent, four-step operational protocol.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <!-- Step 1 -->
            <div class="relative bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-fpi-800 text-amber-400 font-black text-lg flex items-center justify-center mb-5 shadow">
                    01
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Spot &amp; Report</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Student or staff member selects the incident category, pins the campus landmark, attaches evidence, and submits with or without anonymity.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="relative bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-fpi-800 text-amber-400 font-black text-lg flex items-center justify-center mb-5 shadow">
                    02
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Instant Dispatch</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Security Command Desk is alerted immediately, reviews the severity level, and assigns the appropriate rapid response team (medical, security, or works).
                </p>
            </div>

            <!-- Step 3 -->
            <div class="relative bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-fpi-800 text-amber-400 font-black text-lg flex items-center justify-center mb-5 shadow">
                    03
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Active Intervention</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Response units arrive on site to contain the incident. If necessary, a high-priority safety alert is published to the student portal.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="relative bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-fpi-800 text-amber-400 font-black text-lg flex items-center justify-center mb-5 shadow">
                    04
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Resolution &amp; Log</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    The incident is marked resolved with administrative notes, updating the reporter's timeline and archiving audit logs for safety review.
                </p>
            </div>

        </div>

    </div>
</section>

<!-- 5. EMERGENCY CATEGORIES SECTION -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-fpi-700 bg-emerald-100/60 px-3 py-1 rounded-full">
                Incident Classification
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Supported Emergency Categories
            </h2>
            <p class="text-base text-slate-600 mt-3">
                Our system categorizes emergencies so the relevant safety units can respond with the right equipment and protocol.
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            
            <?php
            $categories = [
                ['name' => 'Fire Incident', 'icon' => 'fa-fire', 'color' => 'text-rose-600 bg-rose-50 border-rose-200', 'desc' => 'Hostel or lab fire, electrical sparks, explosions'],
                ['name' => 'Medical Emergency', 'icon' => 'fa-kit-medical', 'color' => 'text-red-600 bg-red-50 border-red-200', 'desc' => 'Collapse, sudden illness, sports trauma, bleeding'],
                ['name' => 'Security Threat', 'icon' => 'fa-shield-halved', 'color' => 'text-indigo-600 bg-indigo-50 border-indigo-200', 'desc' => 'Intruders, armed suspicion, physical harassment'],
                ['name' => 'Accident', 'icon' => 'fa-car-burst', 'color' => 'text-orange-600 bg-orange-50 border-orange-200', 'desc' => 'Vehicular collision, pedestrian impact, machinery'],
                ['name' => 'Theft & Burglary', 'icon' => 'fa-mask', 'color' => 'text-purple-600 bg-purple-50 border-purple-200', 'desc' => 'Hostel break-in, gadget theft, library burglary'],
                ['name' => 'Physical Violence', 'icon' => 'fa-hand-fist', 'color' => 'text-red-700 bg-red-50 border-red-200', 'desc' => 'Assault, violent altercation, student brawl'],
                ['name' => 'Gas & Chemical Leak', 'icon' => 'fa-smog', 'color' => 'text-yellow-600 bg-yellow-50 border-yellow-200', 'desc' => 'Fume release, lab chemical spill, cylinder leakage'],
                ['name' => 'Infrastructure Failure', 'icon' => 'fa-triangle-exclamation', 'color' => 'text-amber-600 bg-amber-50 border-amber-200', 'desc' => 'Structural collapse, open wire, burst water main'],
                ['name' => 'Natural Hazard', 'icon' => 'fa-cloud-bolt', 'color' => 'text-teal-600 bg-teal-50 border-teal-200', 'desc' => 'Severe storm, fallen tree, flash flooding'],
                ['name' => 'Other Incident', 'icon' => 'fa-circle-exclamation', 'color' => 'text-slate-600 bg-slate-100 border-slate-200', 'desc' => 'Any unlisted critical hazard requiring security']
            ];

            foreach ($categories as $cat):
            ?>
            <div class="p-5 rounded-2xl border bg-white hover:shadow-md transition-all group">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl mb-3 border <?= $cat['color'] ?> group-hover:scale-110 transition">
                    <i class="fa-solid <?= $cat['icon'] ?>"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-900 group-hover:text-fpi-800 transition"><?= $cat['name'] ?></h4>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug"><?= $cat['desc'] ?></p>
            </div>
            <?php endforeach; ?>

        </div>

    </div>
</section>

<!-- 6. LATEST CAMPUS ALERTS SECTION -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-100 px-3 py-1 rounded-full">
                    Official Broadcasts
                </span>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                    Current Campus Safety Advisories
                </h2>
                <p class="text-sm text-slate-600 mt-1">Live announcements issued by FPI Security &amp; Safety Unit.</p>
            </div>
            <a href="<?= BASE_URL ?>/safety-guides.php" class="text-sm font-bold text-fpi-800 hover:text-fpi-900 flex items-center gap-1">
                View all safety procedures &rarr;
            </a>
        </div>

        <?php if (!empty($activeAlerts)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($activeAlerts as $alert): ?>
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <?= get_severity_badge($alert['severity']) ?>
                    <span class="text-xs text-slate-400 font-medium">
                        <i class="fa-regular fa-clock mr-1"></i> <?= time_ago($alert['created_at']) ?>
                    </span>
                </div>
                <h3 class="text-base font-bold text-slate-900 leading-snug">
                    <?= e($alert['title']) ?>
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    <?= e($alert['message']) ?>
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1 text-emerald-700 font-medium">
                        <i class="fa-solid fa-location-dot"></i> <?= e($alert['location']) ?>
                    </span>
                    <span class="bg-slate-100 px-2 py-0.5 rounded text-[11px] font-semibold text-slate-600">
                        Target: <?= e($alert['target_audience']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fa-solid fa-shield-heart"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">No Active Emergency Alerts at this Time</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                Federal Polytechnic Ilaro campus is currently operating under regular peaceful status.
            </p>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- 7. EMERGENCY CONTACTS DIRECTORY PREVIEW -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-fpi-700 bg-emerald-100/60 px-3 py-1 rounded-full">
                Direct Communication
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                Campus Emergency Hotlines
            </h2>
            <p class="text-base text-slate-600 mt-3">
                Save these numbers or tap to dial immediately during critical situations.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($contacts as $c): ?>
            <div class="bg-gradient-to-b from-slate-50 to-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 mb-2">
                        <?= e($c['category']) ?>
                    </span>
                    <h4 class="text-base font-bold text-slate-900 leading-tight"><?= e($c['name']) ?></h4>
                    <p class="text-xs text-slate-500 mt-1"><?= e($c['department']) ?></p>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-2 flex items-center gap-1">
                        <i class="fa-solid fa-clock text-[10px]"></i> <?= e($c['availability']) ?>
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-200">
                    <a href="tel:<?= str_replace(' ', '', $c['phone']) ?>" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs shadow-sm transition">
                        <i class="fa-solid fa-phone"></i> <?= e($c['phone']) ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="<?= BASE_URL ?>/contacts.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <span>View Full Emergency Telephone Directory</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</section>

<!-- 8. FREQUENTLY ASKED QUESTIONS SECTION -->
<section class="py-20 bg-slate-50 border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-fpi-700 bg-emerald-100/60 px-3 py-1 rounded-full">
                Clarifications
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                Frequently Asked Questions
            </h2>
            <p class="text-sm text-slate-600 mt-1">Learn how the emergency reporting system works for students and staff.</p>
        </div>

        <div class="space-y-4">
            
            <details class="bg-white rounded-2xl border border-slate-200 p-5 group shadow-sm">
                <summary class="font-bold text-slate-900 cursor-pointer flex justify-between items-center text-sm sm:text-base">
                    <span>Can I submit an emergency report anonymously?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed border-t pt-3">
                    Yes. When filling out the emergency report, you can uncheck the callback option or choose not to provide personal identifiers. However, providing your contact number helps response units locate you faster if immediate life-saving coordination is necessary.
                </div>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-5 group shadow-sm">
                <summary class="font-bold text-slate-900 cursor-pointer flex justify-between items-center text-sm sm:text-base">
                    <span>How fast does campus security respond to a report?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed border-t pt-3">
                    Emergency reports submitted through CES generate high-priority audio-visual alerts on the Central Security Operations console. Critical severity reports are acknowledged and dispatched within 3 to 5 minutes across campus grounds.
                </div>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-5 group shadow-sm">
                <summary class="font-bold text-slate-900 cursor-pointer flex justify-between items-center text-sm sm:text-base">
                    <span>What happens if there is an off-campus incident near student residences?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed border-t pt-3">
                    While the system primarily covers the main campus complexes and on-campus hostels, FPI Security coordinates directly with the Nigeria Police Force (Ilaro Division) and Ogun State Fire Service for incidents affecting students in nearby off-campus lodges.
                </div>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-5 group shadow-sm">
                <summary class="font-bold text-slate-900 cursor-pointer flex justify-between items-center text-sm sm:text-base">
                    <span>How do I track the progress of my emergency report?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="mt-3 text-xs sm:text-sm text-slate-600 leading-relaxed border-t pt-3">
                    Every report receives a unique tracking ID (e.g. <code>CES-2026-00101</code>). Once logged into your student or staff dashboard, you can open "My Reports" to view real-time timeline milestones: Pending, Acknowledged, In Progress, and Resolved.
                </div>
            </details>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
