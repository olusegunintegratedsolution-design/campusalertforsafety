<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * User Registration Page (Student & Staff)
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already authenticated
redirect_if_logged_in();

$errors = [];
$formData = [
    'name'       => '',
    'email'      => '',
    'phone'      => '',
    'role'       => 'student',
    'id_number'  => '',
    'department' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security token invalid or expired. Please submit the form again.';
    } else {
        $name       = clean_input($_POST['name'] ?? '');
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $phone      = clean_input($_POST['phone'] ?? '');
        $role       = in_array($_POST['role'] ?? '', ['student', 'staff']) ? $_POST['role'] : 'student';
        $idNumber   = strtoupper(clean_input($_POST['id_number'] ?? ''));
        $department = clean_input($_POST['department'] ?? '');
        $password   = $_POST['password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        $formData = compact('name', 'email', 'phone', 'role', 'idNumber', 'department');

        // Validation
        if (empty($name) || strlen($name) < 3) {
            $errors[] = 'Full name must be at least 3 characters long.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid institutional email address.';
        }
        if (empty($phone) || strlen($phone) < 10) {
            $errors[] = 'Please provide a valid phone number for emergency contact.';
        }
        if (empty($idNumber)) {
            $errors[] = $role === 'student' ? 'Matriculation Number is required.' : 'Staff ID Number is required.';
        }
        if (empty($department)) {
            $errors[] = 'Please select or enter your academic department/school.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($password !== $confirmPwd) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $pdo = getDB();

            // Check if email already registered
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'An account with this email address is already registered.';
            } else {
                // Secure password hashing
                $hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, role, id_number, department, password, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                ");

                try {
                    $stmt->execute([$name, $email, $phone, $role, $idNumber, $department, $hash]);
                    $newUserId = (int)$pdo->lastInsertId();

                    // Send welcome notification
                    send_notification(
                        $newUserId,
                        'Welcome to Campus Safety System',
                        'Your safety profile has been created. In the event of an emergency, use this platform for instant reporting.',
                        'info',
                        'student/dashboard.php'
                    );

                    // Fetch user and log them in
                    $fetchUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $fetchUser->execute([$newUserId]);
                    $registeredUser = $fetchUser->fetch();

                    login_user($registeredUser);
                    set_flash('success', 'Registration successful! Welcome to the FPI Campus Safety Network.');
                    header('Location: ' . BASE_URL . '/student/dashboard.php');
                    exit;

                } catch (Exception $e) {
                    $errors[] = 'Registration failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Register Account';
require_once __DIR__ . '/includes/header.php';

$departments = [
    'Computer Science',
    'Electrical / Electronics Engineering',
    'Mechanical Engineering',
    'Civil Engineering',
    'Agricultural & Bio-Environmental Engineering',
    'Science Laboratory Technology (SLT)',
    'Food Technology',
    'Statistics & Mathematics',
    'Mass Communication',
    'Business Administration & Management',
    'Accountancy',
    'Banking & Finance',
    'Marketing',
    'Public Administration',
    'Architecture',
    'Estate Management & Valuation',
    'Quantity Surveying',
    'Surveying & Geo-Informatics',
    'Building Technology',
    'Urban & Regional Planning',
    'Library & Information Science',
    'Hospitality Management',
    'Security / Works Staff'
];
?>

<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-6">
        
        <!-- Header -->
        <div class="text-center">
            <div class="w-14 h-14 mx-auto mb-3">
                <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Create Safety Profile
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Register as a Student or Staff member of Federal Polytechnic Ilaro
            </p>
        </div>

        <!-- Error notification -->
        <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
            <?php foreach ($errors as $err): ?>
                <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-rose-600"></i> <?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50">
            <form action="<?= BASE_URL ?>/register.php" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <!-- Role Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">I am Registering As:</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer flex items-center gap-3 p-3 rounded-xl border border-slate-200 has-[:checked]:border-fpi-700 has-[:checked]:bg-emerald-50 transition">
                            <input type="radio" name="role" value="student" <?= ($formData['role'] ?? 'student') === 'student' ? 'checked' : '' ?> class="text-fpi-800 focus:ring-fpi-700" onchange="updateIdLabel('Matriculation Number', 'e.g. FPI/ND/CS/23/0012')">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Student</span>
                                <span class="text-[10px] text-slate-500">ND / HND Candidate</span>
                            </div>
                        </label>
                        <label class="cursor-pointer flex items-center gap-3 p-3 rounded-xl border border-slate-200 has-[:checked]:border-fpi-700 has-[:checked]:bg-emerald-50 transition">
                            <input type="radio" name="role" value="staff" <?= ($formData['role'] ?? '') === 'staff' ? 'checked' : '' ?> class="text-fpi-800 focus:ring-fpi-700" onchange="updateIdLabel('Staff ID / File Number', 'e.g. FPI/STF/2018/104')">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Academic / Staff</span>
                                <span class="text-[10px] text-slate-500">Faculty / Non-Teaching</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Full Name (Surname First)
                    </label>
                    <input type="text" id="name" name="name" required value="<?= e($formData['name'] ?? '') ?>" placeholder="Adebayo, Oluwaseun Emmanuel" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                </div>

                <!-- Email & Phone Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" required value="<?= e($formData['email'] ?? '') ?>" placeholder="student@ilaropoly.edu.ng" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Emergency Phone No.
                        </label>
                        <input type="tel" id="phone" name="phone" required value="<?= e($formData['phone'] ?? '') ?>" placeholder="08140000000" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                </div>

                <!-- ID Number & Department Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="id_number" id="id-label" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Matriculation Number
                        </label>
                        <input type="text" id="id_number" name="id_number" required value="<?= e($formData['id_number'] ?? '') ?>" placeholder="e.g. FPI/ND/CS/23/0012" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                    <div>
                        <label for="department" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Academic Department
                        </label>
                        <select id="department" name="department" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= e($dept) ?>" <?= ($formData['department'] ?? '') === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Password & Confirm -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Password
                        </label>
                        <input type="password" id="password" name="password" required placeholder="At least 6 characters" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Confirm Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-type password" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Complete Registration
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
                Already registered with the safety system?
                <a href="<?= BASE_URL ?>/login.php" class="font-bold text-fpi-800 hover:text-fpi-900 ml-1">Sign In</a>
            </div>
        </div>

    </div>
</div>

<script>
function updateIdLabel(label, placeholder) {
    document.getElementById('id-label').textContent = label;
    document.getElementById('id_number').placeholder = placeholder;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
