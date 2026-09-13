<?php
/**
 * Federal Polytechnic Ilaro - Campus Safety & Emergency Alert System
 * Vercel Serverless Front-Controller Entrypoint
 */

// Normalize requested URI
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);

// Remove trailing slash if not root
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

$rootDir = realpath(__DIR__ . '/..');

// 1. Root route -> index.php
if ($uri === '' || $uri === '/') {
    require $rootDir . '/index.php';
    exit;
}

// 2. Direct file lookup (e.g. /login.php, /student/dashboard.php, /admin/reports.php)
$targetFile = realpath($rootDir . $uri);

if ($targetFile && strpos($targetFile, $rootDir) === 0 && is_file($targetFile)) {
    if (pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
        require $targetFile;
        exit;
    }
    
    // Serve static mime types if reached through router
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'json' => 'application/json'
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($targetFile);
    exit;
}

// 3. Clean URL lookup without .php extension (e.g. /login -> /login.php)
$phpFile = realpath($rootDir . $uri . '.php');
if ($phpFile && strpos($phpFile, $rootDir) === 0 && is_file($phpFile)) {
    require $phpFile;
    exit;
}

// 4. Fallback 404
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - Campus Safety System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-slate-200 text-center shadow-lg space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-2xl font-black">
            404
        </div>
        <h2 class="text-xl font-black text-slate-900">Page Not Found</h2>
        <p class="text-xs text-slate-500">The requested route <code class="bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-700"><?= htmlspecialchars($uri) ?></code> was not found.</p>
        <div class="pt-2">
            <a href="/" class="inline-block py-2.5 px-6 rounded-xl bg-emerald-800 text-white font-bold text-xs shadow hover:bg-emerald-900 transition">
                Return to Campus Safety Home
            </a>
        </div>
    </div>
</body>
</html>
