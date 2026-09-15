<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Administrator Login
 */

require_once __DIR__ . '/../includes/auth.php';

/*
|--------------------------------------------------------------------------
| Already logged-in administrator
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
| Admin Login CSRF Token
|--------------------------------------------------------------------------
|
| We use a secure cookie for this login page so the token does not depend
| on the PHP session storage during the login request.
|
*/

$csrfCookieName = 'ces_admin_csrf';

if (empty($_COOKIE[$csrfCookieName])) {

    $adminCsrfToken = bin2hex(random_bytes(32));

    setcookie(
        $csrfCookieName,
        $adminCsrfToken,
        [
            'expires'  => time() + 3600,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );

} else {

    $adminCsrfToken = $_COOKIE[$csrfCookieName];
}


/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['admin_csrf_token'] ?? '';
    $cookieToken = $_COOKIE[$csrfCookieName] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate CSRF
    |--------------------------------------------------------------------------
    */

    if (
        empty($submittedToken) ||
        empty($cookieToken) ||
        !hash_equals($cookieToken, $submittedToken)
    ) {

        $errors[] = 'Security token invalid or expired. Please refresh the page and try again.';

    } else {

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | Validate Fields
        |--------------------------------------------------------------------------
        */

        if ($email === '') {
            $errors[] = 'Please enter your administrator email.';
        }

        if ($password === '') {
            $errors[] = 'Please enter your administrator password.';
        }


        /*
        |--------------------------------------------------------------------------
        | Authenticate Administrator
        |--------------------------------------------------------------------------
        */

        if (empty($errors)) {

            try {

                $pdo = getDB();

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM users
                    WHERE email = ?
                    AND role = 'admin'
                    LIMIT 1
                ");

                $stmt->execute([$email]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$user) {

                    $errors[] = 'Invalid administrator credentials.';

                } elseif ($user['status'] !== 'active') {

                    $errors[] = 'This administrator account is inactive.';

                } elseif (!password_verify($password, $user['password'])) {

                    $errors[] = 'Invalid administrator credentials.';

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Successful Administrator Login
                    |--------------------------------------------------------------------------
                    */

                    login_user($user);

                    /*
                    |--------------------------------------------------------------------------
                    | Remove admin CSRF cookie after successful login
                    |--------------------------------------------------------------------------
                    */

                    setcookie(
                        $csrfCookieName,
                        '',
                        [
                            'expires'  => time() - 3600,
                            'path'     => '/',
                            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]
                    );

                    set_flash(
                        'success',
                        'Welcome back, Administrator.'
                    );

                    header(
                        'Location: ' . BASE_URL . '/admin/dashboard.php'
                    );

                    exit;
                }

            } catch (Throwable $e) {

                $errors[] = 'Unable to process your login at this time.';
            }
        }
    }
}


$pageTitle = 'Administrator Login';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

    <div class="max-w-md w-full space-y-8">

        <!-- Header -->
        <div class="text-center">

            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-600 flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-lock text-white text-2xl"></i>
            </div>

            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Administrator Login
            </h2>

            <p class="text-xs text-slate-500 mt-1">
                Campus Safety & Emergency Alert System
            </p>

        </div>


        <!-- Error Box -->
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


        <!-- Login Card -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50">

            <form
                action="<?= BASE_URL ?>/admin/login.php"
                method="POST"
                class="space-y-5"
            >

                <!-- Admin CSRF Token -->
                <input
                    type="hidden"
                    name="admin_csrf_token"
                    value="<?= e($adminCsrfToken) ?>"
                >


                <!-- Email -->
                <div>

                    <label
                        for="email"
                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5"
                    >
                        Administrator Email
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
                            value="<?= e($email) ?>"
                            placeholder="Enter administrator email"
                            autocomplete="username"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition"
                        >

                    </div>

                </div>


                <!-- Password -->
                <div>

                    <label
                        for="password"
                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5"
                    >
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
                            placeholder="Enter administrator password"
                            autocomplete="current-password"
                            class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 focus:border-fpi-700 text-sm font-medium text-slate-800 transition"
                        >

                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            <i class="fa-regular fa-eye" id="pwd-eye"></i>
                        </button>

                    </div>

                </div>


                <!-- Submit -->
                <div>

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Sign in to Admin Portal
                    </button>

                </div>

            </form>


            <!-- Back to Normal Login -->
            <div class="mt-6 pt-6 border-t border-slate-100 text-center">

                <a
                    href="<?= BASE_URL ?>/login.php"
                    class="text-xs font-bold text-fpi-800 hover:text-fpi-900"
                >
                    ← Back to normal login
                </a>

            </div>


            <!-- Security Notice -->
            <div class="mt-5 text-center">

                <p class="text-[11px] text-slate-400">
                    Authorized administrators only.
                </p>

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


<?php require_once __DIR__ . '/../includes/footer.php'; ?>