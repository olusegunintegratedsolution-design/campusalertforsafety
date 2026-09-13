<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin Emergency Telephone Directory Management
 */

$pageTitle = 'Emergency Directory Management';
require_once __DIR__ . '/../includes/nav-portal.php';

require_admin();

$pdo = getDB();
$errors = [];

// Handle Add / Edit / Delete POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security verification failed.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_contact') {
            $name         = clean_input($_POST['name'] ?? '');
            $department   = clean_input($_POST['department'] ?? '');
            $phone        = clean_input($_POST['phone'] ?? '');
            $altPhone     = clean_input($_POST['alt_phone'] ?? '');
            $email        = clean_input($_POST['email'] ?? '');
            $availability = clean_input($_POST['availability'] ?? '24/7 Rapid Response');
            $category     = clean_input($_POST['category'] ?? 'Security');
            $priority     = (int)($_POST['priority_order'] ?? 1);

            if (empty($name) || empty($phone)) {
                $errors[] = 'Contact name and primary telephone number are required.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO emergency_contacts (name, department, phone, alt_phone, email, availability, category, priority_order) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $department, $phone, $altPhone, $email, $availability, $category, $priority]);
                log_activity('CONTACT_ADDED', "Added emergency contact $name", $user['id']);
                set_flash('success', "Emergency contact '$name' added successfully.");
                header('Location: ' . BASE_URL . '/admin/contacts.php');
                exit;
            }
        } elseif ($action === 'delete_contact') {
            $contactId = (int)($_POST['contact_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM emergency_contacts WHERE id = ?");
            $stmt->execute([$contactId]);
            log_activity('CONTACT_DELETED', "Deleted emergency contact id $contactId", $user['id']);
            set_flash('info', 'Emergency contact deleted.');
            header('Location: ' . BASE_URL . '/admin/contacts.php');
            exit;
        }
    }
}

// Fetch all contacts
$contactsList = $pdo->query("SELECT * FROM emergency_contacts ORDER BY priority_order ASC, name ASC")->fetchAll();
?>

<div class="space-y-8">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Emergency Directory Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage the institutional hotline telephone numbers and emergency dispatch agencies</p>
        </div>
        <a href="#add-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow-xs transition">
            <i class="fa-solid fa-plus"></i> Add New Hotline
        </a>
    </div>

    <!-- Error notice -->
    <?php if (!empty($errors)): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
        <?php foreach ($errors as $err): ?>
            <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-rose-600"></i> <?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Add Contact Form Card -->
    <div id="add-form" class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-5">
        <h3 class="text-base font-extrabold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-phone-volume text-fpi-800"></i>
            Register New Emergency Hotline
        </h3>

        <form action="<?= BASE_URL ?>/admin/contacts.php" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_contact">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contact Name / Unit <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Campus Security Squad Alpha" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Department / Organization <span class="text-red-500">*</span></label>
                    <input type="text" name="department" required placeholder="e.g. Central Security Directorate" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                        <option value="Security">Security</option>
                        <option value="Medical">Medical</option>
                        <option value="Fire">Fire</option>
                        <option value="Police">Police</option>
                        <option value="Management">Management</option>
                        <option value="Technical">Technical</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Primary Phone <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="+234 803 000 1199" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alternate Phone</label>
                    <input type="tel" name="alt_phone" placeholder="Optional backup number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" placeholder="security@ilaropoly.edu.ng" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Availability Schedule</label>
                    <input type="text" name="availability" value="24/7 Rapid Response" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs text-slate-800">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="py-3 px-6 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Emergency Contact
                </button>
            </div>
        </form>
    </div>

    <!-- Current Directory List Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 sm:p-8 space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 pb-3 border-b border-slate-100">
            Registered Emergency Directory (<?= count($contactsList) ?>)
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Contact Name / Unit</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Primary Phone</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($contactsList as $ct): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                            <?= e($ct['name']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 max-w-[180px] truncate">
                            <?= e($ct['department']) ?>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-fpi-800 border border-emerald-200">
                                <?= e($ct['category']) ?>
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800 whitespace-nowrap">
                            <a href="tel:<?= str_replace(' ', '', $ct['phone']) ?>" class="hover:text-fpi-800"><?= e($ct['phone']) ?></a>
                        </td>
                        <td class="py-3.5 px-4 text-emerald-700 font-semibold text-[11px] whitespace-nowrap">
                            <?= e($ct['availability']) ?>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <form action="<?= BASE_URL ?>/admin/contacts.php" method="POST" class="inline" onsubmit="return confirm('Delete this emergency contact hotline?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_contact">
                                <input type="hidden" name="contact_id" value="<?= $ct['id'] ?>">
                                <button type="submit" class="p-2 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 text-xs transition" title="Delete Hotline">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
