<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Authentication and Role-Based Access Control
 */

require_once __DIR__ . '/functions.php';

/**
 * Check if a user is logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get the currently logged-in user array
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check user roles
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function is_staff(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'staff';
}

function is_student(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'student';
}

/**
 * Require user to be authenticated
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access this page.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Require user to have the 'admin' role
 */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Access denied. Administrator privileges are required.');
        header('Location: ' . BASE_URL . '/student/dashboard.php');
        exit;
    }
}

/**
 * Redirect logged-in users away from guest pages (login/register)
 */
function redirect_if_logged_in(): void {
    if (is_logged_in()) {
        if (is_admin()) {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/student/dashboard.php');
        }
        exit;
    }
}

/**
 * Log in a user securely
 */
function login_user(array $user): void {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'         => $user['id'],
        'name'       => $user['name'],
        'email'      => $user['email'],
        'phone'      => $user['phone'],
        'role'       => $user['role'],
        'id_number'  => $user['id_number'],
        'department' => $user['department'],
        'avatar'     => $user['avatar'] ?? null
    ];

    log_activity('USER_LOGIN', 'Logged into ' . $user['role'] . ' portal.', $user['id']);
}

/**
 * Log out user safely
 */
function logout_user(): void {
    if (is_logged_in()) {
        log_activity('USER_LOGOUT', 'Logged out of system.', $_SESSION['user']['id']);
    }
    
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
