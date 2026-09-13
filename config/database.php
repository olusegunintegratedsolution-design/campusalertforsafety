<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Database Configuration & Connection Handler
 * 
 * Supports both Localhost (XAMPP) and Cloud / Vercel Serverless Hosting
 */

// Read from Environment Variables (Vercel / Cloud) with Localhost Fallbacks
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost'));
    define('DB_USER', getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root'));
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : ''));
    define('DB_NAME', getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'campus_safety_db'));
    define('DB_PORT', (int)(getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : 3306)));
    define('DB_CHARSET', 'utf8mb4');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Campus Safety & Emergency Alert System');
    define('INSTITUTION_NAME', 'Federal Polytechnic Ilaro');
    define('INSTITUTION_ACRONYM', 'FPI');
    define('EMERGENCY_HOTLINE', '+234 803 000 1199');
    define('CLINIC_HOTLINE', '+234 802 555 4321');
}

// Compute BASE_PATH & BASE_URL dynamically
if (!defined('BASE_PATH')) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    // Local XAMPP uses /campus-safety
    if (strpos($scriptName, '/campus-safety') !== false) {
        $basePath = '/campus-safety';
    } else {
        $basePath = '';
    }

    define('BASE_PATH', $basePath);

    // Detect HTTPS correctly behind Vercel/proxies
    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
        $forwardedProto === 'https' ||
        getenv('VERCEL') === '1'
    ) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    define('BASE_URL', $protocol . $host . $basePath);
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    // For serverless runtimes (Vercel), store sessions in writable /tmp directory if default path isn't writable
    $savePath = session_save_path();
    if (empty($savePath) || !is_writable($savePath)) {
        $tmpDir = sys_get_temp_dir();
        if (is_writable($tmpDir)) {
            session_save_path($tmpDir);
        }
    }
    session_start();
}

/**
 * Returns a singleton PDO instance for database interactions
 * @return PDO
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        // Cloud MySQL providers (Aiven, TiDB, PlanetScale) often require or support SSL
        if (getenv('DB_SSL') === 'true' || getenv('MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If local and database doesn't exist, try auto-creating
            if (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') {
                try {
                    $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
                    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    
                    $sqlPath = dirname(__DIR__) . '/database/database.sql';
                    if (file_exists($sqlPath)) {
                        $sql = file_get_contents($sqlPath);
                        $pdo->exec($sql);
                    }
                } catch (Exception $inner) {
                    die('<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #f87171;background:#fef2f2;border-radius:12px;color:#991b1b;">'
                        . '<h2 style="margin-top:0;">Database Connection Error</h2>'
                        . '<p>Could not connect to MySQL server at <strong>' . htmlspecialchars(DB_HOST) . '</strong>.</p>'
                        . '<p><small>' . htmlspecialchars($inner->getMessage()) . '</small></p>'
                        . '<p>Please ensure MySQL is running or configure environment variables.</p>'
                        . '</div>');
                }
            } else {
                die('<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #f87171;background:#fef2f2;border-radius:12px;color:#991b1b;">'
                    . '<h2 style="margin-top:0;">Cloud Database Connection Error</h2>'
                    . '<p>Could not connect to Remote MySQL host <strong>' . htmlspecialchars(DB_HOST) . '</strong>.</p>'
                    . '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>'
                    . '<p>Check your Vercel Environment Variables: <code>DB_HOST</code>, <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_NAME</code>, <code>DB_PORT</code>.</p>'
                    . '</div>');
            }
        }
    }

    return $pdo;
}
