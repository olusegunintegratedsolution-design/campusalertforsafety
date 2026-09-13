<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Notifications Center Page
 */

$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/nav-portal.php';

$userId = $user['id'];
$pdo = getDB();

// Handle Mark All as Read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (validate_csrf()) {
        if ($_POST['action'] === 'mark_all_read') {
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
            set_flash('success', 'All notifications marked as read.');
            header('Location: ' . BASE_URL . '/student/notifications.php');
            exit;
        } elseif ($_POST['action'] === 'mark_single_read' && !empty($_POST['notif_id'])) {
            $nid = (int)$_POST['notif_id'];
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$nid, $userId]);
            header('Location: ' . BASE_URL . '/student/notifications.php');
            exit;
        }
    }
}

// Fetch all notifications for user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto space-y-6">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Notification Center</h2>
            <p class="text-xs text-slate-500 mt-0.5">Real-time alerts, incident status changes, and campus safety dispatches</p>
        </div>
        <?php if ($unreadNotifCount > 0): ?>
        <form action="<?= BASE_URL ?>/student/notifications.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="px-4 py-2 rounded-xl bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-check-double text-emerald-600"></i> Mark All as Read
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notif): ?>
            <div class="p-5 flex items-start gap-4 transition <?= $notif['is_read'] ? 'bg-white hover:bg-slate-50/80' : 'bg-emerald-50/40 hover:bg-emerald-50/60' ?>">
                
                <!-- Type Icon -->
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-base flex-shrink-0 mt-0.5 <?= 
                    $notif['type'] === 'alert' ? 'bg-rose-100 text-rose-600' :
                    ($notif['type'] === 'status_update' ? 'bg-amber-100 text-amber-700' :
                    ($notif['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'))
                ?>">
                    <i class="fa-solid <?= 
                        $notif['type'] === 'alert' ? 'fa-triangle-exclamation' :
                        ($notif['type'] === 'status_update' ? 'fa-clock-rotate-left' :
                        ($notif['type'] === 'success' ? 'fa-circle-check' : 'fa-bell'))
                    ?>"></i>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-hidden">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 truncate <?= $notif['is_read'] ? '' : 'font-extrabold' ?>">
                            <?= e($notif['title']) ?>
                        </h4>
                        <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap">
                            <?= time_ago($notif['created_at']) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        <?= e($notif['message']) ?>
                    </p>
                    
                    <div class="mt-3 flex items-center gap-3">
                        <?php if (!empty($notif['link'])): ?>
                            <a href="<?= BASE_URL . '/' . e($notif['link']) ?>" class="inline-flex items-center gap-1 text-xs font-bold text-fpi-800 hover:text-fpi-900 transition">
                                <span>Open Details</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!$notif['is_read']): ?>
                            <form action="<?= BASE_URL ?>/student/notifications.php" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_single_read">
                                <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
                                <button type="submit" class="text-[11px] text-slate-400 hover:text-slate-600 font-medium">
                                    Mark as read
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-16 text-center text-slate-400">
                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3">
                    <i class="fa-regular fa-bell"></i>
                </div>
                <h3 class="text-base font-bold text-slate-700">No Notifications</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                    You do not have any new safety notices or report updates.
                </p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
