    </main>

    <!-- Public Footer -->
    <footer class="bg-fpi-900 text-slate-300 pt-16 pb-12 border-t border-emerald-950 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                
                <!-- Col 1: Institutional Identity -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex-shrink-0">
                            <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="FPI Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-base font-bold text-white tracking-tight">CAMPUS SAFETY</span>
                            <p class="text-xs text-amber-400 font-medium">Federal Polytechnic Ilaro</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Dedicated to safeguarding students, academic staff, and campus visitors across all schools and directorates with rapid emergency triage and immediate broadcast alerts.
                    </p>
                    <div class="flex items-center space-x-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800/80">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            24/7 Incident Dispatch Active
                        </span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-emerald-800/60 pb-2">Navigation</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="<?= BASE_URL ?>/index.php" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-emerald-500"></i> Home Page</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-emerald-500"></i> About CES Architecture</a></li>
                        <li><a href="<?= BASE_URL ?>/safety-guides.php" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-emerald-500"></i> Emergency Protocols & Guides</a></li>
                        <li><a href="<?= BASE_URL ?>/contacts.php" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-emerald-500"></i> Campus Emergency Directory</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-angle-right text-emerald-500"></i> Staff & Student Sign In</a></li>
                    </ul>
                </div>

                <!-- Col 3: Safety Categories -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-emerald-800/60 pb-2">Emergency Response</h4>
                    <ul class="space-y-2.5 text-xs text-slate-400">
                        <li><a href="<?= BASE_URL ?>/safety-guides.php#fire" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-fire text-rose-500"></i> Fire Evacuation & Drills</a></li>
                        <li><a href="<?= BASE_URL ?>/safety-guides.php#medical" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-kit-medical text-red-400"></i> Medical First Aid & Clinic</a></li>
                        <li><a href="<?= BASE_URL ?>/safety-guides.php#security" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-shield-halved text-indigo-400"></i> Security Threat Escalation</a></li>
                        <li><a href="<?= BASE_URL ?>/safety-guides.php#laboratory" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-flask-vial text-yellow-400"></i> Laboratory Safety Protocols</a></li>
                        <li><a href="<?= BASE_URL ?>/safety-guides.php#natural" class="hover:text-amber-400 transition flex items-center gap-2"><i class="fa-solid fa-cloud-bolt text-teal-400"></i> Storm & Weather Advisories</a></li>
                    </ul>
                </div>

                <!-- Col 4: Campus Hotlines -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-emerald-800/60 pb-2">Emergency Direct Lines</h4>
                    <div class="space-y-3 text-xs">
                        <div class="p-3 bg-emerald-950/60 rounded-xl border border-emerald-800/40">
                            <span class="text-slate-400 block text-[11px]">Security Operations Post:</span>
                            <a href="tel:<?= str_replace(' ', '', EMERGENCY_HOTLINE) ?>" class="text-sm font-extrabold text-amber-400 hover:text-amber-300 transition flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-phone"></i> <?= EMERGENCY_HOTLINE ?>
                            </a>
                        </div>
                        <div class="p-3 bg-emerald-950/60 rounded-xl border border-emerald-800/40">
                            <span class="text-slate-400 block text-[11px]">Health Centre Ambulance:</span>
                            <a href="tel:<?= str_replace(' ', '', CLINIC_HOTLINE) ?>" class="text-sm font-extrabold text-white hover:text-emerald-300 transition flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-truck-medical text-rose-400"></i> <?= CLINIC_HOTLINE ?>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Credits -->
            <div class="pt-8 border-t border-emerald-900/60 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>&copy; <?= date('Y') ?> Federal Polytechnic Ilaro, Ogun State, Nigeria. All Rights Reserved.</p>
                <div class="flex items-center space-x-6 text-[11px]">
                    <span class="text-slate-400">Campus Safety & Emergency Alert System (CES)</span>
                    <span class="text-slate-600">&bull;</span>
                    <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-shield-check mr-1"></i> Official Portal</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Custom Main JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
