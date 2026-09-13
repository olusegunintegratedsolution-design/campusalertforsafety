<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Emergency Contacts Directory Page
 */

$pageTitle = 'Emergency Contacts Directory';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$contacts = $pdo->query("SELECT * FROM emergency_contacts ORDER BY priority_order ASC")->fetchAll();

// Group by category
$grouped = [];
foreach ($contacts as $c) {
    $grouped[$c['category']][] = $c;
}
?>

<div class="bg-gradient-to-b from-fpi-900 to-fpi-800 text-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-700/60">
            Official Directory
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mt-3">
            Campus Emergency Hotlines
        </h1>
        <p class="text-slate-300 max-w-2xl text-sm sm:text-base mt-2">
            Direct telephone access to campus security posts, medical emergency dispatch, police headquarters, and municipal fire service.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-12">
    
    <!-- Top Highlights Banner -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-3xl bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-lg flex items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full text-white">Central Security Post</span>
                <h3 class="text-xl font-black mt-2">24/7 Security Operations</h3>
                <p class="text-xs text-white/80 mt-1">Main dispatch desk for all campus distress calls</p>
            </div>
            <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="px-5 py-3 rounded-2xl bg-white text-red-700 font-extrabold text-sm shadow hover:bg-slate-100 transition whitespace-nowrap flex items-center gap-2">
                <i class="fa-solid fa-phone"></i> Call Now
            </a>
        </div>

        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-700 to-fpi-800 text-white shadow-lg flex items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full text-white">Health Centre</span>
                <h3 class="text-xl font-black mt-2">Ambulance &amp; Clinic Emergency</h3>
                <p class="text-xs text-white/80 mt-1">First aid, resuscitation, and emergency transfer</p>
            </div>
            <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="px-5 py-3 rounded-2xl bg-amber-400 text-slate-900 font-extrabold text-sm shadow hover:bg-amber-300 transition whitespace-nowrap flex items-center gap-2">
                <i class="fa-solid fa-ambulance"></i> Call Clinic
            </a>
        </div>
    </div>

    <!-- Grouped Contacts Directory -->
    <?php foreach ($grouped as $category => $items): ?>
    <div class="space-y-4">
        <div class="flex items-center gap-3 border-b border-slate-200 pb-3">
            <span class="w-3 h-3 rounded-full bg-emerald-700"></span>
            <h3 class="text-lg font-extrabold text-slate-900 uppercase tracking-wider"><?= e($category) ?> Response Units</h3>
            <span class="text-xs text-slate-400 font-medium">(<?= count($items) ?> units)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-fpi-800 border border-emerald-200">
                            <?= e($item['category']) ?>
                        </span>
                        <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <?= e($item['availability']) ?>
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-slate-900 leading-tight"><?= e($item['name']) ?></h4>
                    <p class="text-xs text-slate-500 mt-1"><?= e($item['department']) ?></p>
                    
                    <?php if (!empty($item['email'])): ?>
                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5 truncate">
                        <i class="fa-regular fa-envelope text-slate-400"></i>
                        <a href="mailto:<?= e($item['email']) ?>" class="hover:text-fpi-800 transition"><?= e($item['email']) ?></a>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-2">
                    <a href="tel:<?= str_replace(' ', '', $item['phone']) ?>" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-sm transition">
                        <i class="fa-solid fa-phone"></i> <?= e($item['phone']) ?>
                    </a>
                    <?php if (!empty($item['alt_phone'])): ?>
                    <a href="tel:<?= str_replace(' ', '', $item['alt_phone']) ?>" class="w-full inline-flex items-center justify-center gap-2 py-2 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition">
                        <i class="fa-solid fa-phone text-slate-400"></i> Alt: <?= e($item['alt_phone']) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
