<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System
 *
 * Administrator Password Recovery
 *
 * IMPORTANT:
 * Set ADMIN_RESET_KEY as a Vercel Environment Variable
 * before using this page.
 *
 * Example:
 * ADMIN_RESET_KEY = generate-a-long-random-secret-here
 */

require_once __DIR__ . '/../includes/auth.php';

$errors = [];
$success = '';
$generatedPassword = '';

$resetKey = trim((string) envValue('ADMIN_RESET_KEY', ''));

if ($resetKey === '') {
    $errors[] = 'Administrator password recovery is not currently enabled.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {

    $submittedKey = trim((string)($_POST['reset_key'] ?? ''));

    if ($submittedKey === '' || !hash_equals($resetKey, $submittedKey)) {

        $errors[] = 'Invalid password recovery key.';

    } elseif (!validate_csrf()) {

        $errors[] = 'Security token invalid or expired. Please refresh the page.';

    } else {

        try {

            $pdo = getDB();

            /*
             * Locate the designated administrator.
             */

            $stmt = $pdo->prepare("
                SELECT id, email, role, status
                FROM users
                WHERE LOWER(TRIM(email)) = ?
                LIMIT 1
            ");

            $stmt->execute([
                'admin@ilaropoly.edu.ng'
            ]);

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {

                $errors[] =
                    'Administrator account was not found in the database.';

            } elseif ($admin['role'] !== 'admin') {

                $errors[] =
                    'The administrator account does not have the admin role.';

            } elseif ($admin['status'] !== 'active') {

                $errors[] =
                    'The administrator account is not active.';

            } else {

                /*
                 * Generate a strong temporary password.
                 *
                 * random_bytes() provides cryptographically secure
                 * random data.
                 */

                $generatedPassword =
                    'FPI-' .
                    strtoupper(bin2hex(random_bytes(4))) .
                    '-' .
                    strtoupper(bin2hex(random_bytes(4)));

                /*
                 * Hash the generated password.
                 */

                $passwordHash =
                    password_hash(
                        $generatedPassword,
                        PASSWORD_DEFAULT
                    );

                if ($passwordHash === false) {

                    $errors[] =
                        'Unable to generate the administrator password.';

                } else {

                    /*
                     * Replace the administrator password.
                     */

                    $update = $pdo->prepare("
                        UPDATE users
                        SET password = ?
                        WHERE id = ?
                          AND role = 'admin'
                    ");

                    $update->execute([
                        $passwordHash,
                        $admin['id']
                    ]);

                    if ($update->rowCount() < 1) {

                        $errors[] =
                            'The administrator password could not be updated.';

                        $generatedPassword = '';

                    } else {

                        $success =
                            'Administrator password successfully reset.';
                    }
                }
            }

        } catch (Throwable $e) {

            /*
             * Do not expose database details publicly.
             */

            $errors[] =
                'Unable to reset the administrator password at this time.';
        }
    }
}

$pageTitle = 'Administrator Password Recovery';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4">

    <div class="max-w-lg w-full">

        <div class="text-center mb-8">

            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-600 flex items-center justify-center shadow-lg">

                <i class="fa-solid fa-key text-white text-2xl"></i>

            </div>

            <h1 class="text-3xl font-black text-slate-900">
                Administrator Password Recovery
            </h1>

            <p class="text-sm text-slate-500 mt-2">
                Secure administrator account recovery
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


        <?php if ($success && $generatedPassword !== ''): ?>

            <div class="mb-6 p-6 rounded-2xl bg-emerald-50 border border-emerald-200">

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center">

                        <i class="fa-solid fa-check text-white"></i>

                    </div>

                    <div>

                        <h2 class="font-black text-emerald-900">
                            Password Reset Successful
                        </h2>

                        <p class="text-sm text-emerald-700">
                            Save this password before leaving this page.
                        </p>

                    </div>

                </div>


                <label class="block text-sm font-bold text-slate-700 mb-2">
                    New Administrator Password
                </label>

                <div class="flex gap-2">

                    <input
                        type="text"
                        id="generatedPassword"
                        value="<?= e($generatedPassword) ?>"
                        readonly
                        class="flex-1 px-4 py-3 rounded-xl border border-emerald-300 bg-white font-mono font-bold text-slate-900"
                    >

                    <button
                        type="button"
                        onclick="copyPassword()"
                        class="px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white"
                        title="Copy password"
                    >
                        <i class="fa-solid fa-copy"></i>
                    </button>

                </div>

                <p class="text-xs text-emerald-700 mt-3">
                    This password is shown only on this page. Store it securely.
                </p>

            </div>

        <?php endif; ?>


        <?php if (!$success): ?>

            <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8">

                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200">

                    <div class="flex gap-3">

                        <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-1"></i>

                        <div class="text-sm text-amber-800">

                            <p class="font-bold mb-1">
                                Administrator recovery only
                            </p>

                            <p>
                                This tool changes the administrator password.
                                Do not share the recovery key.
                            </p>

                        </div>

                    </div>

                </div>


                <form
                    method="POST"
                    action="<?= e(BASE_URL) ?>/admin/reset-password.php"
                    class="space-y-6"
                >

                    <?= csrf_field() ?>


                    <div>

                        <label
                            for="reset_key"
                            class="block text-sm font-bold text-slate-700 mb-2"
                        >
                            Recovery Key
                        </label>

                        <input
                            type="password"
                            id="reset_key"
                            name="reset_key"
                            autocomplete="off"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-amber-600 focus:ring-2 focus:ring-amber-100 outline-none"
                            placeholder="Enter administrator recovery key"
                        >

                    </div>


                    <button
                        type="submit"
                        class="w-full py-3.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition"
                    >

                        <i class="fa-solid fa-key mr-2"></i>

                        Generate New Administrator Password

                    </button>

                </form>


                <div class="border-t border-slate-100 mt-6 pt-6 text-center">

                    <a
                        href="<?= e(BASE_URL) ?>/admin/login.php"
                        class="text-sm font-semibold text-slate-600 hover:text-emerald-700"
                    >
                        ← Back to Administrator Login
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>

function copyPassword() {

    const input =
        document.getElementById('generatedPassword');

    if (!input) {
        return;
    }

    navigator.clipboard.writeText(input.value)
        .then(function () {

            alert('Administrator password copied.');

        })
        .catch(function () {

            input.select();
            document.execCommand('copy');

            alert('Administrator password copied.');

        });
}

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>