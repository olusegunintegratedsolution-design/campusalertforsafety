<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin User Management Directory
 */

$pageTitle = 'User Management Directory';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();

// Handle status toggling / role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (validate_csrf()) {
        $targetUserId = (int)($_POST['target_user_id'] ?? 0);

        if ($_POST['action'] === 'toggle_status') {
            // Prevent self-deactivation
            if ($targetUserId === $user['id']) {
                set_flash('error', 'You cannot deactivate your own administrative account.');
            } else {
                $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
                $stmt->execute([$targetUserId]);
                log_activity('USER_STATUS_TOGGLED', "Toggled status of user id $targetUserId", $user['id']);
                set_flash('success', 'User account status updated.');
            }
            header('Location: ' . BASE_URL . '/admin/users.php');
            exit;
        } elseif ($_POST['action'] === 'change_role') {
            $newRole = clean_input($_POST['new_role'] ?? '');
            if (in_array($newRole, ['student', 'staff', 'admin']) && $targetUserId !== $user['id']) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $targetUserId]);
                log_activity('USER_ROLE_CHANGED', "Changed role of user id $targetUserId to $newRole", $user['id']);
                set_flash('success', "User role changed to $newRole.");
            }
            header('Location: ' . BASE_URL . '/admin/users.php');
            exit;
        }
    }
}

// Search and filter parameters
$q    = clean_input($_GET['q'] ?? '');
$role = clean_input($_GET['role'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR id_number LIKE ? OR phone LIKE ?)";
    $term = "%$q%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}
if (!empty($role)) {
    $sql .= " AND role = ?";
    $params[] = $role;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll();
?>

<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Campus User Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Directory of registered students, academic staff, and security personnel</p>
        </div>
        <div class="text-xs text-slate-400 font-mono bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-xs">
            Total Accounts: <strong><?= count($usersList) ?></strong>
        </div>
    </div>

    <!-- Search & Role Filter Bar -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <form action="<?= BASE_URL ?>/admin/users.php" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by name, email, matric/staff ID, or phone number..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
            </div>

            <div class="w-full sm:w-48">
                <select name="role" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none text-xs font-medium text-slate-800">
                    <option value="">All Roles</option>
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Students</option>
                    <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administrators</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
                Search
            </button>
            <?php if (!empty($q) || !empty($role)): ?>
            <a href="<?= BASE_URL ?>/admin/users.php" class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 text-xs transition" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (!empty($usersList)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-5">Name / ID</th>
                        <th class="py-3.5 px-5">Email Address</th>
                        <th class="py-3.5 px-5">Phone No.</th>
                        <th class="py-3.5 px-5">Role</th>
                        <th class="py-3.5 px-5">Department</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5">Joined</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($usersList as $u): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-fpi-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block"><?= e($u['name']) ?></span>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= e($u['id_number']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 whitespace-nowrap">
                            <a href="mailto:<?= e($u['email']) ?>" class="hover:text-fpi-800 underline"><?= e($u['email']) ?></a>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 whitespace-nowrap font-mono">
                            <?= e($u['phone']) ?>
                        </td>
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase <?= 
                                $u['role'] === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-300' :
                                ($u['role'] === 'staff' ? 'bg-sky-100 text-sky-800 border border-sky-300' :
                                'bg-emerald-100 text-emerald-800 border border-emerald-300')
                            ?>">
                                <?= e($u['role']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 max-w-[160px] truncate">
                            <?= e($u['department']) ?>
                        </td>
                        <td class="py-3.5 px-5 whitespace-nowrap">
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-rose-600 font-bold text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3.5 px-5 text-slate-400 whitespace-nowrap">
                            <?= date('M j, Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-2">
                            <!-- Toggle Status Form -->
                            <?php if ($u['id'] !== $user['id']): ?>
                            <form action="<?= BASE_URL ?>/admin/users.php" method="POST" class="inline" onsubmit="return confirm('Change status for this user?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="px-2.5 py-1 rounded-lg border text-xs font-semibold <?= $u['status'] === 'active' ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' ?>">
                                    <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                            <?php else: ?>
                                <span class="text-[10px] text-slate-400 italic">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-16 text-center text-slate-400 text-xs">
            No registered users found matching filter.
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
