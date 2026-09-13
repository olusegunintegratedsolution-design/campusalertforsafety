<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Emergency Reporting Interface
 */

$pageTitle = 'Report an Emergency';
require_once __DIR__ . '/../includes/nav-portal.php';

$pdo = getDB();

// Fetch campus locations for selector
$locationsStmt = $pdo->query("SELECT * FROM campus_locations ORDER BY name ASC");
$campusLocations = $locationsStmt->fetchAll();

$errors = [];
$submittedReport = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security verification failed. Please refresh and try again.';
    } else {
        $emergencyType = clean_input($_POST['emergency_type'] ?? '');
        $locationId = !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null;
        $locationManual = clean_input($_POST['location_name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');
        $severity = clean_input($_POST['severity'] ?? 'Medium');
        $phone = clean_input($_POST['reporter_phone'] ?? $user['phone']);
        $allowContact = isset($_POST['allow_contact']) ? 1 : 0;
        $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : 6.8928000;
        $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : 3.0165000;

        // Validation
        $allowedTypes = [
            'Fire', 'Medical Emergency', 'Security Threat', 'Accident', 
            'Theft', 'Violence', 'Gas Leak', 'Infrastructure Failure', 
            'Natural Hazard', 'Other'
        ];
        if (!in_array($emergencyType, $allowedTypes)) {
            $errors[] = 'Please select a valid emergency incident category.';
        }

        if (empty($locationManual)) {
            // If location dropdown selected, get name
            if ($locationId) {
                foreach ($campusLocations as $loc) {
                    if ($loc['id'] == $locationId) {
                        $locationManual = $loc['name'];
                        break;
                    }
                }
            } else {
                $errors[] = 'Please specify the campus location of the incident.';
            }
        }

        if (empty($description) || strlen($description) < 10) {
            $errors[] = 'Please provide a descriptive explanation of the incident (at least 10 characters).';
        }

        if (!in_array($severity, ['Low', 'Medium', 'High', 'Critical'])) {
            $severity = 'Medium';
        }

        // Image file upload handling
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, $allowedExts)) {
                $errors[] = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Evidence image size must not exceed 5MB.';
            } elseif ($file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__) . '/assets/uploads/reports/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFilename = 'report_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $targetFile = $uploadDir . $newFilename;
                
                if (@move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $imagePath = 'assets/uploads/reports/' . $newFilename;
                } else {
                    // Serverless fallback (e.g. Vercel read-only filesystem)
                    $imgData = @file_get_contents($file['tmp_name']);
                    if ($imgData) {
                        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'image/' . $fileExt;
                        $imagePath = 'data:' . $mime . ';base64,' . base64_encode($imgData);
                    }
                }
            }
        }

        if (empty($errors)) {
            $refId = generate_report_reference();

            $insertStmt = $pdo->prepare("
                INSERT INTO emergency_reports (
                    report_reference, user_id, emergency_type, description, 
                    location_id, location_name, latitude, longitude, severity, 
                    image_path, reporter_phone, allow_contact, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");

            try {
                $insertStmt->execute([
                    $refId,
                    $user['id'],
                    $emergencyType,
                    $description,
                    $locationId,
                    $locationManual,
                    $latitude,
                    $longitude,
                    $severity,
                    $imagePath,
                    $phone,
                    $allowContact
                ]);
                $newReportId = (int)$pdo->lastInsertId();

                // Add to timeline
                $timelineStmt = $pdo->prepare("
                    INSERT INTO report_timeline (report_id, action_by, status_to, notes, created_at) 
                    VALUES (?, ?, 'Pending', 'Emergency report filed by student/staff.', NOW())
                ");
                $timelineStmt->execute([$newReportId, $user['id']]);

                // Notify all Campus Administrators
                notify_all_admins(
                    "EMERGENCY: $emergencyType ($severity)",
                    "New incident reported at $locationManual (Ref: $refId)",
                    'alert',
                    "admin/report-view.php?id=$newReportId"
                );

                // Notify reporter
                send_notification(
                    $user['id'],
                    "Report Submitted: $refId",
                    "Your $emergencyType incident report at $locationManual was received and logged with Campus Security.",
                    'info',
                    "student/my-reports.php?ref=$refId"
                );

                // Log system activity
                log_activity('EMERGENCY_REPORTED', "Created report $refId ($emergencyType - $severity)", $user['id']);

                $submittedReport = [
                    'reference' => $refId,
                    'type' => $emergencyType,
                    'location' => $locationManual,
                    'severity' => $severity,
                    'id' => $newReportId
                ];

            } catch (Exception $e) {
                $errors[] = 'Failed to submit report: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php if ($submittedReport): ?>
<!-- SUCCESS CONFIRMATION RECEIPT MODAL / CARD -->
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-3xl p-8 border-2 border-emerald-500 shadow-2xl space-y-6 text-center">
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl animate-bounce">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 mb-2">
                Emergency Report Received
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                Dispatched to Security Operations
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 mt-1">
                Your incident has been flagged and transmitted to the Central Security Command Unit.
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3 font-mono text-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500 uppercase">Incident Reference ID:</span>
                <span class="text-base font-extrabold text-fpi-800 font-sans tracking-wide"><?= e($submittedReport['reference']) ?></span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500 uppercase">Category:</span>
                <span class="font-bold text-slate-900 font-sans"><?= e($submittedReport['type']) ?></span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <span class="text-slate-500 uppercase">Location:</span>
                <span class="font-bold text-slate-900 font-sans"><?= e($submittedReport['location']) ?></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500 uppercase">Initial Severity:</span>
                <span><?= get_severity_badge($submittedReport['severity']) ?></span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <a href="<?= BASE_URL ?>/student/my-reports.php?ref=<?= urlencode($submittedReport['reference']) ?>" class="flex-1 py-3 px-4 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow transition text-center">
                <i class="fa-solid fa-timeline mr-1.5"></i> Track Report Status
            </a>
            <a href="<?= BASE_URL ?>/student/report-emergency.php" class="py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition text-center">
                Submit Another Report
            </a>
        </div>
    </div>
</div>

<?php else: ?>

<!-- REPORTING FORM WRAPPER -->
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Top Emergency Advisory Strip -->
    <div class="p-4 rounded-2xl bg-red-50 border border-red-200 flex items-start gap-3 shadow-xs">
        <div class="text-red-600 text-lg mt-0.5 flex-shrink-0">
            <i class="fa-solid fa-triangle-exclamation animate-pulse"></i>
        </div>
        <div class="text-xs text-red-900 leading-relaxed">
            <strong>Immediate Life Threat?</strong> If someone is in mortal danger or an explosive fire is spreading, contact the 24/7 Campus Security Hotline directly at 
            <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="font-bold underline ml-1"><?= EMERGENCY_HOTLINE ?></a> or 
            Clinic at <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="font-bold underline ml-1"><?= CLINIC_HOTLINE ?></a>.
        </div>
    </div>

    <!-- Error notices -->
    <?php if (!empty($errors)): ?>
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-300 text-rose-800 text-xs space-y-1 shadow-sm">
        <?php foreach ($errors as $err): ?>
            <p class="flex items-center gap-2"><i class="fa-solid fa-circle-xmark text-rose-600"></i> <?= e($err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Main Reporting Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm space-y-8">
        
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Campus Emergency Report Form</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Please provide accurate details so the nearest security or medical unit can locate you immediately.
            </p>
        </div>

        <form action="<?= BASE_URL ?>/student/report-emergency.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <?= csrf_field() ?>

            <!-- SECTION 1: EMERGENCY TYPE SELECTION -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                    1. Select Emergency Category <span class="text-red-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <?php
                    $types = [
                        ['id' => 'Fire', 'label' => 'Fire', 'icon' => 'fa-fire', 'color' => 'text-rose-600'],
                        ['id' => 'Medical Emergency', 'label' => 'Medical', 'icon' => 'fa-kit-medical', 'color' => 'text-red-600'],
                        ['id' => 'Security Threat', 'label' => 'Security Threat', 'icon' => 'fa-shield-halved', 'color' => 'text-indigo-600'],
                        ['id' => 'Accident', 'label' => 'Accident', 'icon' => 'fa-car-burst', 'color' => 'text-orange-600'],
                        ['id' => 'Theft', 'label' => 'Theft', 'icon' => 'fa-mask', 'color' => 'text-purple-600'],
                        ['id' => 'Violence', 'label' => 'Violence', 'icon' => 'fa-hand-fist', 'color' => 'text-red-700'],
                        ['id' => 'Gas Leak', 'label' => 'Gas/Fume Leak', 'icon' => 'fa-smog', 'color' => 'text-yellow-600'],
                        ['id' => 'Infrastructure Failure', 'label' => 'Infrastructure', 'icon' => 'fa-triangle-exclamation', 'color' => 'text-amber-600'],
                        ['id' => 'Natural Hazard', 'label' => 'Storm/Hazard', 'icon' => 'fa-cloud-bolt', 'color' => 'text-teal-600'],
                        ['id' => 'Other', 'label' => 'Other', 'icon' => 'fa-circle-exclamation', 'color' => 'text-slate-600']
                    ];

                    foreach ($types as $t):
                    ?>
                    <label class="cursor-pointer relative p-3.5 rounded-2xl border border-slate-200 hover:border-slate-300 has-[:checked]:border-fpi-700 has-[:checked]:bg-emerald-50/70 has-[:checked]:ring-2 has-[:checked]:ring-fpi-700 transition flex flex-col items-center justify-center text-center group">
                        <input type="radio" name="emergency_type" value="<?= e($t['id']) ?>" required <?= (isset($_POST['emergency_type']) && $_POST['emergency_type'] === $t['id']) ? 'checked' : '' ?> class="sr-only">
                        <i class="fa-solid <?= $t['icon'] ?> text-2xl <?= $t['color'] ?> mb-2 group-hover:scale-110 transition"></i>
                        <span class="text-xs font-bold text-slate-800"><?= e($t['label']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION 2: SEVERITY LEVEL -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                    2. Severity &amp; Urgency Assessment <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="cursor-pointer p-3.5 rounded-2xl border border-slate-200 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-600 transition">
                        <input type="radio" name="severity" value="Low" class="sr-only">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-900">LOW</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Minor issue, no immediate physical risk</p>
                    </label>

                    <label class="cursor-pointer p-3.5 rounded-2xl border border-slate-200 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:ring-2 has-[:checked]:ring-amber-500 transition">
                        <input type="radio" name="severity" value="Medium" checked class="sr-only">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold text-slate-900">MEDIUM</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Potential escalation, requires response</p>
                    </label>

                    <label class="cursor-pointer p-3.5 rounded-2xl border border-slate-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:ring-2 has-[:checked]:ring-orange-500 transition">
                        <input type="radio" name="severity" value="High" class="sr-only">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                            <span class="text-xs font-bold text-slate-900">HIGH</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Serious danger to life or facilities</p>
                    </label>

                    <label class="cursor-pointer p-3.5 rounded-2xl border border-slate-200 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50 has-[:checked]:ring-2 has-[:checked]:ring-rose-600 transition">
                        <input type="radio" name="severity" value="Critical" class="sr-only">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                            <span class="text-xs font-bold text-slate-900">CRITICAL</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Active life hazard, immediate sirens</p>
                    </label>
                </div>
            </div>

            <!-- SECTION 3: CAMPUS LOCATION & INTERACTIVE LEAFLET PIN -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        3. Campus Location Details <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-500">Select standard landmark or drag the pin on map</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Standard location dropdown -->
                    <div>
                        <label for="location_id" class="block text-xs font-semibold text-slate-600 mb-1">
                            Common Campus Landmark
                        </label>
                        <select id="location_id" name="location_id" onchange="onLandmarkSelected(this)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                            <option value="">-- Choose Known Location --</option>
                            <?php foreach ($campusLocations as $cl): ?>
                                <option value="<?= $cl['id'] ?>" data-lat="<?= $cl['latitude'] ?>" data-lng="<?= $cl['longitude'] ?>" data-name="<?= e($cl['name']) ?>">
                                    <?= e($cl['name']) ?> (<?= e($cl['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Specific location text description -->
                    <div>
                        <label for="location_name" class="block text-xs font-semibold text-slate-600 mb-1">
                            Exact Room / Landmark / Spot
                        </label>
                        <input type="text" id="location_name" name="location_name" required value="<?= e($_POST['location_name'] ?? '') ?>" placeholder="e.g. Science Complex Block B, Room 104 or Corridor" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>
                </div>

                <!-- Leaflet Location Picker Map -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-crosshairs text-fpi-800"></i> Click on map or drag the pin to pinpoint location</span>
                        <span id="coords-display" class="font-mono text-[10px] text-slate-400">Lat: 6.89280, Lng: 3.01650</span>
                    </div>
                    <div id="picker-map" class="h-64 rounded-2xl border border-slate-300 z-10"></div>
                    <input type="hidden" id="latitude" name="latitude" value="<?= e($_POST['latitude'] ?? '6.8928000') ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?= e($_POST['longitude'] ?? '3.0165000') ?>">
                </div>
            </div>

            <!-- SECTION 4: DESCRIPTION -->
            <div class="space-y-2">
                <label for="description" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                    4. Incident Description <span class="text-red-500">*</span>
                </label>
                <textarea id="description" name="description" rows="4" required placeholder="Describe what happened, current hazards, number of people affected, or if any suspicious individuals are still around..." class="w-full p-4 rounded-2xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs sm:text-sm font-medium text-slate-800 leading-relaxed"><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- SECTION 5: EVIDENCE IMAGE & CONTACT PREFERENCE -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                
                <!-- File upload -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        5. Photo / Evidence (Optional)
                    </label>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative">
                        <input type="file" id="report-image-input" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-1">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-400 text-2xl"></i>
                            <div class="text-xs text-slate-600 font-medium">Click to upload photo evidence</div>
                            <p class="text-[10px] text-slate-400">JPG, PNG, WEBP up to 5MB</p>
                        </div>
                    </div>
                    <!-- Image preview -->
                    <div id="image-preview-container" class="hidden mt-2">
                        <img id="image-preview-img" src="#" alt="Preview" class="h-28 rounded-xl object-cover border border-slate-300 shadow-xs">
                    </div>
                </div>

                <!-- Contact phone & Callback preference -->
                <div class="space-y-4">
                    <div>
                        <label for="reporter_phone" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                            6. Contact Phone Number
                        </label>
                        <input type="tel" id="reporter_phone" name="reporter_phone" value="<?= e($_POST['reporter_phone'] ?? $user['phone']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-fpi-700 text-xs font-medium text-slate-800">
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <label class="cursor-pointer flex items-start gap-3">
                            <input type="checkbox" name="allow_contact" value="1" checked class="mt-0.5 rounded text-fpi-800 focus:ring-fpi-700">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Allow security personnel to contact me</span>
                                <span class="text-[10px] text-slate-500">Security officers may call your number to verify directions or triage patient condition.</span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Prominent Submit Action Button -->
            <div class="pt-4 border-t border-slate-200">
                <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 via-rose-600 to-red-700 hover:from-red-500 hover:to-rose-600 text-white font-extrabold text-sm sm:text-base tracking-wide shadow-xl shadow-red-600/30 hover:shadow-2xl transition transform hover:-translate-y-0.5 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-300 text-lg"></i>
                    <span>SUBMIT EMERGENCY REPORT NOW</span>
                </button>
                <p class="text-center text-[11px] text-slate-400 mt-2">
                    False emergency alarms are subject to disciplinary measures under Federal Polytechnic Ilaro safety regulations.
                </p>
            </div>

        </form>

    </div>

</div>

<!-- Location Picker Init Script -->
<script>
let pickerMapInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    pickerMapInstance = initLocationPickerMap('picker-map', 'latitude', 'longitude', 6.8928, 3.0165);
    
    // Update coordinates display
    const latInp = document.getElementById('latitude');
    const lngInp = document.getElementById('longitude');
    const display = document.getElementById('coords-display');
    
    const updateDisplay = () => {
        if (display && latInp && lngInp) {
            display.textContent = `Lat: ${parseFloat(latInp.value).toFixed(5)}, Lng: ${parseFloat(lngInp.value).toFixed(5)}`;
        }
    };
    
    if (latInp && lngInp) {
        latInp.addEventListener('change', updateDisplay);
        lngInp.addEventListener('change', updateDisplay);
    }
});

function onLandmarkSelected(select) {
    const selectedOption = select.options[select.selectedIndex];
    const lat = selectedOption.getAttribute('data-lat');
    const lng = selectedOption.getAttribute('data-lng');
    const name = selectedOption.getAttribute('data-name');
    
    if (name) {
        document.getElementById('location_name').value = name;
    }
    
    if (lat && lng && pickerMapInstance) {
        const newLatLng = new L.LatLng(parseFloat(lat), parseFloat(lng));
        pickerMapInstance.marker.setLatLng(newLatLng);
        pickerMapInstance.map.setView(newLatLng, 17);
        document.getElementById('latitude').value = parseFloat(lat).toFixed(7);
        document.getElementById('longitude').value = parseFloat(lng).toFixed(7);
        document.getElementById('coords-display').textContent = `Lat: ${parseFloat(lat).toFixed(5)}, Lng: ${parseFloat(lng).toFixed(5)}`;
    }
}
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer-portal.php'; ?>
