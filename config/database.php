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


/* =========================================================
   ENVIRONMENT VARIABLE HELPER
   ========================================================= */

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


/* =========================================================
   DATABASE CONFIGURATION
   ========================================================= */

if (!defined('DB_HOST')) {
    define('DB_HOST', envValue('DB_HOST', 'localhost'));
    define('DB_USER', envValue('DB_USER', 'root'));
    define('DB_PASS', envValue('DB_PASS', ''));
    define('DB_NAME', envValue('DB_NAME', 'campus_safety_db'));
    define('DB_PORT', (int) envValue('DB_PORT', 3306));
    define('DB_CHARSET', 'utf8mb4');
}


/* =========================================================
   APPLICATION CONFIGURATION
   ========================================================= */

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


/* =========================================================
   VERCEL DETECTION
   ========================================================= */

$isVercel = envValue('VERCEL', '0') === '1';


/* =========================================================
   BASE PATH AND BASE URL
   ========================================================= */

if (!defined('BASE_PATH')) {

    $scriptName = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? ''
    );

    /*
     * Local XAMPP:
     * http://localhost/campus-safety
     *
     * Vercel:
     * https://your-project.vercel.app
     */

    if (strpos($scriptName, '/campus-safety') !== false) {
        $basePath = '/campus-safety';
    } else {
        $basePath = '';
    }

    define('BASE_PATH', $basePath);


    /*
     * Vercel is always HTTPS.
     *
     * This prevents the redirect loop that can happen
     * when Vercel's HTTPS proxy is detected as HTTP.
     */

    if ($isVercel) {

        $protocol = 'https://';

    } else {

        $https = $_SERVER['HTTPS'] ?? '';

        $isHttps =
            (!empty($https) && strtolower($https) !== 'off')
            ||
            (
                isset($_SERVER['SERVER_PORT'])
                &&
                (int) $_SERVER['SERVER_PORT'] === 443
            );

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


/* =========================================================
   SESSION CONFIGURATION
   ========================================================= */

/*
 * Vercel can route different HTTP requests to different container
 * instances. File-based PHP sessions stored in /tmp are therefore
 * not reliable for authentication or CSRF tokens.
 *
 * Store PHP sessions in TiDB instead so the same session is available
 * on every request/container. The table is created automatically and
 * does not affect the existing application tables.
 */
class CESDatabaseSessionHandler implements SessionHandlerInterface
{
    private ?PDO $pdo = null;
    private bool $tableReady = false;

    private function db(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = getDB();
        }

        if (!$this->tableReady) {
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS `app_sessions` (
                    `session_id` VARCHAR(128) NOT NULL PRIMARY KEY,
                    `session_data` MEDIUMBLOB NOT NULL,
                    `last_activity` INT UNSIGNED NOT NULL,
                    INDEX `idx_app_sessions_activity` (`last_activity`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->tableReady = true;
        }

        return $this->pdo;
    }

    public function open(string $path, string $name): bool
    {
        try {
            $this->db();
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $stmt = $this->db()->prepare(
                'SELECT session_data FROM app_sessions WHERE session_id = ? LIMIT 1'
            );
            $stmt->execute([$id]);
            $data = $stmt->fetchColumn();
            return $data === false ? '' : (string)$data;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'INSERT INTO app_sessions (session_id, session_data, last_activity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE session_data = VALUES(session_data), last_activity = VALUES(last_activity)'
            );
            return $stmt->execute([$id, $data, time()]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $stmt = $this->db()->prepare(
                'DELETE FROM app_sessions WHERE session_id = ?'
            );
            return $stmt->execute([$id]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->db()->prepare(
                'DELETE FROM app_sessions WHERE last_activity < ?'
            );
            $stmt->execute([time() - $max_lifetime]);
            return $stmt->rowCount();
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    if ($isVercel) {
        ini_set('session.cookie_secure', '1');
    }

    $sessionHandler = new CESDatabaseSessionHandler();
    session_set_save_handler($sessionHandler, true);
    session_start();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

function getDB(): PDO
{
    static $pdo = null;

    /*
     * Reuse existing connection.
     */

    if ($pdo !== null) {
        return $pdo;
    }


    /*
     * IMPORTANT:
     *
     * This variable is defined INSIDE the function.
     * Therefore there will be no:
     *
     * Undefined variable $isVercel
     *
     * warning.
     */

    $isVercel = envValue(
        'VERCEL',
        '0'
    ) === '1';


    /* =====================================================
       PDO CONNECTION STRING
       ===================================================== */

    $dsn =
        'mysql:host=' . DB_HOST .
        ';port=' . DB_PORT .
        ';dbname=' . DB_NAME .
        ';charset=' . DB_CHARSET;


    /* =====================================================
       PDO OPTIONS
       ===================================================== */

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


    /* =====================================================
       TIDB CLOUD SSL / TLS
       ===================================================== */

    $dbSSL = strtolower(
        (string) envValue(
            'DB_SSL',
            'false'
        )
    ) === 'true';


    /*
     * TiDB Cloud requires a secure TLS connection.
     *
     * Vercel + TiDB:
     * TLS is automatically enabled.
     *
     * Localhost:
     * TLS remains disabled unless DB_SSL=true.
     */

    if ($isVercel || $dbSSL) {

        /*
         * Alpine Linux system CA certificate.
         */

        $sslCA = envValue(
            'DB_SSL_CA',
            '/etc/ssl/cert.pem'
        );


        /*
         * Only use the CA file when it exists.
         */

        if (is_file($sslCA)) {

            $options[
                PDO::MYSQL_ATTR_SSL_CA
            ] = $sslCA;

            /*
             * Verify TiDB Cloud's certificate.
             */

            $options[
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT
            ] = true;
        }
    }


    /* =====================================================
       CONNECT TO DATABASE
       ===================================================== */

    try {

        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $options
        );

        return $pdo;

    } catch (PDOException $e) {


        /* =================================================
           LOCAL XAMPP DATABASE
           ================================================= */

        if (
            DB_HOST === 'localhost'
            ||
            DB_HOST === '127.0.0.1'
        ) {

            try {

                /*
                 * Connect to MySQL without selecting
                 * a database first.
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
                 * Safely create the local database.
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
                 * Connect again using the database.
                 */

                $pdo = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    $options
                );


                /*
                 * Try to import the local SQL file
                 * if it exists.
                 */

                $sqlPath =
                    dirname(__DIR__) .
                    '/database/database.sql';


                if (file_exists($sqlPath)) {

                    $sql = file_get_contents($sqlPath);

                    if (
                        $sql !== false
                        &&
                        trim($sql) !== ''
                    ) {

                        /*
                         * Remove CREATE DATABASE statements.
                         */

                        $sql = preg_replace(
                            '/CREATE\s+DATABASE.*?;/is',
                            '',
                            $sql
                        );


                        /*
                         * Remove USE statements.
                         */

                        $sql = preg_replace(
                            '/USE\s+[`a-zA-Z0-9_-]+.*?;/is',
                            '',
                            $sql
                        );


                        /*
                         * Execute the SQL.
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
                            Make sure MySQL is running
                            in XAMPP.
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


        /* =================================================
           CLOUD DATABASE ERROR
           ================================================= */

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
                    Could not connect to the remote
                    MySQL database.
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
                    Check these Vercel Environment Variables:
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