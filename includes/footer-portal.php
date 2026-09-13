            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <script>
    function togglePortalSidebar() {
        const sidebar = document.getElementById('portal-sidebar');
        const backdrop = document.getElementById('portal-mobile-backdrop');
        if (sidebar && backdrop) {
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    }
    </script>
</body>
</html>
