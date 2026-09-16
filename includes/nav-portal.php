<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Authenticated Portal Shell Component (Header, Sidebar, Notifications)
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

require_login();

$user = current_user();
$unreadNotifCount = get_unread_notification_count($user['id']);

$pdo = getDB();
// Fetch latest 5 notifications for dropdown
$recentNotifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentNotifs->execute([$user['id']]);
$userNotifications = $recentNotifs->fetchAll();

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$isAdminPortal = ($currentDir === 'admin');

$activeAlerts = get_active_alerts($user['role'] === 'student' ? 'Students' : ($user['role'] === 'staff' ? 'Staff' : 'Everyone'));
$criticalAlert = null;
foreach ($activeAlerts as $a) {
    if ($a['severity'] === 'Critical' || $a['severity'] === 'High') {
        $criticalAlert = $a;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= APP_NAME ?> - <?= INSTITUTION_NAME ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fpi: {
                            900: '#022c22',
                            800: '#064e3b',
                            700: '#047857',
                            600: '#059669',
                            500: '#10b981',
                            gold: '#d97706',
                            goldlight: '#f59e0b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="flex h-full overflow-hidden text-slate-800 antialiased">

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div id="portal-mobile-backdrop" class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden hidden" onclick="togglePortalSidebar()"></div>

    <!-- SIDEBAR COMPONENT -->
    <aside id="portal-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-fpi-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static flex-shrink-0 border-r border-emerald-950 shadow-2xl lg:shadow-none">
        
        <!-- Sidebar Brand -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-emerald-900/60 bg-emerald-950/40">
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 flex-shrink-0">
                    <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-sm font-black text-white tracking-tight flex items-center gap-1.5">
                        FPI CAMPUS SAFETY
                    </span>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">
                        <?= $isAdminPortal ? 'Command Center' : 'User Portal' ?>
                    </span>
                </div>
            </a>
            <button type="button" onclick="togglePortalSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg focus:outline-none">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- User Quick Card in Sidebar -->
        <div class="p-4 mx-3 my-3 rounded-2xl bg-emerald-950/60 border border-emerald-800/40 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-fpi-700 text-white font-black text-sm flex items-center justify-center flex-shrink-0 shadow">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="overflow-hidden flex-1">
                <h4 class="text-xs font-bold text-white truncate"><?= e($user['name']) ?></h4>
                <p class="text-[10px] text-slate-400 truncate"><?= e($user['department']) ?></p>
                <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[9px] font-extrabold uppercase <?= $user['role'] === 'admin' ? 'bg-amber-400 text-slate-900' : 'bg-emerald-800 text-emerald-200' ?>">
                    <?= strtoupper(e($user['role'])) ?>
                </span>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 text-xs font-medium">
            
            <?php if ($isAdminPortal): ?>
                <!-- ADMIN MENU -->
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Emergency Operations</div>
                
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'dashboard.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-4 text-center text-amber-400"></i>
                    <span>Dashboard Overview</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= ($currentPage === 'reports.php' || $currentPage === 'report-view.php') ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-list-check w-4 text-center text-rose-400"></i>
                    <span>Emergency Reports</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/map.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'map.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-map-location-dot w-4 text-center text-emerald-400"></i>
                    <span>Live Incident Map</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/alerts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'alerts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-tower-broadcast w-4 text-center text-yellow-400"></i>
                    <span>Broadcast Alerts</span>
                </a>

                <div class="pt-4 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administration</div>

                <a href="<?= BASE_URL ?>/admin/users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'users.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-users w-4 text-center text-sky-400"></i>
                    <span>User Management</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/contacts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'contacts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-address-book w-4 text-center text-teal-400"></i>
                    <span>Emergency Directory</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'analytics.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-chart-pie w-4 text-center text-purple-400"></i>
                    <span>Analytics &amp; Trends</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/logs.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'logs.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-shield-halved w-4 text-center text-indigo-400"></i>
                    <span>Audit &amp; Security Logs</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'settings.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-sliders w-4 text-center text-slate-400"></i>
                    <span>System Settings</span>
                </a>

            <?php else: ?>
                <!-- STUDENT / STAFF MENU -->
                
                <!-- Quick Emergency Report Highlight Button -->
                <div class="p-2 mb-2">
                    <a href="<?= BASE_URL ?>/student/report-emergency.php" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-extrabold text-xs shadow-md transition transform hover:scale-[1.02]">
                        <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
                        <span>REPORT EMERGENCY</span>
                    </a>
                </div>

                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dashboard &amp; Activity</div>

                <a href="<?= BASE_URL ?>/student/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'dashboard.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-4 text-center text-amber-400"></i>
                    <span>Dashboard Home</span>
                </a>

                <a href="<?= BASE_URL ?>/student/my-reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'my-reports.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-clipboard-list w-4 text-center text-rose-400"></i>
                    <span>My Incident Reports</span>
                </a>

                <a href="<?= BASE_URL ?>/student/alerts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'alerts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-bullhorn w-4 text-center text-yellow-400"></i>
                    <span>Campus Alerts Feed</span>
                </a>

                <a href="<?= BASE_URL ?>/student/notifications.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition <?= $currentPage === 'notifications.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-bell w-4 text-center text-sky-400"></i>
                        <span>Notifications</span>
                    </div>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-600 text-white"><?= $unreadNotifCount ?></span>
                    <?php endif; ?>
                </a>

                <div class="pt-4 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Safety Resources</div>

                <a href="<?= BASE_URL ?>/contacts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'contacts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-phone-volume w-4 text-center text-teal-400"></i>
                    <span>Emergency Contacts</span>
                </a>

                <a href="<?= BASE_URL ?>/safety-guides.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'safety-guides.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-book-medical w-4 text-center text-emerald-400"></i>
                    <span>Safety Protocols</span>
                </a>

                <a href="<?= BASE_URL ?>/student/profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'profile.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-regular fa-user w-4 text-center text-slate-400"></i>
                    <span>My Profile</span>
                </a>

            <?php endif; ?>

        </nav>

        <!-- Sidebar Footer Action -->
        <div class="p-4 border-t border-emerald-950 bg-emerald-950/50 space-y-2">
            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-xs font-semibold text-rose-300 hover:bg-rose-950/40 border border-rose-900/40 transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
            </a>
        </div>

    </aside>

    <!-- MAIN PORTAL WRAPPER (TOPBAR + CONTENT) -->
    <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
        
        <!-- PORTAL TOPBAR -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 flex-shrink-0 shadow-xs">
            
            <div class="flex items-center space-x-3">
                <button type="button" onclick="togglePortalSidebar()" class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-fpi-800 hover:bg-slate-100 transition focus:outline-none" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-tight">
                        <?= isset($pageTitle) ? e($pageTitle) : 'Portal' ?>
                    </h1>
                    <p class="text-[11px] text-slate-400 hidden sm:block">Federal Polytechnic Ilaro &bull; Safety Monitoring Network</p>
                </div>
            </div>

            <!-- Topbar Right Controls -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <!-- Quick Emergency CTA in topbar -->
                <a href="<?= BASE_URL ?>/student/report-emergency.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm transition">
                    <i class="fa-solid fa-bolt text-amber-300"></i> Report Incident
                </a>

                <!-- Notifications Dropdown -->
                <div class="relative">
                    <button type="button" id="notif-btn" class="relative p-2.5 rounded-xl text-slate-600 hover:text-fpi-800 hover:bg-slate-100 transition focus:outline-none" title="Notifications">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-600 text-white text-[9px] font-extrabold flex items-center justify-center animate-pulse">
                                <?= $unreadNotifCount > 9 ? '9+' : $unreadNotifCount ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown Panel -->
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden">
                        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Notifications</h4>
                            <a href="<?= BASE_URL ?>/student/notifications.php" class="text-[11px] font-semibold text-fpi-800 hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                            <?php if (!empty($userNotifications)): ?>
                                <?php foreach ($userNotifications as $n): ?>
                                <a href="<?= !empty($n['link']) ? BASE_URL . '/' . e($n['link']) : '#' ?>" class="p-3.5 block hover:bg-slate-50 transition <?= $n['is_read'] ? 'opacity-70' : 'bg-emerald-50/40' ?>">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-fpi-800 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                            <i class="fa-solid <?= $n['type'] === 'alert' ? 'fa-triangle-exclamation text-red-600' : 'fa-bell text-emerald-700' ?>"></i>
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <h5 class="text-xs font-bold text-slate-900 truncate"><?= e($n['title']) ?></h5>
                                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5"><?= e($n['message']) ?></p>
                                            <span class="text-[10px] text-slate-400 mt-1 block"><?= time_ago($n['created_at']) ?></span>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-6 text-center text-xs text-slate-400">
                                    No notifications received yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="user-menu-btn" class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none">
                        <div class="w-8 h-8 rounded-lg bg-fpi-800 text-white font-bold text-xs flex items-center justify-center">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <span class="hidden md:inline-block text-xs font-bold text-slate-800 max-w-[120px] truncate"><?= e($user['name']) ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 p-2 space-y-1">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900 truncate"><?= e($user['name']) ?></p>
                            <p class="text-[10px] text-slate-400 truncate"><?= e($user['email']) ?></p>
                        </div>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-gauge-high text-slate-400"></i> Admin Console
                            </a>
                            <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-sliders text-slate-400"></i> Settings
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/student/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-gauge-high text-slate-400"></i> Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/student/profile.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-regular fa-user text-slate-400"></i> Profile
                            </a>
                        <?php endif; ?>
                        <div class="border-t border-slate-100 pt-1">
                            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Dynamic High-Priority Alert Banner if active -->
        <?php if ($criticalAlert): ?>
        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white text-xs py-2 px-4 shadow-sm flex items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-white text-red-700 font-extrabold text-[10px] tracking-wider uppercase flex-shrink-0 animate-pulse">
                    URGENT CAMPUS ALERT
                </span>
                <span class="font-bold truncate"><?= e($criticalAlert['title']) ?>:</span>
                <span class="truncate text-white/90"><?= e($criticalAlert['message']) ?></span>
                <span class="opacity-80 text-[11px] hidden md:inline">&bull; <?= e($criticalAlert['location']) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/student/alerts.php" class="text-[11px] bg-white/20 hover:bg-white/30 text-white font-bold px-2.5 py-0.5 rounded whitespace-nowrap transition">
                Details &rarr;
            </a>
        </div>
        <?php endif; ?>

        <!-- SCROLLABLE PAGE WORKSPACE -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100/70">
            
            <!-- Flash Message Toast Container -->
            <?php if ($flashMsg = display_flash()): ?>
                <div class="max-w-7xl mx-auto mb-4">
                    <?= $flashMsg ?>
                </div>
            <?php endif; ?>

            <!-- Real-time Portal Content Starts Here -->
            <div class="max-w-7xl mx-auto space-y-6">
<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Authenticated Portal Shell Component (Header, Sidebar, Notifications)
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

require_login();

$user = current_user();
$unreadNotifCount = get_unread_notification_count($user['id']);

$pdo = getDB();
// Fetch latest 5 notifications for dropdown
$recentNotifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentNotifs->execute([$user['id']]);
$userNotifications = $recentNotifs->fetchAll();

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$isAdminPortal = ($currentDir === 'admin');

$activeAlerts = get_active_alerts($user['role'] === 'student' ? 'Students' : ($user['role'] === 'staff' ? 'Staff' : 'Everyone'));
$criticalAlert = null;
foreach ($activeAlerts as $a) {
    if ($a['severity'] === 'Critical' || $a['severity'] === 'High') {
        $criticalAlert = $a;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= APP_NAME ?> - <?= INSTITUTION_NAME ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        fpi: {
                            900: '#022c22',
                            800: '#064e3b',
                            700: '#047857',
                            600: '#059669',
                            500: '#10b981',
                            gold: '#d97706',
                            goldlight: '#f59e0b',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="flex h-full overflow-hidden text-slate-800 antialiased">

    <!-- Mobile Sidebar Drawer Backdrop -->
    <div id="portal-mobile-backdrop" class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden hidden" onclick="togglePortalSidebar()"></div>

    <!-- SIDEBAR COMPONENT -->
    <aside id="portal-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-fpi-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static flex-shrink-0 border-r border-emerald-950 shadow-2xl lg:shadow-none">
        
        <!-- Sidebar Brand -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-emerald-900/60 bg-emerald-950/40">
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 flex-shrink-0">
                    <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-sm font-black text-white tracking-tight flex items-center gap-1.5">
                        FPI CAMPUS SAFETY
                    </span>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">
                        <?= $isAdminPortal ? 'Command Center' : 'User Portal' ?>
                    </span>
                </div>
            </a>
            <button type="button" onclick="togglePortalSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg focus:outline-none">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- User Quick Card in Sidebar -->
        <div class="p-4 mx-3 my-3 rounded-2xl bg-emerald-950/60 border border-emerald-800/40 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-fpi-700 text-white font-black text-sm flex items-center justify-center flex-shrink-0 shadow">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="overflow-hidden flex-1">
                <h4 class="text-xs font-bold text-white truncate"><?= e($user['name']) ?></h4>
                <p class="text-[10px] text-slate-400 truncate"><?= e($user['department']) ?></p>
                <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[9px] font-extrabold uppercase <?= $user['role'] === 'admin' ? 'bg-amber-400 text-slate-900' : 'bg-emerald-800 text-emerald-200' ?>">
                    <?= strtoupper(e($user['role'])) ?>
                </span>
            </div>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 text-xs font-medium">
            
            <?php if ($isAdminPortal): ?>
                <!-- ADMIN MENU -->
                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Emergency Operations</div>
                
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'dashboard.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-4 text-center text-amber-400"></i>
                    <span>Dashboard Overview</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= ($currentPage === 'reports.php' || $currentPage === 'report-view.php') ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-list-check w-4 text-center text-rose-400"></i>
                    <span>Emergency Reports</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/map.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'map.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-map-location-dot w-4 text-center text-emerald-400"></i>
                    <span>Live Incident Map</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/alerts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'alerts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-tower-broadcast w-4 text-center text-yellow-400"></i>
                    <span>Broadcast Alerts</span>
                </a>

                <div class="pt-4 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administration</div>

                <a href="<?= BASE_URL ?>/admin/users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'users.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-users w-4 text-center text-sky-400"></i>
                    <span>User Management</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/contacts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'contacts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-address-book w-4 text-center text-teal-400"></i>
                    <span>Emergency Directory</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'analytics.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-chart-pie w-4 text-center text-purple-400"></i>
                    <span>Analytics &amp; Trends</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/logs.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'logs.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-shield-halved w-4 text-center text-indigo-400"></i>
                    <span>Audit &amp; Security Logs</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'settings.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-sliders w-4 text-center text-slate-400"></i>
                    <span>System Settings</span>
                </a>

            <?php else: ?>
                <!-- STUDENT / STAFF MENU -->
                
                <!-- Quick Emergency Report Highlight Button -->
                <div class="p-2 mb-2">
                    <a href="<?= BASE_URL ?>/student/report-emergency.php" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-extrabold text-xs shadow-md transition transform hover:scale-[1.02]">
                        <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
                        <span>REPORT EMERGENCY</span>
                    </a>
                </div>

                <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dashboard &amp; Activity</div>

                <a href="<?= BASE_URL ?>/student/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'dashboard.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-gauge-high w-4 text-center text-amber-400"></i>
                    <span>Dashboard Home</span>
                </a>

                <a href="<?= BASE_URL ?>/student/my-reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'my-reports.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-clipboard-list w-4 text-center text-rose-400"></i>
                    <span>My Incident Reports</span>
                </a>

                <a href="<?= BASE_URL ?>/student/alerts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'alerts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-bullhorn w-4 text-center text-yellow-400"></i>
                    <span>Campus Alerts Feed</span>
                </a>

                <a href="<?= BASE_URL ?>/student/notifications.php" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition <?= $currentPage === 'notifications.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-bell w-4 text-center text-sky-400"></i>
                        <span>Notifications</span>
                    </div>
                    <?php if ($unreadNotifCount > 0): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-600 text-white"><?= $unreadNotifCount ?></span>
                    <?php endif; ?>
                </a>

                <div class="pt-4 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Safety Resources</div>

                <a href="<?= BASE_URL ?>/contacts.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'contacts.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-phone-volume w-4 text-center text-teal-400"></i>
                    <span>Emergency Contacts</span>
                </a>

                <a href="<?= BASE_URL ?>/safety-guides.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'safety-guides.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-solid fa-book-medical w-4 text-center text-emerald-400"></i>
                    <span>Safety Protocols</span>
                </a>

                <a href="<?= BASE_URL ?>/student/profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?= $currentPage === 'profile.php' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-slate-300 hover:bg-emerald-900/60 hover:text-white' ?>">
                    <i class="fa-regular fa-user w-4 text-center text-slate-400"></i>
                    <span>My Profile</span>
                </a>

            <?php endif; ?>

        </nav>

        <!-- Sidebar Footer Action -->
        <div class="p-4 border-t border-emerald-950 bg-emerald-950/50 space-y-2">
            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl text-xs font-semibold text-rose-300 hover:bg-rose-950/40 border border-rose-900/40 transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
            </a>
        </div>

    </aside>

    <!-- MAIN PORTAL WRAPPER (TOPBAR + CONTENT) -->
    <div class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
        
        <!-- PORTAL TOPBAR -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 flex-shrink-0 shadow-xs">
            
            <div class="flex items-center space-x-3">
                <button type="button" onclick="togglePortalSidebar()" class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-fpi-800 hover:bg-slate-100 transition focus:outline-none" aria-label="Toggle Navigation">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-tight">
                        <?= isset($pageTitle) ? e($pageTitle) : 'Portal' ?>
                    </h1>
                    <p class="text-[11px] text-slate-400 hidden sm:block">Federal Polytechnic Ilaro &bull; Safety Monitoring Network</p>
                </div>
            </div>

            <!-- Topbar Right Controls -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                
                <!-- Quick Emergency CTA in topbar -->
                <a href="<?= BASE_URL ?>/student/report-emergency.php" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm transition">
                    <i class="fa-solid fa-bolt text-amber-300"></i> Report Incident
                </a>

                <!-- Notifications Dropdown -->
                <div class="relative">
                    <button type="button" id="notif-btn" class="relative p-2.5 rounded-xl text-slate-600 hover:text-fpi-800 hover:bg-slate-100 transition focus:outline-none" title="Notifications">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <?php if ($unreadNotifCount > 0): ?>
                            <span class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-600 text-white text-[9px] font-extrabold flex items-center justify-center animate-pulse">
                                <?= $unreadNotifCount > 9 ? '9+' : $unreadNotifCount ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown Panel -->
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden">
                        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Notifications</h4>
                            <a href="<?= BASE_URL ?>/student/notifications.php" class="text-[11px] font-semibold text-fpi-800 hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                            <?php if (!empty($userNotifications)): ?>
                                <?php foreach ($userNotifications as $n): ?>
                                <a href="<?= !empty($n['link']) ? BASE_URL . '/' . e($n['link']) : '#' ?>" class="p-3.5 block hover:bg-slate-50 transition <?= $n['is_read'] ? 'opacity-70' : 'bg-emerald-50/40' ?>">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-fpi-800 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                            <i class="fa-solid <?= $n['type'] === 'alert' ? 'fa-triangle-exclamation text-red-600' : 'fa-bell text-emerald-700' ?>"></i>
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <h5 class="text-xs font-bold text-slate-900 truncate"><?= e($n['title']) ?></h5>
                                            <p class="text-[11px] text-slate-600 line-clamp-2 mt-0.5"><?= e($n['message']) ?></p>
                                            <span class="text-[10px] text-slate-400 mt-1 block"><?= time_ago($n['created_at']) ?></span>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-6 text-center text-xs text-slate-400">
                                    No notifications received yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button type="button" id="user-menu-btn" class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none">
                        <div class="w-8 h-8 rounded-lg bg-fpi-800 text-white font-bold text-xs flex items-center justify-center">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <span class="hidden md:inline-block text-xs font-bold text-slate-800 max-w-[120px] truncate"><?= e($user['name']) ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <!-- Dropdown -->
                    <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 p-2 space-y-1">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900 truncate"><?= e($user['name']) ?></p>
                            <p class="text-[10px] text-slate-400 truncate"><?= e($user['email']) ?></p>
                        </div>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-gauge-high text-slate-400"></i> Admin Console
                            </a>
                            <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-sliders text-slate-400"></i> Settings
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/student/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-solid fa-gauge-high text-slate-400"></i> Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>/student/profile.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100">
                                <i class="fa-regular fa-user text-slate-400"></i> Profile
                            </a>
                        <?php endif; ?>
                        <div class="border-t border-slate-100 pt-1">
                            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign Out
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Dynamic High-Priority Alert Banner if active -->
        <?php if ($criticalAlert): ?>
        <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white text-xs py-2 px-4 shadow-sm flex items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center space-x-2.5 overflow-hidden">
                <span class="inline-flex items-center px-2 py-0.5 rounded bg-white text-red-700 font-extrabold text-[10px] tracking-wider uppercase flex-shrink-0 animate-pulse">
                    URGENT CAMPUS ALERT
                </span>
                <span class="font-bold truncate"><?= e($criticalAlert['title']) ?>:</span>
                <span class="truncate text-white/90"><?= e($criticalAlert['message']) ?></span>
                <span class="opacity-80 text-[11px] hidden md:inline">&bull; <?= e($criticalAlert['location']) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/student/alerts.php" class="text-[11px] bg-white/20 hover:bg-white/30 text-white font-bold px-2.5 py-0.5 rounded whitespace-nowrap transition">
                Details &rarr;
            </a>
        </div>
        <?php endif; ?>

        <!-- SCROLLABLE PAGE WORKSPACE -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-100/70">
            
            <!-- Flash Message Toast Container -->
            <?php if ($flashMsg = display_flash()): ?>
                <div class="max-w-7xl mx-auto mb-4">
                    <?= $flashMsg ?>
                </div>
            <?php endif; ?>

            <!-- Real-time Portal Content Starts Here -->
            <div class="max-w-7xl mx-auto space-y-6">
