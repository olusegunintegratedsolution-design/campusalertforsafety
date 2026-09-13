<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Automated Verification & Diagnostic Script
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

echo "=== Federal Polytechnic Ilaro: System Verification Test ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest($name, $condition) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        $testsPassed++;
        echo " [PASS] $name\n";
    } else {
        echo " [FAIL] $name\n";
    }
}

try {
    $pdo = getDB();
    assertTest("Database Connection Established", $pdo instanceof PDO);

    // 1. Check Tables
    $tables = ['users', 'campus_locations', 'emergency_reports', 'report_timeline', 'alerts', 'emergency_contacts', 'notifications', 'system_logs', 'system_settings'];
    foreach ($tables as $t) {
        $check = $pdo->query("SHOW TABLES LIKE '$t'")->fetch();
        assertTest("Table exists: $t", !empty($check));
    }

    // 2. Verify Demo Users & Passwords
    $admin = $pdo->query("SELECT * FROM users WHERE email = 'admin@ilaropoly.edu.ng'")->fetch();
    assertTest("Admin user exists", !empty($admin) && $admin['role'] === 'admin');
    assertTest("Admin password verifies ('Admin@12345')", !empty($admin) && password_verify('Admin@12345', $admin['password']));

    $student = $pdo->query("SELECT * FROM users WHERE email = 'student@ilaropoly.edu.ng'")->fetch();
    assertTest("Student user exists", !empty($student) && $student['role'] === 'student');
    assertTest("Student password verifies ('Student@12345')", !empty($student) && password_verify('Student@12345', $student['password']));

    $staff = $pdo->query("SELECT * FROM users WHERE email = 'staff@ilaropoly.edu.ng'")->fetch();
    assertTest("Staff user exists", !empty($staff) && $staff['role'] === 'staff');
    assertTest("Staff password verifies ('Staff@12345')", !empty($staff) && password_verify('Staff@12345', $staff['password']));

    // 3. Test Reference ID Generator
    $ref = generate_report_reference();
    assertTest("Reference ID format matches CES-YYYY-XXXXX ($ref)", (bool)preg_match('/^CES-\d{4}-\d{5}$/', $ref));

    // 4. Test Notification Dispatch
    $notifSent = send_notification($student['id'], "Test Diagnostic Alert", "Automated system test", "info");
    assertTest("Notification dispatch functionality", $notifSent === true);

    // 5. Test Active Alerts
    $alerts = get_active_alerts();
    assertTest("Active alerts retrieval", is_array($alerts));

    // 6. Test System Log Activity
    log_activity("TEST_RUN", "Automated diagnostic test completed", $admin['id']);
    $latestLog = $pdo->query("SELECT * FROM system_logs ORDER BY id DESC LIMIT 1")->fetch();
    assertTest("Audit logging functionality", !empty($latestLog) && $latestLog['action'] === 'TEST_RUN');

} catch (Exception $e) {
    echo "Diagnostic Error: " . $e->getMessage() . "\n";
}

echo "\n=======================================================\n";
echo "Results: $testsPassed / $testsTotal tests passed.\n";
if ($testsPassed === $testsTotal) {
    echo "STATUS: ALL CORE SYSTEMS OPERATIONAL & VALIDATED!\n";
} else {
    echo "STATUS: SOME TESTS FAILED.\n";
}
echo "=======================================================\n";
