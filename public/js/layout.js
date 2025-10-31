var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Show loader on page unload (navigation or reload)
window.addEventListener("beforeunload", function () {
    const loader = document.getElementById("loader-display");
    if (loader) {
        loader.style.display = "flex";
    }
});

// Hide loader on page load
window.addEventListener("load", function () {
    const loader = document.getElementById("loader-display");
    if (loader) {
        loader.style.display = "none";
    }
});

// Hide loader on pageshow (including when coming back from bfcache)
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        const loader = document.getElementById("loader-display");
        if (loader) {
            loader.style.display = "none";
        }
    }
});

const toggleBtn = document.getElementById("toggleSidebar");
const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebar-overlay");

// Fungsi untuk menyimpan status sidebar ke localStorage
function saveSidebarState(isClosed) {
    localStorage.setItem("sidebarClosed", isClosed ? "true" : "false");
}

// Fungsi untuk membaca status sidebar dari localStorage dan mengatur tampilan
function loadSidebarState() {
    const isClosed = localStorage.getItem("sidebarClosed") === "true";
    if (window.innerWidth <= 768) {
        // On mobile, sidebar is hidden by default
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
        toggleBtn.classList.remove("toggled");
    } else {
        if (isClosed) {
            sidebar.classList.add("d-none");
            toggleBtn.classList.add("toggled");
        } else {
            sidebar.classList.remove("d-none");
            toggleBtn.classList.remove("toggled");
        }
    }
}

// Muat status sidebar saat halaman dimuat
loadSidebarState();

toggleBtn.addEventListener("click", function () {
    if (window.innerWidth <= 768) {
        // On mobile, toggle sidebar overlay
        sidebar.classList.toggle("show");
        sidebarOverlay.classList.toggle("show");
    } else {
        // Toggle kelas 'd-none' untuk menyembunyikan/memperlihatkan sidebar
        sidebar.classList.toggle("d-none");
        // Toggle class untuk pindahkan posisi tombol
        toggleBtn.classList.toggle("toggled");

        // Simpan status sidebar setelah toggle
        const isClosed = sidebar.classList.contains("d-none");
        saveSidebarState(isClosed);
    }
});

// Handle window resize to reset sidebar state
window.addEventListener("resize", function () {
    loadSidebarState();
});
