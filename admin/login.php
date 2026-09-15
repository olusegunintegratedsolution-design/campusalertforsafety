<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System
 *
 * Dedicated Administrator Login
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
        $errors[] = 'Security token invalid or expired. Please refresh the page.';
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

                /*
                 * IMPORTANT:
                 * First find the account by email ONLY.
                 *
                 * We deliberately do not put role/status in this query.
                 * That lets us determine whether the problem is:
                 * - account missing
                 * - wrong role
                 * - inactive account
                 * - wrong password
                 */

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM users
                    WHERE LOWER(TRIM(email)) = ?
                    LIMIT 1
                ");

                $stmt->execute([$email]);

                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$admin) {

                    $errors[] = 'DEBUG: Administrator email was not found in the database.';

                } elseif (strtolower(trim((string)$admin['role'])) !== 'admin') {

                    $errors[] = 'DEBUG: Account exists, but its role is "' .
                        e((string)$admin['role']) .
                        '". It must be "admin".';

                } elseif (strtolower(trim((string)$admin['status'])) !== 'active') {

                    $errors[] = 'DEBUG: Administrator account exists, but status is "' .
                        e((string)$admin['status']) .
                        '". It must be "active".';

                } elseif (empty($admin['password'])) {

                    $errors[] = 'DEBUG: Administrator password field is empty.';

                } elseif (!password_verify($password, (string)$admin['password'])) {

                    $errors[] = 'DEBUG: Administrator account was found, but the password does not match the stored password hash.';

                } else {

                    /*
                     * SUCCESS
                     */

                    login_user($admin);

                    set_flash(
                        'success',
                        'Welcome to the Campus Safety Command Center.'
                    );

                    header(
                        'Location: ' . BASE_URL . '/admin/dashboard.php'
                    );

                    exit;
                }

            } catch (Throwable $e) {

                /*
                 * TEMPORARY DIAGNOSTIC MESSAGE.
                 *
                 * After the login is working, replace this with the
                 * generic production message.
                 */

                $errors[] = 'DEBUG DATABASE ERROR: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Administrator Login';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">

    <div class="max-w-md w-full">

        <div class="text-center mb-8">

            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-700 flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>

            <h1 class="text-3xl font-black text-slate-900">
                Administrator Login
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Campus Safety Command Center
            </p>

        </div>

        <?php if (!empty($errors)): ?>

            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">

                <?php foreach ($errors as $error): ?>

                    <div class="flex items-start gap-2 text-sm mb-2 last:mb-0">

                        <i class="fa-solid fa-circle-xmark mt-0.5"></i>

                        <span>
                            <?= e($error) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8">

            <form
                method="POST"
                action="<?= e(BASE_URL) ?>/admin/login.php"
                class="space-y-6"
            >

                <?= csrf_field() ?>

                <div>

                    <label
                        for="email"
                        class="block text-sm font-bold text-slate-700 mb-2"
                    >
                        Administrator Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= e($email) ?>"
                        autocomplete="username"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 outline-none"
                        placeholder="admin@ilaropoly.edu.ng"
                    >

                </div>

                <div>

                    <label
                        for="password"
                        class="block text-sm font-bold text-slate-700 mb-2"
                    >
                        Administrator Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 outline-none"
                        placeholder="Enter administrator password"
                    >

                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold transition"
                >
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Enter Command Center
                </button>

            </form>

            <div class="border-t border-slate-100 mt-6 pt-6 text-center">

                <a
                    href="<?= e(BASE_URL) ?>/login.php"
                    class="text-sm font-semibold text-slate-600 hover:text-emerald-700"
                >
                    ← Back to normal login
                </a>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
