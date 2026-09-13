/**
 * Federal Polytechnic Ilaro - Campus Safety & Emergency Alert System
 * Main JavaScript Interactions & Leaflet Map Helpers
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // 2. Notifications Dropdown Toggle
    const notifBtn = document.getElementById('notif-btn');
    const notifDropdown = document.getElementById('notif-dropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
        });
    }

    // 3. User Menu Dropdown Toggle
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');
    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!userMenuDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });
    }

    // 4. Auto dismiss flash alerts after 7 seconds
    const flashAlerts = document.querySelectorAll('[role="alert"]');
    flashAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 7000);
    });

    // 5. Image Upload Preview
    const imageUploadInput = document.getElementById('report-image-input');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreviewImg = document.getElementById('image-preview-img');
    if (imageUploadInput && imagePreviewContainer && imagePreviewImg) {
        imageUploadInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreviewImg.src = e.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.classList.add('hidden');
            }
        });
    }
});

/**
 * Initialize Leaflet Map for Incident Monitoring
 */
function initIncidentMap(containerId, incidents, centerLat = 6.8928, centerLng = 3.0165, zoom = 16) {
    if (!document.getElementById(containerId) || typeof L === 'undefined') return null;

    const map = L.map(containerId).setView([centerLat, centerLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors | Federal Polytechnic Ilaro'
    }).addTo(map);

    const markers = [];

    const getPinClass = (severity) => {
        switch ((severity || '').toLowerCase()) {
            case 'critical': return 'pin-critical';
            case 'high':     return 'pin-high';
            case 'medium':   return 'pin-medium';
            default:         return 'pin-low';
        }
    };

    const getIconClass = (type) => {
        switch ((type || '').toLowerCase()) {
            case 'fire': return 'fa-fire';
            case 'medical emergency': return 'fa-kit-medical';
            case 'security threat': return 'fa-shield-halved';
            case 'accident': return 'fa-car-burst';
            case 'theft': return 'fa-mask';
            case 'violence': return 'fa-hand-fist';
            case 'gas leak': return 'fa-smog';
            default: return 'fa-triangle-exclamation';
        }
    };

    incidents.forEach(inc => {
        if (!inc.latitude || !inc.longitude) return;

        const pinClass = getPinClass(inc.severity);
        const iconClass = getIconClass(inc.emergency_type);

        const customIcon = L.divIcon({
            className: '',
            html: `<div class="custom-map-pin ${pinClass}"><i class="fa-solid ${iconClass}"></i></div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        const popupContent = `
            <div class="text-xs">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <span class="font-bold text-slate-800 text-sm">${inc.emergency_type}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${
                        inc.severity === 'Critical' ? 'bg-red-100 text-red-800' :
                        inc.severity === 'High' ? 'bg-orange-100 text-orange-800' :
                        inc.severity === 'Medium' ? 'bg-amber-100 text-amber-800' :
                        'bg-emerald-100 text-emerald-800'
                    }">${inc.severity}</span>
                </div>
                <p class="text-slate-600 mb-1"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i>${inc.location_name}</p>
                <div class="flex items-center justify-between text-slate-500 border-t pt-1 mt-1 text-[11px]">
                    <span>Status: <strong>${inc.status}</strong></span>
                    <span>Ref: ${inc.report_reference}</span>
                </div>
                ${inc.view_url ? `<a href="${inc.view_url}" class="mt-2 block text-center bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-1 px-2 rounded text-xs transition">View Details</a>` : ''}
            </div>
        `;

        const marker = L.marker([inc.latitude, inc.longitude], { icon: customIcon })
            .bindPopup(popupContent);
        
        marker.severity = inc.severity;
        marker.status = inc.status;
        marker.addTo(map);
        markers.push(marker);
    });

    return { map, markers };
}

/**
 * Initialize Interactive Location Picker Map
 */
function initLocationPickerMap(containerId, latInputId, lngInputId, centerLat = 6.8928, centerLng = 3.0165) {
    if (!document.getElementById(containerId) || typeof L === 'undefined') return null;

    const latInput = document.getElementById(latInputId);
    const lngInput = document.getElementById(lngInputId);

    const initialLat = latInput && latInput.value ? parseFloat(latInput.value) : centerLat;
    const initialLng = lngInput && lngInput.value ? parseFloat(lngInput.value) : centerLng;

    const map = L.map(containerId).setView([initialLat, initialLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors | Federal Polytechnic Ilaro'
    }).addTo(map);

    const customPin = L.divIcon({
        className: '',
        html: `<div class="custom-map-pin pin-critical"><i class="fa-solid fa-location-crosshairs"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    const marker = L.marker([initialLat, initialLng], {
        draggable: true,
        icon: customPin
    }).addTo(map);

    const updateInputs = (lat, lng) => {
        if (latInput) latInput.value = lat.toFixed(7);
        if (lngInput) lngInput.value = lng.toFixed(7);
    };

    updateInputs(initialLat, initialLng);

    marker.on('dragend', function (e) {
        const coord = e.target.getLatLng();
        updateInputs(coord.lat, coord.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    return { map, marker };
}
