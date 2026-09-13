<?php
/**
 * Federal Polytechnic Ilaro
 * Campus Safety & Emergency Alert System (CES)
 * About System Page
 */

$pageTitle = 'About the System';
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-gradient-to-b from-fpi-900 to-fpi-800 text-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 bg-emerald-950/80 px-3 py-1 rounded-full border border-emerald-700/60">
            Institutional Safeguard Initiative
        </span>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mt-3">
            About Campus Safety &amp; Emergency Alert System
        </h1>
        <p class="text-slate-300 max-w-2xl text-sm sm:text-base mt-2">
            Pioneering digital crisis communication and rapid emergency intervention at Federal Polytechnic Ilaro.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
    
    <!-- Mission & Objectives Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Empowering the Polytechnic Community with Instant Protection
            </h2>
            <p class="text-slate-600 text-sm leading-relaxed">
                The Federal Polytechnic Ilaro community spans thousands of students, lecturers, administrative staff, and visitors across various schools, laboratories, workshops, and student residential halls. Rapid notification during incidents is critical to preventing harm and protecting institutional assets.
            </p>
            <p class="text-slate-600 text-sm leading-relaxed">
                The <strong>Campus Safety &amp; Emergency Alert System (CES)</strong> was designed as a modern, reliable, and responsive web platform to eliminate communication delays between incident occurrence and emergency dispatch.
            </p>
            
            <div class="grid grid-cols-2 gap-4 pt-2">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-2xl font-black text-fpi-800">10+</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Monitored Hazard Types</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-2xl font-black text-amber-600">13+</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Campus Landmarks Mapped</div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 via-white to-slate-100 rounded-3xl p-8 border border-emerald-200 shadow-sm space-y-6">
            <h3 class="text-lg font-bold text-fpi-900 flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-amber-500"></i> Core Objectives
            </h3>
            <ul class="space-y-4 text-xs text-slate-700">
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-900 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">1</span>
                    <span><strong>Rapid Incident Triage:</strong> Enable students and staff to notify the Central Security Desk within 60 seconds of noticing an emergency.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-900 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">2</span>
                    <span><strong>Geographic Mapping:</strong> Pinpoint exact coordinates and campus landmarks on an interactive Leaflet map to guide response vehicles quickly.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-900 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">3</span>
                    <span><strong>Institutional Mass Broadcasts:</strong> Deliver authoritative warnings to protect the campus population against fires, weather disasters, or security lockdowns.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-900 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">4</span>
                    <span><strong>Accountability &amp; Analytics:</strong> Provide administrative incident tracking, transparent status timelines, and safety trend metrics for polytechnic management.</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Operating Principles -->
    <div class="bg-white rounded-3xl p-10 border border-slate-200 shadow-sm">
        <h3 class="text-2xl font-bold text-slate-900 text-center mb-10">Our Guiding Safety Pillars</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center sm:text-left">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900">Confidentiality &amp; Trust</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Reporters can report incidents with peace of mind. Student information is handled confidentially and reports can be submitted anonymously where necessary.
                </p>
            </div>

            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-stopwatch-20"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900">Swift Operational Response</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    The platform interfaces with frontline responders: the Central Security Unit, the Directorate of Medical Services, and Ogun State Fire Command.
                </p>
            </div>

            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h4 class="text-base font-bold text-slate-900">Precautionary Broadcasts</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Instead of rumors circulating on social media, official safety bulletins provide verified instructions directly to students and staff.
                </p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
