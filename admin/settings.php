<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Admin System Configurations & Preferences
 */

/*
|--------------------------------------------------------------------------
| Page Configuration
|--------------------------------------------------------------------------
*/

$pageTitle = 'System Settings';


/*
|--------------------------------------------------------------------------
| Load Required Files
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';


/*
|--------------------------------------------------------------------------
| Require Administrator Access
|--------------------------------------------------------------------------
*/

require_admin();


/*
|--------------------------------------------------------------------------
| Current User & Database
|--------------------------------------------------------------------------
*/

$user = current_user();
$pdo = getDB();


/*
|--------------------------------------------------------------------------
| Handle Settings Update
|--------------------------------------------------------------------------
| IMPORTANT:
| This must happen BEFORE nav-portal.php because this section
| can use header() to redirect the administrator.
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'save_settings'
) {

    /*
    |--------------------------------------------------------------------------
    | Validate CSRF Token
    |--------------------------------------------------------------------------
    */

    if (!validate_csrf()) {

        set_flash(
            'error',
            'Security token invalid or expired. Please submit the form again.'
        );

        header(
            'Location: ' . BASE_URL . '/admin/settings.php'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Allowed Settings
    |--------------------------------------------------------------------------
    */

    $allowedKeys = [
        'institution_name',
        'system_name',
        'institution_short',
        'emergency_hotline',
        'medical_hotline',
        'campus_default_lat',
        'campus_default_lng',
        'broadcast_banner_enabled'
    ];


    /*
    |--------------------------------------------------------------------------
    | Save Settings
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO system_settings
            (setting_key, setting_value)
        VALUES
            (?, ?)
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value)
    ");


    foreach ($allowedKeys as $k) {

        $val = clean_input(
            $_POST[$k] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | Checkbox Handling
        |--------------------------------------------------------------------------
        */

        if ($k === 'broadcast_banner_enabled') {

            $val = isset($_POST[$k])
                ? '1'
                : '0';
        }


        $stmt->execute([
            $k,
            $val
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

    log_activity(
        'SETTINGS_UPDATED',
        'Updated institutional and map parameters.',
        $user['id']
    );


    /*
    |--------------------------------------------------------------------------
    | Success Message
    |--------------------------------------------------------------------------
    */

    set_flash(
        'success',
        'System configurations saved successfully.'
    );


    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    */

    header(
        'Location: ' . BASE_URL . '/admin/settings.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Current Settings
|--------------------------------------------------------------------------
*/

$settingsRows = $pdo
    ->query("
        SELECT setting_key, setting_value
        FROM system_settings
    ")
    ->fetchAll(PDO::FETCH_KEY_PAIR);


/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
| nav-portal.php is loaded AFTER all PHP processing that can
| send headers.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/nav-portal.php';

?>


<div class="max-w-4xl mx-auto space-y-8">
    
    <div>

        <h2 class="text-2xl font-black text-slate-900 tracking-tight">
            System Settings &amp; Preferences
        </h2>

        <p class="text-xs text-slate-500 mt-0.5">
            Customize institutional details, hotlines, and default geographic parameters
        </p>

    </div>


    <!-- Settings Form -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">

        <form
            action="<?= BASE_URL ?>/admin/settings.php"
            method="POST"
            class="space-y-6"
        >

            <?= csrf_field() ?>

            <input
                type="hidden"
                name="action"
                value="save_settings"
            >


            <!-- Section 1: Institutional Identification -->
            <div class="space-y-4">

                <h3 class="text-xs font-bold uppercase tracking-wider text-fpi-800 pb-2 border-b border-slate-100 flex items-center gap-2">

                    <i class="fa-solid fa-graduation-cap"></i>

                    Institutional Details

                </h3>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Institution Name
                        </label>

                        <input
                            type="text"
                            name="institution_name"
                            value="<?= e($settingsRows['institution_name'] ?? 'Federal Polytechnic Ilaro') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800"
                        >

                    </div>


                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Acronym / Short Title
                        </label>

                        <input
                            type="text"
                            name="institution_short"
                            value="<?= e($settingsRows['institution_short'] ?? 'FPI') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800"
                        >

                    </div>

                </div>


                <div>

                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Application Title
                    </label>

                    <input
                        type="text"
                        name="system_name"
                        value="<?= e($settingsRows['system_name'] ?? 'Campus Safety & Emergency Alert System') ?>"
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800"
                    >

                </div>

            </div>


            <!-- Section 2: Hotlines -->
            <div class="space-y-4 pt-2">

                <h3 class="text-xs font-bold uppercase tracking-wider text-fpi-800 pb-2 border-b border-slate-100 flex items-center gap-2">

                    <i class="fa-solid fa-phone"></i>

                    Emergency Dispatch Numbers

                </h3>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Primary Security Hotline
                        </label>

                        <input
                            type="text"
                            name="emergency_hotline"
                            value="<?= e($settingsRows['emergency_hotline'] ?? '+234 803 000 1199') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800"
                        >

                    </div>


                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Health Centre Clinic Hotline
                        </label>

                        <input
                            type="text"
                            name="medical_hotline"
                            value="<?= e($settingsRows['medical_hotline'] ?? '+234 802 555 4321') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800"
                        >

                    </div>

                </div>

            </div>


            <!-- Section 3: Geographic Coordinates -->
            <div class="space-y-4 pt-2">

                <h3 class="text-xs font-bold uppercase tracking-wider text-fpi-800 pb-2 border-b border-slate-100 flex items-center gap-2">

                    <i class="fa-solid fa-map-location-dot"></i>

                    Default Campus Map Coordinates

                </h3>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Default Center Latitude
                        </label>

                        <input
                            type="text"
                            name="campus_default_lat"
                            value="<?= e($settingsRows['campus_default_lat'] ?? '6.8928000') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-xs text-slate-800"
                        >

                    </div>


                    <div>

                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Default Center Longitude
                        </label>

                        <input
                            type="text"
                            name="campus_default_lng"
                            value="<?= e($settingsRows['campus_default_lng'] ?? '3.0165000') ?>"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-xs text-slate-800"
                        >

                    </div>

                </div>

            </div>


            <!-- Section 4: Alert Banner Toggle -->
            <div class="pt-2">

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">

                    <label class="cursor-pointer flex items-center gap-3">

                        <input
                            type="checkbox"
                            name="broadcast_banner_enabled"
                            value="1"
                            <?= (
                                !empty($settingsRows['broadcast_banner_enabled']) &&
                                $settingsRows['broadcast_banner_enabled'] == '1'
                            ) ? 'checked' : '' ?>
                            class="rounded text-fpi-800 focus:ring-fpi-700"
                        >

                        <div>

                            <span class="block text-xs font-bold text-slate-800">
                                Enable High-Priority Alert Banner Across Portals
                            </span>

                            <span class="text-[10px] text-slate-500">
                                When checked, active high/critical alerts render prominently at the top of landing &amp; user dashboards.
                            </span>

                        </div>

                    </label>

                </div>

            </div>


            <!-- Save Button -->
            <div class="pt-4 border-t border-slate-100">

                <button
                    type="submit"
                    class="py-3 px-6 rounded-xl bg-fpi-800 hover:bg-fpi-900 text-white font-bold text-xs shadow transition"
                >
                    Save System Settings
                </button>

            </div>

        </form>

    </div>

</div>


<?php

/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/footer-portal.php';

?>