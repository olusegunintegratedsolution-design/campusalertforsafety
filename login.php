<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * User Login Page
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already authenticated
redirect_if_logged_in();

$redirectParam = clean_input($_GET['redirect'] ?? '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please submit the form again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Please enter both your institutional email address and password.';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {

                if ($user['status'] !== 'active') {

                    $errors[] = 'Your account has been deactivated by campus security administration.';

                } elseif ($user['role'] === 'admin') {

                    // Administrators must use the dedicated Admin Portal login.
                    $errors[] = 'Administrator accounts must sign in through the Admin Portal.';

                } else {

                    login_user($user);

                    set_flash('success', 'Welcome back, ' . $user['name'] . '!');

                    // Redirect handling
                    if ($redirectParam === 'report') {
                        header('Location: ' . BASE_URL . '/student/report-emergency.php');
                    } else {
                        header('Location: ' . BASE_URL . '/student/dashboard.php');
                    }

                    exit;
                }

            } else {
                $errors[] = 'Invalid email address or password. Please try again.';
            }
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        
        <!-- Header -->
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4">
                <img src="<?= BASE_URL ?>/assets/images/logo.svg"
                     alt="FPI Logo"
                     class="w-full h-full object-contain">
            </div>

            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Sign in to Campus Safety
            </h2>

            <p class="text-xs text-slate-500 mt-1">
                Access your emergency reporting dashboard, notifications, and alerts
            </p>
        </div>

        <!-- Error box -->
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

        <!-- Form Card -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50">

            <form action="<?= BASE_URL ?>/login.php<?= $redirectParam ? '?redirect=' . urlencode($redirectParam) : '' ?>"
                  method="POST"
                  class="space-y-5">

                <?= csrf_field() ?>

                <!-- Email -->
                <div>
                    <label for="email"
                           class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Institutional Email Address
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-envelope"></i>
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            value="<?= e($_POST['email'] ?? '') ?>"
                            placeholder="matric_no@ilaropoly.edu.ng"
                            autocomplete="username"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password"
                           class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Password
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition"
                        >

                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-regular fa-eye" id="pwd-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div>
                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Sign In to Portal
                    </button>
                </div>

            </form>

            <!-- Register -->
            <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
                Don't have a registered campus safety profile?

                <a href="<?= BASE_URL ?>/register.php"
                   class="font-bold text-fpi-800 hover:text-fpi-900 ml-1">
                    Create Account
                </a>
            </div>

            <!-- Admin Login -->
            <div class="mt-5 text-center">
                <p class="text-xs text-slate-400 mb-2">
                    Are you an authorized administrator?
                </p>

                <a
                    href="<?= BASE_URL ?>/admin/login.php"
                    class="inline-flex items-center justify-center gap-2 text-xs font-bold text-fpi-800 hover:text-fpi-900 transition">
                    <i class="fa-solid fa-shield-halved"></i>
                    Administrator Login
                </a>
            </div>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>