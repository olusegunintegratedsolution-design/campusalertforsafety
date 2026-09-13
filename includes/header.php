<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Public Header Component
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$activeAlerts = get_active_alerts();
$criticalAlert = null;
foreach ($activeAlerts as $alt) {
    if ($alt['severity'] === 'Critical' || $alt['severity'] === 'High') {
        $criticalAlert = $alt;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
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
    
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="flex flex-col min-h-screen text-slate-800 antialiased selection:bg-emerald-800 selection:text-white">

    <!-- Top Emergency Hotline Bar -->
    <div class="bg-fpi-900 text-slate-200 text-xs py-2 px-4 border-b border-emerald-900/60 z-30">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center text-emerald-400 font-semibold">
                    <i class="fa-solid fa-graduation-cap mr-1.5"></i> <?= INSTITUTION_NAME ?>
                </span>
                <span class="hidden md:inline-block text-slate-400">|</span>
                <span class="hidden md:inline-block text-slate-300">Campus Emergency Safety Directorate</span>
            </div>
            <div class="flex items-center space-x-5">
                <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="flex items-center gap-1.5 text-amber-400 hover:text-amber-300 font-bold transition">
                    <i class="fa-solid fa-phone-volume text-rose-400 animate-pulse"></i> 
                    <span>24/7 Security: <?= EMERGENCY_HOTLINE ?></span>
                </a>
                <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="hidden sm:flex items-center gap-1.5 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-hospital text-emerald-400"></i> 
                    <span>Clinic: <?= CLINIC_HOTLINE ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Campus High-Priority Alert Marquee/Banner -->
    <?php if ($criticalAlert): ?>
    <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white text-sm py-2.5 px-4 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center space-x-3 overflow-hidden">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded bg-white text-rose-700 font-extrabold text-xs tracking-wider uppercase flex-shrink-0 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> ACTIVE ALERT
                </span>
                <p class="font-medium truncate text-white/95">
                    <strong><?= e($criticalAlert['title']) ?>:</strong> <?= e($criticalAlert['message']) ?>
                    <span class="opacity-80 text-xs ml-2">(<?= e($criticalAlert['location']) ?>)</span>
                </p>
            </div>
            <a href="<?= BASE_URL ?>/safety-guides.php" class="text-xs bg-white/20 hover:bg-white/30 text-white font-semibold px-3 py-1 rounded-full whitespace-nowrap transition flex-shrink-0">
                Safety Info &rarr;
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Navigation Bar -->
    <header class="bg-white/95 backdrop-blur border-b border-slate-200 sticky <?= $criticalAlert ? 'top-[41px]' : 'top-0' ?> z-40 transition-all shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Institutional Brand / Logo -->
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-12 h-12 flex-shrink-0 transition-transform group-hover:scale-105">
                        <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-extrabold text-fpi-900 tracking-tight leading-none group-hover:text-fpi-700 transition">CAMPUS SAFETY</span>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-fpi-800 text-amber-400 rounded">CES</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium tracking-wide leading-tight mt-0.5">Federal Polytechnic Ilaro</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="<?= BASE_URL ?>/index.php" class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $currentPage === 'index.php' ? 'text-fpi-800 bg-emerald-50 font-semibold' : 'text-slate-600 hover:text-fpi-800 hover:bg-slate-100' ?>">Home</a>
                    <a href="<?= BASE_URL ?>/about.php" class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $currentPage === 'about.php' ? 'text-fpi-800 bg-emerald-50 font-semibold' : 'text-slate-600 hover:text-fpi-800 hover:bg-slate-100' ?>">About System</a>
                    <a href="<?= BASE_URL ?>/safety-guides.php" class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $currentPage === 'safety-guides.php' ? 'text-fpi-800 bg-emerald-50 font-semibold' : 'text-slate-600 hover:text-fpi-800 hover:bg-slate-100' ?>">Safety Guides</a>
                    <a href="<?= BASE_URL ?>/contacts.php" class="px-3.5 py-2 rounded-lg text-sm font-medium transition <?= $currentPage === 'contacts.php' ? 'text-fpi-800 bg-emerald-50 font-semibold' : 'text-slate-600 hover:text-fpi-800 hover:bg-slate-100' ?>">Emergency Contacts</a>
                </nav>

                <!-- Auth & Action Buttons -->
                <div class="hidden sm:flex items-center space-x-3">
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-fpi-800 text-white hover:bg-fpi-900 transition shadow-sm">
                                <i class="fa-solid fa-gauge-high text-amber-400"></i> Admin Console
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/student/dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-fpi-800 text-white hover:bg-fpi-900 transition shadow-sm">
                                <i class="fa-solid fa-user-shield text-emerald-400"></i> My Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout.php" class="p-2 text-slate-500 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition text-sm" title="Logout">
                            <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="px-4 py-2 text-sm font-semibold text-fpi-800 hover:text-fpi-900 transition">
                            Sign In
                        </a>
                        <a href="<?= BASE_URL ?>/register.php" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 transition">
                            Register
                        </a>
                    <?php endif; ?>

                    <!-- Prominent Emergency CTA Button -->
                    <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-md shadow-red-500/20 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-bolt-lightning text-amber-300"></i> Report Emergency
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex sm:hidden items-center space-x-2">
                    <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="p-2.5 rounded-xl bg-red-600 text-white text-xs font-bold shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </a>
                    <button type="button" id="mobile-menu-btn" class="p-2.5 rounded-xl text-slate-600 hover:text-fpi-800 hover:bg-slate-100 transition focus:outline-none" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobile-menu" class="hidden sm:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl">
            <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100 <?= $currentPage === 'index.php' ? 'bg-emerald-50 text-fpi-800 font-bold' : '' ?>">Home</a>
            <a href="<?= BASE_URL ?>/about.php" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100 <?= $currentPage === 'about.php' ? 'bg-emerald-50 text-fpi-800 font-bold' : '' ?>">About System</a>
            <a href="<?= BASE_URL ?>/safety-guides.php" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100 <?= $currentPage === 'safety-guides.php' ? 'bg-emerald-50 text-fpi-800 font-bold' : '' ?>">Safety Guides</a>
            <a href="<?= BASE_URL ?>/contacts.php" class="block px-3 py-2.5 rounded-xl text-base font-medium text-slate-700 hover:bg-slate-100 <?= $currentPage === 'contacts.php' ? 'bg-emerald-50 text-fpi-800 font-bold' : '' ?>">Emergency Contacts</a>
            
            <div class="pt-3 border-t border-slate-200 space-y-2">
                <?php if (is_logged_in()): ?>
                    <a href="<?= is_admin() ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/student/dashboard.php' ?>" class="block w-full text-center py-2.5 px-4 rounded-xl text-sm font-bold bg-fpi-800 text-white">
                        <i class="fa-solid fa-gauge-high mr-2"></i> Open Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" class="block w-full text-center py-2.5 px-4 rounded-xl text-sm font-semibold text-rose-600 bg-rose-50">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Log Out
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="block w-full text-center py-2.5 px-4 rounded-xl text-sm font-bold border border-slate-300 text-slate-700 hover:bg-slate-50">
                        Sign In
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="block w-full text-center py-2.5 px-4 rounded-xl text-sm font-bold bg-fpi-800 text-white hover:bg-fpi-900">
                        Register Account
                    </a>
                <?php endif; ?>
                <a href="<?= is_logged_in() ? BASE_URL . '/student/report-emergency.php' : BASE_URL . '/login.php?redirect=report' ?>" class="block w-full text-center py-3 px-4 rounded-xl text-sm font-bold bg-red-600 text-white shadow-md">
                    <i class="fa-solid fa-bolt mr-1.5"></i> REPORT EMERGENCY NOW
                </a>
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="flex-1">
        <?php if ($flashHtml = display_flash()): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
                <?= $flashHtml ?>
            </div>
        <?php endif; ?>
