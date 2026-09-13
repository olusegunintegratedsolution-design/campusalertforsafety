<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * Safety Guides & Protocols Page
 */

$pageTitle = 'Safety Information & Guides';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gradient-to-b from-fpi-900 to-fpi-800 text-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-700/60">
            Emergency Preparedness Manual
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mt-3">
            Campus Safety &amp; Emergency Protocols
        </h1>
        <p class="text-slate-300 max-w-2xl text-sm sm:text-base mt-2">
            Standard operating procedures and life-saving steps for students and staff across Federal Polytechnic Ilaro.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    
    <!-- Quick Navigation Pills -->
    <div class="flex flex-wrap gap-2 mb-12 p-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <a href="#fire" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-fire text-rose-500"></i> Fire Safety</a>
        <a href="#medical" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-kit-medical text-red-500"></i> Medical Emergencies</a>
        <a href="#security" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-indigo-500"></i> Security Threats</a>
        <a href="#theft" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-mask text-purple-500"></i> Theft Prevention</a>
        <a href="#evacuation" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-person-running text-amber-500"></i> Emergency Evacuation</a>
        <a href="#accident" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-car-burst text-orange-500"></i> Accident Response</a>
        <a href="#general" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-fpi-800 transition flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-500"></i> General Campus Rules</a>
    </div>

    <!-- 1. Fire Safety -->
    <div id="fire" class="scroll-mt-32 mb-16 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-fire"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Fire Safety &amp; Prevention Protocol</h2>
                <p class="text-xs text-slate-500">Applies to lecture halls, laboratories, workshops, and student hostels</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
            <div class="bg-rose-50/50 p-6 rounded-2xl border border-rose-100 space-y-3">
                <h4 class="font-bold text-rose-900 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> If You Discover a Fire:
                </h4>
                <ul class="space-y-2 text-slate-700">
                    <li class="flex items-start gap-2"><strong>1.</strong> Pull the manual fire alarm or shout "FIRE!" to alert nearby occupants.</li>
                    <li class="flex items-start gap-2"><strong>2.</strong> Immediately submit an emergency report via CES or call the Fire Service: <strong>112 / +234 803 333 4444</strong>.</li>
                    <li class="flex items-start gap-2"><strong>3.</strong> Evacuate the building via the nearest designated emergency staircase. Do not use elevators.</li>
                    <li class="flex items-start gap-2"><strong>4.</strong> Stay low if encountering smoke to breathe cooler, clearer air.</li>
                </ul>
            </div>

            <div class="bg-emerald-50/50 p-6 rounded-2xl border border-emerald-100 space-y-3">
                <h4 class="font-bold text-emerald-900 flex items-center gap-2">
                    <i class="fa-solid fa-fire-extinguisher text-emerald-600"></i> Using an Extinguisher (P.A.S.S.):
                </h4>
                <ul class="space-y-2 text-slate-700">
                    <li class="flex items-start gap-2"><strong>P - Pull:</strong> Pull the pin at the top of the extinguisher to break the seal.</li>
                    <li class="flex items-start gap-2"><strong>A - Aim:</strong> Aim low, pointing the nozzle at the base of the fire, not the flames.</li>
                    <li class="flex items-start gap-2"><strong>S - Squeeze:</strong> Squeeze the handle lever slowly and evenly.</li>
                    <li class="flex items-start gap-2"><strong>S - Sweep:</strong> Sweep from side to side until the fire is completely extinguished.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 2. Medical Emergencies -->
    <div id="medical" class="scroll-mt-32 mb-16 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-kit-medical"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Medical Emergencies &amp; First Aid</h2>
                <p class="text-xs text-slate-500">Coordinated with the FPI Directorate of Medical Services &amp; Clinic</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs sm:text-sm">
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <h4 class="font-bold text-slate-900">Unconscious Patient</h4>
                <p class="text-xs text-slate-600">Check for responsiveness and pulse. Place patient in recovery position on their side to prevent airway blockage. Never force liquids into an unconscious person's mouth.</p>
            </div>
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <h4 class="font-bold text-slate-900">Severe Bleeding</h4>
                <p class="text-xs text-slate-600">Apply direct, firm pressure over the wound using a clean cloth or sterile gauze. Elevate the injured limb above heart level if no fracture is suspected.</p>
            </div>
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <h4 class="font-bold text-slate-900">Heat Stroke &amp; Fainting</h4>
                <p class="text-xs text-slate-600">Move the person to a cool, shaded area. Loosen tight clothing and fan them gently. Administer sips of clean water only once fully awake and oriented.</p>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-emerald-900 font-medium">
                <strong>Need an Ambulance?</strong> Campus Health Centre Ambulance responds 24/7 across all campus hostels and faculties.
            </div>
            <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs whitespace-nowrap transition flex items-center gap-2">
                <i class="fa-solid fa-truck-medical"></i> Call Ambulance: <?= CLINIC_HOTLINE ?>
            </a>
        </div>
    </div>

    <!-- 3. Security Threats -->
    <div id="security" class="scroll-mt-32 mb-16 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Security Threats &amp; Active Violence</h2>
                <p class="text-xs text-slate-500">Run, Hide, Tell protocol for dangerous physical altercations or armed intruders</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs sm:text-sm">
            <div class="p-6 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-600 text-white font-extrabold text-xs uppercase">Step 1: RUN</span>
                <p class="text-xs text-slate-700 leading-relaxed">
                    If there is an accessible path, attempt to evacuate the immediate danger zone. Leave your belongings behind. Encourage others to come with you, but do not let them slow your escape.
                </p>
            </div>
            <div class="p-6 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-600 text-white font-extrabold text-xs uppercase">Step 2: HIDE</span>
                <p class="text-xs text-slate-700 leading-relaxed">
                    If evacuation is impossible, lock and barricade the room door with heavy desks. Turn off lights, silence your mobile phones, and stay out of the intruder's line of sight behind solid walls.
                </p>
            </div>
            <div class="p-6 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2">
                <span class="inline-block px-2.5 py-1 rounded bg-indigo-600 text-white font-extrabold text-xs uppercase">Step 3: TELL</span>
                <p class="text-xs text-slate-700 leading-relaxed">
                    When safe to do so, submit an emergency report via CES or call the Campus Security Hotline: <strong><?= EMERGENCY_HOTLINE ?></strong>. State the location, number of attackers, and weapon details.
                </p>
            </div>
        </div>
    </div>

    <!-- 4. Theft Prevention -->
    <div id="theft" class="scroll-mt-32 mb-16 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-mask"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Theft Prevention &amp; Asset Protection</h2>
                <p class="text-xs text-slate-500">Safeguarding laptops, phones, bicycles, and personal property</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <h4 class="font-bold text-slate-900">Hostel Security Checklist:</h4>
                <ul class="space-y-2 text-slate-600">
                    <li>&bull; Always lock your room door and louvers, even when stepping out for 2 minutes.</li>
                    <li>&bull; Never leave high-value electronics charging unattended near open ground floor windows.</li>
                    <li>&bull; Mark your textbooks and laptops with identifiable permanent markings or engraving.</li>
                    <li>&bull; Report suspicious strangers lingering near hostel staircases to the hall porter immediately.</li>
                </ul>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <h4 class="font-bold text-slate-900">Library &amp; Classroom Precautions:</h4>
                <ul class="space-y-2 text-slate-600">
                    <li>&bull; Never ask unfamiliar persons to "watch" your laptop or mobile phone in the library.</li>
                    <li>&bull; Keep your backpack closed and within direct eyesight during lectures and laboratory sessions.</li>
                    <li>&bull; If theft occurs, note down serial numbers and immediately submit a theft report on CES.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 5. Emergency Evacuation -->
    <div id="evacuation" class="scroll-mt-32 mb-16 bg-white rounded-3xl p-8 border border-slate-200 shadow-sm space-y-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl font-bold">
                <i class="fa-solid fa-person-running"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Campus Evacuation &amp; Assembly Points</h2>
                <p class="text-xs text-slate-500">Safe gathering areas during major campus alerts or building dismissals</p>
            </div>
        </div>

        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
            When a building evacuation is sounded or a mass broadcast is received, proceed calmly to the nearest designated open field. Keep clear of building facades, overhead electrical wires, and vehicle access roads.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <strong class="block text-slate-900 font-bold mb-1">Zone A: Academic Core</strong>
                <p class="text-slate-500">Convocation Arena &amp; Central Lawn</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <strong class="block text-slate-900 font-bold mb-1">Zone B: Engineering Area</strong>
                <p class="text-slate-500">Sports Stadium &amp; Pavilion Grounds</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <strong class="block text-slate-900 font-bold mb-1">Zone C: Science &amp; DSA</strong>
                <p class="text-slate-500">Block B Car Park &amp; Open Quadrangle</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <strong class="block text-slate-900 font-bold mb-1">Zone D: Student Hostels</strong>
                <p class="text-slate-500">Hall 1 &amp; 3 Recreational Volleyball Courts</p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
