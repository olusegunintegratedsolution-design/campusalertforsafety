<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Dedicated Administrator Login
 *
 * Administrator authentication is handled by the existing users table.
 * The admin account must have role='admin' and status='active'.
 */

require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in() && is_admin()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please refresh the page and try again.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '') {
            $errors[] = 'Please enter your administrator email.';
        }

        if ($password === '') {
            $errors[] = 'Please enter your administrator password.';
        }

        if (empty($errors)) {
            try {
                $pdo = getDB();

                // Only an active database user with the admin role can enter
                // the Command Center. Student/staff accounts are rejected.
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM users
                    WHERE email = ?
                      AND role = 'admin'
                      AND status = 'active'
                    LIMIT 1
                ");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$admin || !password_verify($password, (string)$admin['password'])) {
                    $errors[] = 'Invalid administrator credentials.';
                } else {
                    login_user($admin);

                    set_flash('success', 'Welcome to the Campus Safety Command Center.');
                    header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    exit;
                }
            } catch (Throwable $e) {
                // Do not expose database/connection details to the browser.
                $errors[] = 'Unable to process administrator login at this time.';
            }
        }
    }
}

$pageTitle = 'Administrator Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">

        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-fpi-900 flex items-center justify-center shadow-xl">
                <i class="fa-solid fa-shield-halved text-amber-400 text-2xl"></i>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-black uppercase tracking-widest">
                <i class="fa-solid fa-lock"></i> Restricted Access
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-3">
                Administrator Login
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Campus Safety Command Center
            </p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                        <?= e($err) ?>
                    </p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50">
            <form action="<?= BASE_URL ?>/admin/login.php" method="POST" class="space-y-5" autocomplete="off">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Administrator Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" required
                               value="<?= e($email) ?>"
                               placeholder="admin@ilaropoly.edu.ng"
                               autocomplete="username"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Administrator Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your secure password"
                               autocomplete="current-password"
                               class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition">
                        <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                                aria-label="Show or hide password">
                            <i class="fa-regular fa-eye" id="pwd-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Enter Command Center
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <a href="<?= BASE_URL ?>/login.php"
                   class="text-xs font-bold text-fpi-800 hover:text-fpi-900">
                    ← Back to normal login
                </a>
            </div>

            <p class="text-center text-[10px] text-slate-400 mt-4">
                Authorized administrators only. Administrator activity is recorded.
            </p>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('password');
    const eye = document.getElementById('pwd-eye');

    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}
</script>


<div class="text-center mt-4">
    <a href="<?= e(BASE_URL) ?>/admin/reset-password.php" class="text-xs font-bold text-amber-700 hover:text-amber-800">
        <i class="fa-solid fa-key mr-1"></i> Administrator password recovery
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
