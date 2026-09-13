<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Core Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Clean & sanitize general string input
 */
function clean_input($data): string {
    if (is_array($data)) {
        return '';
    }
    $data = trim((string)$data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize output for HTML display
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate or get CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden CSRF form input
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validate CSRF token from POST
 */
function validate_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    return false;
}

/**
 * Flash notification messaging
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function has_flash(): bool {
    return isset($_SESSION['flash']);
}

function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash(): string {
    $flash = get_flash();
    if (!$flash) {
        return '';
    }

    $type = $flash['type'];
    $msg = e($flash['message']);

    $colorClasses = [
        'success' => 'bg-emerald-50 border-emerald-300 text-emerald-800 <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>',
        'error'   => 'bg-rose-50 border-rose-300 text-rose-800 <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>',
        'warning' => 'bg-amber-50 border-amber-300 text-amber-800 <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>',
        'info'    => 'bg-sky-50 border-sky-300 text-sky-800 <i class="fa-solid fa-circle-info text-sky-600 text-lg"></i>'
    ];

    $cfg = $colorClasses[$type] ?? $colorClasses['info'];
    [$class, $icon] = explode(' <i', $cfg);
    $icon = '<i' . $icon;

    return <<<HTML
    <div class="mb-6 p-4 rounded-xl border flex items-start space-x-3 shadow-sm transition-all duration-300 $class" role="alert">
        <div class="flex-shrink-0 mt-0.5">$icon</div>
        <div class="flex-1 text-sm font-medium leading-relaxed">$msg</div>
        <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 ml-auto focus:outline-none">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
HTML;
}

/**
 * Generate unique CES Incident Reference ID
 * Example: CES-2026-00142
 */
function generate_report_reference(): string {
    $pdo = getDB();
    $year = date('Y');
    
    // Count existing reports for year to maintain sequential look
    $stmt = $pdo->query("SELECT COUNT(*) FROM emergency_reports WHERE YEAR(created_at) = $year");
    $count = (int)$stmt->fetchColumn() + 1;
    
    do {
        $ref = sprintf("CES-%s-%05d", $year, $count);
        $check = $pdo->prepare("SELECT id FROM emergency_reports WHERE report_reference = ?");
        $check->execute([$ref]);
        $exists = $check->fetchColumn();
        if ($exists) {
            $count++;
        }
    } while ($exists);

    return $ref;
}

/**
 * Returns HTML badge for incident severity
 */
function get_severity_badge(string $severity): string {
    switch (ucfirst(strtolower($severity))) {
        case 'Critical':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200"><span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>CRITICAL</span>';
        case 'High':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-200"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>HIGH</span>';
        case 'Medium':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>MEDIUM</span>';
        case 'Low':
        default:
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>LOW</span>';
    }
}

/**
 * Returns HTML badge for incident status
 */
function get_status_badge(string $status): string {
    switch (ucwords(strtolower($status))) {
        case 'Pending':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300"><i class="fa-regular fa-clock text-[11px]"></i> Pending Review</span>';
        case 'Acknowledged':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-300"><i class="fa-solid fa-clipboard-check text-[11px]"></i> Acknowledged</span>';
        case 'In Progress':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900 border border-amber-300"><i class="fa-solid fa-spinner fa-spin text-[11px]"></i> In Progress</span>';
        case 'Resolved':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"><i class="fa-solid fa-circle-check text-[11px]"></i> Resolved</span>';
        case 'Rejected':
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300"><i class="fa-solid fa-ban text-[11px]"></i> Rejected</span>';
        default:
            return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">' . e($status) . '</span>';
    }
}

/**
 * Emergency type icon helper
 */
function get_emergency_icon(string $type): string {
    $icons = [
        'Fire'                  => 'fa-solid fa-fire text-rose-600',
        'Medical Emergency'     => 'fa-solid fa-kit-medical text-red-600',
        'Security Threat'       => 'fa-solid fa-shield-halved text-indigo-600',
        'Accident'              => 'fa-solid fa-car-burst text-orange-600',
        'Theft'                 => 'fa-solid fa-mask text-purple-600',
        'Violence'              => 'fa-solid fa-hand-fist text-red-700',
        'Gas Leak'              => 'fa-solid fa-smog text-yellow-600',
        'Infrastructure Failure'=> 'fa-solid fa-triangle-exclamation text-amber-600',
        'Natural Hazard'        => 'fa-solid fa-cloud-bolt text-teal-600',
        'Other'                 => 'fa-solid fa-circle-exclamation text-slate-600'
    ];
    return $icons[$type] ?? 'fa-solid fa-triangle-exclamation text-slate-600';
}

/**
 * Dispatch an in-app notification to a user
 */
function send_notification(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): bool {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $message, $type, $link]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Notify all administrators
 */
function notify_all_admins(string $title, string $message, string $type = 'alert', ?string $link = null): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $insert = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        foreach ($admins as $adminId) {
            $insert->execute([$adminId, $title, $message, $type, $link]);
        }
    } catch (Exception $e) {
        // silent fail
    }
}

/**
 * Unread notification count for user
 */
function get_unread_notification_count(int $userId): int {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * System audit activity logger
 */
function log_activity(string $action, ?string $details = null, ?int $userId = null): void {
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userEmail = null;

        if ($userId === null && isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $userEmail = $_SESSION['user']['email'] ?? null;
        } elseif ($userId !== null) {
            $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userEmail = $stmt->fetchColumn() ?: null;
        }

        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, user_email, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $userEmail, $action, $details, $ip]);
    } catch (Exception $e) {
        // silent fail
    }
}

/**
 * Relative time calculation (e.g. 5 mins ago)
 */
function time_ago($datetime): string {
    if (!$datetime) return 'Never';
    $time = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $m = floor($diff / 60);
        return $m . ($m > 1 ? ' mins ago' : ' min ago');
    } elseif ($diff < 86400) {
        $h = floor($diff / 3600);
        return $h . ($h > 1 ? ' hrs ago' : ' hr ago');
    } elseif ($diff < 604800) {
        $d = floor($diff / 86400);
        return $d . ($d > 1 ? ' days ago' : ' day ago');
    } else {
        return date('M j, Y g:i A', $time);
    }
}

/**
 * Fetch active alerts for display
 */
function get_active_alerts(string $target = 'Everyone'): array {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM alerts 
            WHERE is_active = 1 
              AND (expiry_time IS NULL OR expiry_time > NOW())
              AND (target_audience = 'Everyone' OR target_audience = ?)
            ORDER BY 
              CASE severity 
                WHEN 'Critical' THEN 1 
                WHEN 'High' THEN 2 
                WHEN 'Medium' THEN 3 
                ELSE 4 
              END, 
              created_at DESC
        ");
        $stmt->execute([$target]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
