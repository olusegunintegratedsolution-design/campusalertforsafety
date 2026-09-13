<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Campus Alerts Feed Page
 */

$pageTitle = 'Campus Alerts Feed';
require_once __DIR__ . '/../includes/nav-portal.php';

$pdo = getDB();
$targetAudience = $user['role'] === 'student' ? 'Students' : ($user['role'] === 'staff' ? 'Staff' : 'Everyone');

// Fetch active alerts
$activeStmt = $pdo->prepare("
    SELECT a.*, u.name as publisher_name 
    FROM alerts a 
    LEFT JOIN users u ON a.created_by = u.id 
    WHERE a.is_active = 1 
      AND (a.expiry_time IS NULL OR a.expiry_time > NOW())
      AND (a.target_audience = 'Everyone' OR a.target_audience = ?)
    ORDER BY a.created_at DESC
");
$activeStmt->execute([$targetAudience]);
$liveAlerts = $activeStmt->fetchAll();

// Fetch past/archived alerts
$pastStmt = $pdo->prepare("
    SELECT a.*, u.name as publisher_name 
    FROM alerts a 
    LEFT JOIN users u ON a.created_by = u.id 
    WHERE a.is_active = 0 OR (a.expiry_time IS NOT NULL AND a.expiry_time <= NOW())
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$pastStmt->execute();
$pastAlerts = $pastStmt->fetchAll();
?>

<div class="space-y-8">
    
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Campus Safety Broadcasts &amp; Alerts</h2>
        <p class="text-xs text-slate-500 mt-0.5">Official emergency bulletins dispatched by Federal Polytechnic Ilaro Security Unit</p>
    </div>

    <!-- Active Alerts Section -->
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                Active Advisories (<?= count($liveAlerts) ?>)
            </h3>
        </div>

        <?php if (!empty($liveAlerts)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($liveAlerts as $alt): ?>
                <div class="bg-white rounded-3xl p-6 sm:p-7 border-2 <?= $alt['severity'] === 'Critical' ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' ?> shadow-sm hover:shadow-md transition space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <?= get_severity_badge($alt['severity']) ?>
                        <span class="text-xs text-slate-400 font-medium">
                            <i class="fa-regular fa-clock mr-1"></i> <?= time_ago($alt['created_at']) ?>
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-fpi-800 uppercase tracking-wide bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                            <?= e($alt['emergency_type']) ?>
                        </span>
                        <h4 class="text-lg font-black text-slate-900 leading-snug pt-1">
                            <?= e($alt['title']) ?>
                        </h4>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        <?= nl2br(e($alt['message'])) ?>
                    </p>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1 font-semibold text-emerald-800">
                            <i class="fa-solid fa-location-dot text-rose-500"></i>
                            <?= e($alt['location']) ?>
                        </span>
                        <span class="text-[11px] text-slate-400">
                            Target: <strong><?= e($alt['target_audience']) ?></strong>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-2xl mb-4">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <h4 class="text-base font-bold text-slate-800">No Active Emergency Alerts</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                    No active campus crisis bulletins or warnings have been issued for your user group at this time.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past / Expired Alerts Section -->
    <?php if (!empty($pastAlerts)): ?>
    <div class="space-y-4 pt-6">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
            Past / Concluded Advisories (Archive)
        </h3>

        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden divide-y divide-slate-100">
            <?php foreach ($pastAlerts as $pa): ?>
            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 opacity-75 hover:opacity-100 transition">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase bg-slate-100 text-slate-600 px-2 py-0.5 rounded">Archived</span>
                        <h5 class="text-xs font-bold text-slate-800"><?= e($pa['title']) ?></h5>
                    </div>
                    <p class="text-[11px] text-slate-500 line-clamp-1"><?= e($pa['message']) ?></p>
                </div>
                <div class="text-[11px] text-slate-400 sm:text-right flex-shrink-0">
                    <div><?= e($pa['location']) ?></div>
                    <div class="text-[10px]"><?= date('M j, Y', strtotime($pa['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
