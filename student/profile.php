<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * User Profile & Account Security Page
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/nav-portal.php';

$userId = $user['id'];
$pdo = getDB();

$errors = [];
$successMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $phone = clean_input($_POST['phone'] ?? '');
            $department = clean_input($_POST['department'] ?? '');

            if (empty($phone) || strlen($phone) < 10) {
                $errors[] = 'Please provide a valid emergency phone number.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET phone = ?, department = ? WHERE id = ?");
                $stmt->execute([$phone, $department, $userId]);
                
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['department'] = $department;
                $user = $_SESSION['user'];

                set_flash('success', 'Profile details updated successfully.');
                header('Location: ' . BASE_URL . '/student/profile.php');
                exit;
            }
        } elseif ($action === 'change_password') {
            $currentPwd = $_POST['current_password'] ?? '';
            $newPwd = $_POST['new_password'] ?? '';
            $confirmPwd = $_POST['confirm_password'] ?? '';

            $userQuery = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $userQuery->execute([$userId]);
            $dbHash = $userQuery->fetchColumn();

            if (!password_verify($currentPwd, $dbHash)) {
                $errors[] = 'Your current password was entered incorrectly.';
            } elseif (strlen($newPwd) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPwd !== $confirmPwd) {
                $errors[] = 'New passwords do not match.';
            } else {
                $newHash = password_hash($newPwd, PASSWORD_BCRYPT);
                $updatePwd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePwd->execute([$newHash, $userId]);

                log_activity('PASSWORD_CHANGED', 'User updated account password.', $userId);
                set_flash('success', 'Your password has been changed securely.');
                header('Location: ' . BASE_URL . '/student/profile.php');
                exit;
            }
        }
    }
}
?>

<div class="max-w-4xl mx-auto space-y-8">
    
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Account &amp; Security Profile</h2>
        <p class="text-xs text-slate-500 mt-0.5">Manage your emergency contact telephone and credentials</p>
    </div>

    <!-- Error notice -->
    <?php if (!empty($errors)): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
        <?php foreach ($errors as $err): ?>
            <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-rose-600"></i> <?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Profile Details Form -->
        <div class="md:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-regular fa-id-card text-fpi-800"></i>
                Institutional Identity
            </h3>

            <form action="<?= BASE_URL ?>/student/profile.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Full Name</label>
                    <input type="text" value="<?= e($user['name']) ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs font-semibold cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" value="<?= e($user['email']) ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs font-semibold cursor-not-allowed">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">ID Number</label>
                        <input type="text" value="<?= e($user['id_number']) ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs font-semibold cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">User Role</label>
                        <input type="text" value="<?= strtoupper(e($user['role'])) ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-xs font-semibold cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label for="department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Academic Department</label>
                    <input type="text" id="department" name="department" value="<?= e($user['department']) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Emergency Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    <span class="text-[10px] text-slate-400 mt-1 block">Used by security units for emergency callbacks</span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow transition">
                        Update Contact Information
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Change Password Card -->
        <div class="md:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-lock text-fpi-800"></i>
                Change Password
            </h3>

            <form action="<?= BASE_URL ?>/student/profile.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password">

                <div>
                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                </div>

                <div>
                    <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="Min 6 characters" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-type new password" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
