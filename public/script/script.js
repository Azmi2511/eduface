// Dropdown Notifikasi
function toggleNotification() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('hidden');
}

// Tutup notifikasi jika klik di luar
window.addEventListener('click', function (e) {
    const btn = document.getElementById('notificationBtn');
    const dropdown = document.getElementById('notificationDropdown');

    if (btn && dropdown) {
        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            if (!dropdown.classList.contains('hidden')) {
                dropdown.classList.add('hidden');
            }
        }
    }
});

// LOGIKA SIDEBAR BARU
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
const sidebarToggle = document.getElementById('sidebar-toggle');

function openSidebar() {
    // Hapus translate agar sidebar masuk ke layar
    sidebar.classList.remove('-translate-x-full');
    
    // Tampilkan overlay
    overlay.classList.remove('opacity-0', 'pointer-events-none');
}

function closeSidebar() {
    // Kembalikan sidebar ke luar layar
    sidebar.classList.add('-translate-x-full');
    
    // Sembunyikan overlay
    overlay.classList.add('opacity-0', 'pointer-events-none');
}

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Mencegah event bubbling
        // Cek apakah sidebar sedang tertutup (memiliki class -translate-x-full)
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        closeSidebar();
    });
}