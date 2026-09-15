<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in() && is_admin()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security request. Please refresh the page and try again.';
    } else {

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '') {
            $errors[] = 'Please enter your administrator email.';
        }

        if ($password === '') {
            $errors[] = 'Please enter your password.';
        }

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

                    // Create the normal authenticated session
                    login_user($user);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?> | Campus Safety</title>

    <link rel="stylesheet"
          href="<?= BASE_URL ?>/assets/css/style.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f9f8;
            margin: 0;
        }

        .admin-login-wrapper {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .admin-login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .admin-login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .admin-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            border-radius: 50%;
            background: #1b8969;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .admin-login-header h1 {
            margin: 0 0 8px;
            font-size: 25px;
        }

        .admin-login-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #1b8969;
        }

        .admin-login-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #1b8969;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .admin-login-button:hover {
            background: #176f56;
        }

        .error-box {
            background: #fff0f0;
            color: #b42318;
            border: 1px solid #f5c2c2;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .back-link a {
            color: #1b8969;
            text-decoration: none;
            font-weight: 600;
        }

        .security-note {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>

<div class="admin-login-wrapper">

    <div class="admin-login-card">

        <div class="admin-login-header">

            <div class="admin-icon">
                🔐
            </div>

            <h1>Administrator Login</h1>

            <p>
                Campus Safety & Emergency Alert System
            </p>

        </div>

        <?php if (!empty($errors)): ?>

            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <div>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <?= csrf_field() ?>

            <div class="form-group">

                <label for="email">
                    Administrator Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="Enter administrator email"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter administrator password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button
                type="submit"
                class="admin-login-button"
            >
                Sign in to Admin Portal
            </button>

        </form>

        <div class="back-link">
            <a href="<?= BASE_URL ?>/login.php">
                ← Back to normal login
            </a>
        </div>

        <div class="security-note">
            Authorized administrators only.
        </div>

    </div>

</div>

</body>
</html>