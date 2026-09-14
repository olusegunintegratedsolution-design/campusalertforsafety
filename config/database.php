<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 *
 * Database Configuration
 * Supports:
 * - XAMPP / Localhost MySQL
 * - TiDB Cloud
 * - Vercel + FrankenPHP
 */

/**
 * ---------------------------------------------------------
 * ENVIRONMENT VARIABLE HELPER
 * ---------------------------------------------------------
 */
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


/**
 * ---------------------------------------------------------
 * DATABASE CONFIGURATION
 * ---------------------------------------------------------
 */
if (!defined('DB_HOST')) {
    define('DB_HOST', envValue('DB_HOST', 'localhost'));
    define('DB_USER', envValue('DB_USER', 'root'));
    define('DB_PASS', envValue('DB_PASS', ''));
    define('DB_NAME', envValue('DB_NAME', 'campus_safety_db'));
    define('DB_PORT', (int) envValue('DB_PORT', 3306));
    define('DB_CHARSET', 'utf8mb4');
}


/**
 * ---------------------------------------------------------
 * APPLICATION CONFIGURATION
 * ---------------------------------------------------------
 */
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Campus Safety & Emergency Alert System');
    define('INSTITUTION_NAME', 'Federal Polytechnic Ilaro');
    define('INSTITUTION_ACRONYM', 'FPI');

    define('EMERGENCY_HOTLINE', '+234 803 000 1199');
    define('CLINIC_HOTLINE', '+234 802 555 4321');
}


/**
 * ---------------------------------------------------------
 * VERCEL / HOST DETECTION
 * ---------------------------------------------------------
 */
$isVercel = envValue('VERCEL', '0') === '1';


/**
 * ---------------------------------------------------------
 * BASE PATH / BASE URL
 * ---------------------------------------------------------
 *
 * Local:
 * http://localhost/campus-safety
 *
 * Vercel:
 * https://campusalertforsafety.vercel.app
 */
if (!defined('BASE_PATH')) {

    $scriptName = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? ''
    );

    /*
     * XAMPP project:
     * http://localhost/campus-safety/
     */
    if (strpos($scriptName, '/campus-safety') !== false) {
        $basePath = '/campus-safety';
    } else {
        $basePath = '';
    }

    define('BASE_PATH', $basePath);


    /*
     * Vercel always uses HTTPS.
     *
     * This prevents the HTTP -> HTTPS redirect loop
     * that can happen behind Vercel's proxy.
     */
    if ($isVercel) {
        $protocol = 'https://';
    } else {
        $https = $_SERVER['HTTPS'] ?? '';

        $isHttps =
            (!empty($https) && strtolower($https) !== 'off')
            ||
            (isset($_SERVER['SERVER_PORT'])
                && (int) $_SERVER['SERVER_PORT'] === 443);

        $protocol = $isHttps
            ? 'https://'
            : 'http://';
    }


    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    define(
        'BASE_URL',
        $protocol . $host . $basePath
    );
}


/**
 * ---------------------------------------------------------
 * SESSION CONFIGURATION
 * ---------------------------------------------------------
 */
if (session_status() === PHP_SESSION_NONE) {

    /*
     * Vercel containers are temporary.
     * /tmp is writable and suitable for PHP sessions
     * during the lifetime of the instance.
     */
    if ($isVercel) {
        $tmpSessionPath = sys_get_temp_dir();

        if (is_dir($tmpSessionPath) && is_writable($tmpSessionPath)) {
            session_save_path($tmpSessionPath);
        }
    }

    /*
     * Secure session cookie on Vercel/HTTPS.
     */
    if ($isVercel) {
        ini_set(
            'session.cookie_secure',
            '1'
        );
    }

    ini_set(
        'session.cookie_httponly',
        '1'
    );

    ini_set(
        'session.cookie_samesite',
        'Lax'
    );

    session_start();
}


/**
 * ---------------------------------------------------------
 * DATABASE CONNECTION
 * ---------------------------------------------------------
 *
 * @return PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    /*
     * Reuse existing connection.
     */
    if ($pdo !== null) {
        return $pdo;
    }


    /**
     * -----------------------------------------------------
     * PDO DSN
     * -----------------------------------------------------
     */
    $dsn =
        'mysql:host=' . DB_HOST .
        ';port=' . DB_PORT .
        ';dbname=' . DB_NAME .
        ';charset=' . DB_CHARSET;


    /**
     * -----------------------------------------------------
     * PDO OPTIONS
     * -----------------------------------------------------
     */
    $options = [

        PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE =>
            PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES =>
            false,

        PDO::MYSQL_ATTR_INIT_COMMAND =>
            'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ];


    /**
     * -----------------------------------------------------
     * SSL / TLS
     * -----------------------------------------------------
     *
     * TiDB Cloud requires secure TLS connections.
     *
     * On our Alpine/FrankenPHP Vercel container,
     * the system CA bundle is:
     *
     * /etc/ssl/cert.pem
     */
    $dbSSL = strtolower(
        (string) envValue('DB_SSL', 'false')
    ) === 'true';


    /*
     * Automatically enable SSL on Vercel.
     */
    if ($isVercel || $dbSSL) {

        /*
         * Allow a custom CA path if supplied.
         *
         * Otherwise use Alpine's system CA bundle.
         */
        $sslCA = envValue(
            'DB_SSL_CA',
            '/etc/ssl/cert.pem'
        );


        /*
         * Only configure SSL CA if the file exists.
         */
        if (is_file($sslCA)) {

            $options[
                PDO::MYSQL_ATTR_SSL_CA
            ] = $sslCA;

            /*
             * IMPORTANT:
             * Verify the TiDB server certificate.
             */
            $options[
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
            ] = true;
        }
    }


    /**
     * -----------------------------------------------------
     * CONNECT
     * -----------------------------------------------------
     */
    try {

        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $options
        );

        return $pdo;

    } catch (PDOException $e) {


        /**
         * -------------------------------------------------
         * LOCAL XAMPP FALLBACK
         * -------------------------------------------------
         *
         * If localhost database does not exist,
         * create it automatically.
         */
        if (
            DB_HOST === 'localhost'
            ||
            DB_HOST === '127.0.0.1'
        ) {

            try {

                /*
                 * Connect without selecting database.
                 */
                $rootDsn =
                    'mysql:host=' . DB_HOST .
                    ';port=' . DB_PORT .
                    ';charset=' . DB_CHARSET;


                $rootOptions = [
                    PDO::ATTR_ERRMODE =>
                        PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE =>
                        PDO::FETCH_ASSOC,

                    PDO::ATTR_EMULATE_PREPARES =>
                        false
                ];


                $rootPdo = new PDO(
                    $rootDsn,
                    DB_USER,
                    DB_PASS,
                    $rootOptions
                );


                /*
                 * Create database.
                 */
                $safeDatabaseName =
                    str_replace(
                        '`',
                        '``',
                        DB_NAME
                    );


                $rootPdo->exec(
                    'CREATE DATABASE IF NOT EXISTS `' .
                    $safeDatabaseName .
                    '` DEFAULT CHARACTER SET utf8mb4 ' .
                    'COLLATE utf8mb4_unicode_ci'
                );


                /*
                 * Connect again to the new database.
                 */
                $pdo = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    $options
                );


                /**
                 * -----------------------------------------
                 * IMPORT LOCAL DATABASE SQL
                 * -----------------------------------------
                 */
                $sqlPath =
                    dirname(__DIR__) .
                    '/database/database.sql';


                if (file_exists($sqlPath)) {

                    $sql = file_get_contents($sqlPath);

                    if ($sql !== false && trim($sql) !== '') {

                        /*
                         * Remove CREATE DATABASE / USE statements
                         * because the database is already selected.
                         */
                        $sql = preg_replace(
                            '/CREATE\s+DATABASE.*?;/is',
                            '',
                            $sql
                        );

                        $sql = preg_replace(
                            '/USE\s+[`a-zA-Z0-9_-]+.*?;/is',
                            '',
                            $sql
                        );


                        /*
                         * Execute SQL.
                         */
                        $pdo->exec($sql);
                    }
                }


                return $pdo;


            } catch (Exception $inner) {

                die(
                    '<div style="
                        font-family:Arial,sans-serif;
                        padding:30px;
                        max-width:650px;
                        margin:50px auto;
                        border:1px solid #f87171;
                        background:#fef2f2;
                        border-radius:12px;
                        color:#991b1b;
                    ">
                        <h2 style="margin-top:0;">
                            Local Database Error
                        </h2>

                        <p>
                            Could not connect to MySQL on
                            <strong>' .
                            htmlspecialchars(DB_HOST) .
                            '</strong>.
                        </p>

                        <p>
                            Please make sure XAMPP MySQL is running.
                        </p>

                        <p>
                            <small>' .
                            htmlspecialchars(
                                $inner->getMessage()
                            ) .
                            '</small>
                        </p>
                    </div>'
                );
            }
        }


        /**
         * -------------------------------------------------
         * CLOUD DATABASE ERROR
         * -------------------------------------------------
         */
        die(
            '<div style="
                font-family:Arial,sans-serif;
                padding:30px;
                max-width:700px;
                margin:50px auto;
                border:1px solid #f87171;
                background:#fef2f2;
                border-radius:12px;
                color:#991b1b;
            ">

                <h2 style="margin-top:0;">
                    Cloud Database Connection Error
                </h2>

                <p>
                    Could not connect to the remote MySQL
                    database.
                </p>

                <p>
                    <strong>Host:</strong>
                    ' .
                    htmlspecialchars(DB_HOST) .
                    '
                </p>

                <p>
                    <strong>Database:</strong>
                    ' .
                    htmlspecialchars(DB_NAME) .
                    '
                </p>

                <p>
                    <strong>Port:</strong>
                    ' .
                    htmlspecialchars((string) DB_PORT) .
                    '
                </p>

                <p>
                    <small>' .
                    htmlspecialchars(
                        $e->getMessage()
                    ) .
                    '</small>
                </p>

                <p>
                    Check your Vercel Environment Variables:
                </p>

                <ul>
                    <li>DB_HOST</li>
                    <li>DB_USER</li>
                    <li>DB_PASS</li>
                    <li>DB_NAME</li>
                    <li>DB_PORT</li>
                    <li>DB_SSL</li>
                </ul>

            </div>'
        );
    }
}