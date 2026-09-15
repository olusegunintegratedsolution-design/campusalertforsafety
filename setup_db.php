<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Automated Database Installer & Seed Verification Script
 */

require_once __DIR__ . '/config/database.php';

echo "=== Federal Polytechnic Ilaro: Campus Safety Database Setup ===\n";

try {
    // 1. Connect without dbname to ensure database exists
    $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $pdo = new PDO($rootDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "[1/4] Checking and creating database `" . DB_NAME . "`...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 2. Connect with dbname
    $dbDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $dbPdo = new PDO($dbDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 3. Execute database.sql
    echo "[2/4] Importing schema and seed data from database/database.sql...\n";
    $sqlPath = __DIR__ . '/database/database.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("database.sql file not found at: $sqlPath");
    }

    $sql = file_get_contents($sqlPath);
    // Split queries by semicolon (basic) or execute multi-query
    $dbPdo->exec($sql);
    echo "      Schema and seed records imported successfully.\n";
    // 4. Verify tables and record counts

    // 4. Verify tables and record counts
    echo "[4/4] Verifying database tables and records...\n";
    $tables = ['users', 'campus_locations', 'emergency_reports', 'report_timeline', 'alerts', 'emergency_contacts', 'notifications', 'system_logs', 'system_settings', 'app_sessions'];
    foreach ($tables as $tbl) {
        $count = $dbPdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
        echo "      - Table `$tbl`: $count rows\n";
    }
    echo "
=== SUCCESS: Database initialization complete! ===
";
    echo "Administrator credentials are configured through Vercel Environment Variables.
";
} catch (Exception $e) {
    echo "\n[ERROR] Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
