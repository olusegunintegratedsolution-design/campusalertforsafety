<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System
 *
 * Dedicated Administrator Login
 */

require_once __DIR__ . '/../includes/auth.php';

/*
|--------------------------------------------------------------------------
| If already logged in as admin, go straight to dashboard
|--------------------------------------------------------------------------
*/

if (is_logged_in() && is_admin()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$errors = [];
$email = '';

/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please refresh the page.';
    } else {

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

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
                |--------------------------------------------------------------------------
                | Only the designated administrator account is allowed
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM users
                    WHERE email = ?
                      AND role = 'admin'
                      AND status = 'active'
                    LIMIT 1
                ");

                $stmt->execute([
                    $email
                ]);

                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                /*
                |--------------------------------------------------------------------------
                | Check administrator account
                |--------------------------------------------------------------------------
                */

                if (!$admin) {

                    $errors[] = 'Invalid administrator credentials.';

                } elseif (!password_verify($password, $admin['password'])) {

                    $errors[] = 'Invalid administrator credentials.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Successful administrator login
                    |--------------------------------------------------------------------------
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
                | Do not expose database errors to the public.
                */

                $errors[] = 'Unable to process administrator login.';
            }
        }
    }
}

$pageTitle = 'Administrator Login';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">

    <div class="max-w-md w-full">

        <!-- Header -->

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


        <!-- Errors -->

        <?php if (!empty($errors)): ?>

            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">

                <?php foreach ($errors as $error): ?>

                    <div class="flex items-center gap-2 text-sm">

                        <i class="fa-solid fa-circle-xmark"></i>

                        <span>
                            <?= e($error) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- Login Card -->

        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8">

            <form
                method="POST"
                action="<?= BASE_URL ?>/admin/login.php"
                class="space-y-6"
            >

                <?= csrf_field() ?>


                <!-- Email -->

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


                <!-- Password -->

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


                <!-- Submit -->

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
                    href="<?= BASE_URL ?>/login.php"
                    class="text-sm font-semibold text-slate-600 hover:text-emerald-700"
                >
                    ← Back to normal login
                </a>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>