<?php
/**
 * Temporary administrator password recovery.
 * Requires ADMIN_RESET_KEY in the deployment environment.
 */
require_once __DIR__ . '/../includes/auth.php';

$errors = [];
$success = '';
$generatedPassword = '';
$resetKey = trim((string) envValue('ADMIN_RESET_KEY', ''));

if ($resetKey === '') {
    $errors[] = 'Administrator password recovery is not enabled. Set ADMIN_RESET_KEY in the deployment environment.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $submittedKey = trim((string) ($_POST['reset_key'] ?? ''));

    if ($submittedKey === '' || !hash_equals($resetKey, $submittedKey)) {
        $errors[] = 'Invalid password recovery key.';
    } elseif (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please refresh the page.';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("\n                SELECT id, email, role, status\n                FROM users\n                WHERE LOWER(TRIM(email)) = ?\n                LIMIT 1\n            ");
            $stmt->execute(['admin@ilaropoly.edu.ng']);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                $errors[] = 'Administrator account was not found in the database.';
            } elseif ($admin['role'] !== 'admin') {
                $errors[] = 'The administrator account does not have the admin role.';
            } elseif ($admin['status'] !== 'active') {
                $errors[] = 'The administrator account is not active.';
            } else {
                $generatedPassword = 'FPI-' . strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
                $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);

                if ($passwordHash === false) {
                    $errors[] = 'Unable to generate the administrator password.';
                    $generatedPassword = '';
                } else {
                    $update = $pdo->prepare("\n                        UPDATE users\n                        SET password = ?\n                        WHERE id = ? AND role = 'admin' AND status = 'active'\n                    ");
                    $update->execute([$passwordHash, $admin['id']]);

                    $verify = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                    $verify->execute([$admin['id']]);
                    $storedHash = (string) $verify->fetchColumn();

                    if (!password_verify($generatedPassword, $storedHash)) {
                        $errors[] = 'The administrator password could not be verified after the reset.';
                        $generatedPassword = '';
                    } else {
                        $success = 'Administrator password successfully reset. Save the password below, then remove ADMIN_RESET_KEY from Vercel and redeploy.';
                    }
                }
            }
        } catch (Throwable $e) {
            $errors[] = 'Unable to reset the administrator password. Check the database connection and deployment environment variables.';
        }
    }
}

$pageTitle = 'Administrator Password Recovery';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4">
                <img src="<?= e(BASE_URL) ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Administrator Password Recovery</h2>
            <p class="text-xs text-slate-500 mt-2">Temporary recovery for the designated Campus Safety administrator.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark"></i><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success && $generatedPassword !== ''): ?>
            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900">
                <p class="text-sm font-bold mb-3"><?= e($success) ?></p>
                <div class="bg-white border border-emerald-200 rounded-xl p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">New administrator password</p>
                    <code class="block text-lg font-black tracking-wider break-all"><?= e($generatedPassword) ?></code>
                </div>
                <p class="text-xs mt-3">This password is displayed only on this response. Store it securely.</p>
                <a href="<?= e(BASE_URL) ?>/admin/login.php" class="mt-4 inline-flex items-center gap-2 font-bold text-sm text-emerald-800 hover:text-emerald-950">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Continue to Admin Login
                </a>
            </div>
        <?php elseif (empty($success)): ?>
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl">
                <form method="POST" action="<?= e(BASE_URL) ?>/admin/reset-password.php" class="space-y-5">
                    <?= csrf_field() ?>
                    <div>
                        <label for="reset_key" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Recovery Key</label>
                        <input type="password" id="reset_key" name="reset_key" required autocomplete="off" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-sm" placeholder="Enter your recovery key">
                    </div>
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm transition">
                        <i class="fa-solid fa-key mr-2"></i> Generate New Admin Password
                    </button>
                </form>
                <div class="mt-5 text-center">
                    <a href="<?= e(BASE_URL) ?>/admin/login.php" class="text-xs font-bold text-fpi-800 hover:text-fpi-900">Back to Admin Login</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
