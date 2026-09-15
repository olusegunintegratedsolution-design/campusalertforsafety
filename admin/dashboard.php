<?php
/**
 * Campus Alert - Secure Administrator Command Center Login
 */
require_once __DIR__ . '/../includes/auth.php';

if (is_admin()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $errors[] = 'Enter the administrator username and password.';
        } elseif (!admin_credentials_valid($username, $password)) {
            $errors[] = 'Invalid administrator credentials.';
        } else {
            try {
                login_admin();
                set_flash('success', 'Welcome to the Campus Safety Command Center.');
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Administrator profile is not configured correctly. Please contact the system owner.';
            }
        }
    }
}

$pageTitle = 'Administrator Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[82vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full">
        <div class="text-center mb-7">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-fpi-900 p-3 shadow-xl shadow-emerald-950/20">
                <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-black uppercase tracking-widest">
                <i class="fa-solid fa-shield-halved"></i> Restricted Access
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-3">Admin Command Center</h1>
            <p class="text-sm text-slate-500 mt-2">Authorized administrator access only.</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
            <?php foreach ($errors as $err): ?>
                <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark"></i><?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-2xl shadow-slate-200/60">
            <form method="POST" class="space-y-5" autocomplete="off">
                <?= csrf_field() ?>
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Administrator Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fa-solid fa-user-shield"></i></span>
                        <input type="text" id="username" name="username" required autocomplete="username" class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800" placeholder="Administrator username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Administrator Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" required autocomplete="current-password" class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800" placeholder="Enter secure password">
                        <button type="button" onclick="toggleAdminPassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"><i class="fa-regular fa-eye" id="admin-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-fpi-900 hover:bg-fpi-800 text-white font-bold text-sm shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Enter Command Center
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <a href="<?= BASE_URL ?>/index.php" class="text-xs font-bold text-slate-500 hover:text-fpi-800"><i class="fa-solid fa-arrow-left mr-1"></i> Return to Campus Safety</a>
            </div>
        </div>
        <p class="text-center text-[10px] text-slate-400 mt-5">This portal is monitored and administrator activity is recorded.</p>
    </div>
</div>

<script>
function toggleAdminPassword() {
    const input = document.getElementById('password');
    const eye = document.getElementById('admin-eye');
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    eye.classList.toggle('fa-eye', visible);
    eye.classList.toggle('fa-eye-slash', !visible);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>