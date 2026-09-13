<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 *
 * Database Configuration & Connection Handler
 * Supports:
 * - XAMPP / Localhost
 * - Vercel + FrankenPHP
 * - Remote MySQL databases
 */

// ============================================================
// ENVIRONMENT HELPER
// ============================================================

function envValue(string $key, $default = null)
{
    $value = getenv($key);

    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    return $default;
}


// ============================================================
// DATABASE CONFIGURATION
// ============================================================

if (!defined('DB_HOST')) {
    define('DB_HOST', envValue('DB_HOST', 'localhost'));
    define('DB_USER', envValue('DB_USER', 'root'));
    define('DB_PASS', envValue('DB_PASS', ''));
    define('DB_NAME', envValue('DB_NAME', 'campus_safety_db'));
    define('DB_PORT', (int) envValue('DB_PORT', 3306));
    define('DB_CHARSET', 'utf8mb4');
}


// ============================================================
// APPLICATION INFORMATION
// ============================================================

if (!defined('APP_NAME')) {
    define(
        'APP_NAME',
        'Campus Safety & Emergency Alert System'
    );

    define(
        'INSTITUTION_NAME',
        'Federal Polytechnic Ilaro'
    );

    define(
        'INSTITUTION_ACRONYM',
        'FPI'
    );

    define(
        'EMERGENCY_HOTLINE',
        '+234 803 000 1199'
    );

    define(
        'CLINIC_HOTLINE',
        '+234 802 555 4321'
    );
}


// ============================================================
// BASE PATH
// ============================================================
//
// Local XAMPP:
// http://localhost/campus-safety
//
// Vercel:
// https://campusalertforsafety.vercel.app
//
// Therefore:
// Local  = /campus-safety
// Vercel = empty
//

if (!defined('BASE_PATH')) {

    $scriptName = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? ''
    );

    if (strpos($scriptName, '/campus-safety') !== false) {
        $basePath = '/campus-safety';
    } else {
        $basePath = '';
    }

    define('BASE_PATH', $basePath);
}


// ============================================================
// BASE URL
// ============================================================
//
// IMPORTANT:
// Vercel runs behind a proxy. In some situations PHP may see
// HTTP internally even though the visitor is using HTTPS.
//
// If PHP generates:
// http://campusalertforsafety.vercel.app
//
// while Vercel forces:
// https://campusalertforsafety.vercel.app
//
// the browser can enter an infinite redirect loop.
//
// This section forces HTTPS whenever running on Vercel.
//

if (!defined('BASE_URL')) {

    $isVercel = (
        getenv('VERCEL') === '1' ||
        isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1' ||
        isset($_SERVER['VERCEL']) && $_SERVER['VERCEL'] === '1'
    );

    if ($isVercel) {

        // Vercel must always use HTTPS
        $protocol = 'https://';

    } else {

        // Local XAMPP detection
        $httpsEnabled = (
            (!empty($_SERVER['HTTPS']) &&
             strtolower($_SERVER['HTTPS']) !== 'off')
            ||
            (isset($_SERVER['SERVER_PORT']) &&
             (int) $_SERVER['SERVER_PORT'] === 443)
            ||
            (
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
            )
        );

        $protocol = $httpsEnabled
            ? 'https://'
            : 'http://';
    }

    // Get current hostname
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Remove accidental whitespace
    $host = trim($host);

    define(
        'BASE_URL',
        $protocol . $host . BASE_PATH
    );
}


// ============================================================
// SESSION CONFIGURATION
// ============================================================
//
// Sessions are required for:
// - Login
// - Logout
// - Authentication
// - Flash messages
//
// /tmp is writable on Vercel/FrankenPHP.
//

if (session_status() === PHP_SESSION_NONE) {

    $currentSessionPath = session_save_path();

    if (
        empty($currentSessionPath) ||
        !is_writable($currentSessionPath)
    ) {

        $temporaryDirectory = sys_get_temp_dir();

        if (
            !empty($temporaryDirectory) &&
            is_dir($temporaryDirectory) &&
            is_writable($temporaryDirectory)
        ) {
            session_save_path($temporaryDirectory);
        }
    }

    session_start();
}


// ============================================================
// DATABASE CONNECTION
// ============================================================

/**
 * Get a PDO database connection.
 *
 * Uses a singleton connection so that the application
 * does not repeatedly create database connections.
 *
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    // Return existing connection
    if ($pdo instanceof PDO) {
        return $pdo;
    }


    // ========================================================
    // PDO DSN
    // ========================================================

    $dsn =
        'mysql:' .
        'host=' . DB_HOST . ';' .
        'port=' . DB_PORT . ';' .
        'dbname=' . DB_NAME . ';' .
        'charset=' . DB_CHARSET;


    // ========================================================
    // PDO OPTIONS
    // ========================================================

    $options = [

        // Throw exceptions when database errors occur
        PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

        // Return database rows as associative arrays
        PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

        // Use native MySQL prepared statements
        PDO::ATTR_EMULATE_PREPARES =>
            false,

        // Set UTF-8
        PDO::MYSQL_ATTR_INIT_COMMAND =>
            'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ];


    // ========================================================
    // MYSQL SSL
    // ========================================================
    //
    // Some cloud MySQL providers require SSL.
    //
    // DB_SSL=true can be added to Vercel Environment Variables.
    //

    $dbSSL = strtolower(
        (string) envValue('DB_SSL', 'false')
    );

    if ($dbSSL === 'true') {

        // Allow connection to cloud MySQL servers that
        // require SSL but do not provide a local CA file.
        $options[
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
        ] = false;
    }


    // ========================================================
    // CONNECT TO DATABASE
    // ========================================================

    try {

        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $options
        );

        return $pdo;

    } catch (PDOException $e) {

        // ====================================================
        // LOCAL XAMPP DATABASE
        // ====================================================

        if (
            DB_HOST === 'localhost' ||
            DB_HOST === '127.0.0.1'
        ) {

            try {

                // Connect to MySQL without selecting a database
                $rootDsn =
                    'mysql:' .
                    'host=' . DB_HOST . ';' .
                    'port=' . DB_PORT . ';' .
                    'charset=' . DB_CHARSET;

                $rootPdo = new PDO(
                    $rootDsn,
                    DB_USER,
                    DB_PASS,
                    $options
                );


                // Create database if it does not exist
                $databaseName = str_replace(
                    '`',
                    '``',
                    DB_NAME
                );

                $rootPdo->exec(
                    "CREATE DATABASE IF NOT EXISTS `{$databaseName}` " .
                    "DEFAULT CHARACTER SET utf8mb4 " .
                    "COLLATE utf8mb4_unicode_ci"
                );


                // Connect again using the new database
                $pdo = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    $options
                );


                // =================================================
                // OPTIONAL LOCAL DATABASE IMPORT
                // =================================================
                //
                // This is mainly useful for XAMPP development.
                // It should not be relied upon for Vercel.
                //

                $sqlPath =
                    dirname(__DIR__) .
                    '/database/database.sql';

                if (file_exists($sqlPath)) {

                    $sql = file_get_contents($sqlPath);

                    if ($sql !== false && trim($sql) !== '') {

                        try {
                            $pdo->exec($sql);
                        } catch (PDOException $sqlError) {
                            // Ignore import errors here because
                            // the database may already be populated.
                        }
                    }
                }


                return $pdo;

            } catch (Throwable $localError) {

                // =================================================
                // LOCAL DATABASE ERROR
                // =================================================

                $safeMessage = htmlspecialchars(
                    $localError->getMessage(),
                    ENT_QUOTES,
                    'UTF-8'
                );

                die(
                    '<div style="
                        font-family: Arial, sans-serif;
                        max-width: 650px;
                        margin: 60px auto;
                        padding: 30px;
                        border: 1px solid #fca5a5;
                        background: #fef2f2;
                        border-radius: 12px;
                        color: #991b1b;
                    ">
                        <h2 style="margin-top:0;">
                            Database Connection Error
                        </h2>

                        <p>
                            Could not connect to the local MySQL
                            database.
                        </p>

                        <p>
                            Make sure <strong>MySQL</strong> is
                            running in XAMPP.
                        </p>

                        <p>
                            <strong>Database:</strong>
                            ' . htmlspecialchars(
                                DB_NAME,
                                ENT_QUOTES,
                                'UTF-8'
                            ) . '
                        </p>

                        <small>
                            ' . $safeMessage . '
                        </small>
                    </div>'
                );
            }
        }


        // ========================================================
        // CLOUD DATABASE ERROR
        // ========================================================
        //
        // Do NOT expose DB password or sensitive connection
        // information to visitors.
        //

        $safeHost = htmlspecialchars(
            DB_HOST,
            ENT_QUOTES,
            'UTF-8'
        );

        $safeMessage = htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        );

        die(
            '<div style="
                font-family: Arial, sans-serif;
                max-width: 650px;
                margin: 60px auto;
                padding: 30px;
                border: 1px solid #fca5a5;
                background: #fef2f2;
                border-radius: 12px;
                color: #991b1b;
            ">

                <h2 style="margin-top:0;">
                    Cloud Database Connection Error
                </h2>

                <p>
                    The application could not connect to
                    the remote MySQL database.
                </p>

                <p>
                    <strong>Database Host:</strong>
                    ' . $safeHost . '
                </p>

                <p>
                    Please check the following Vercel
                    Environment Variables:
                </p>

                <ul>
                    <li>DB_HOST</li>
                    <li>DB_USER</li>
                    <li>DB_PASS</li>
                    <li>DB_NAME</li>
                    <li>DB_PORT</li>
                    <li>DB_SSL (if required by your provider)</li>
                </ul>

                <p>
                    <small>
                        Connection message:
                        ' . $safeMessage . '
                    </small>
                </p>

            </div>'
        );
    }
}