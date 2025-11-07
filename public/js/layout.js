// === Bootstrap tooltip ===
var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// === Loader handling ===
window.addEventListener("beforeunload", function () {
    const loader = document.getElementById("loader-display");
    if (loader) loader.style.display = "flex";
});

window.addEventListener("load", function () {
    const loader = document.getElementById("loader-display");
    if (loader) loader.style.display = "none";
});

window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        const loader = document.getElementById("loader-display");
        if (loader) loader.style.display = "none";
    }
});

// === Sidebar logic (hanya jalan kalau elemen ada) ===
const toggleBtn = document.getElementById("toggleSidebar");
const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebar-overlay");

// Jalankan hanya kalau elemen sidebar memang ada
if (sidebar && toggleBtn) {
    function saveSidebarState(isClosed) {
        localStorage.setItem("sidebarClosed", isClosed ? "true" : "false");
    }

    function loadSidebarState() {
        const isClosed = localStorage.getItem("sidebarClosed") === "true";
        if (window.innerWidth <= 768) {
            sidebar.classList.remove("show");
            if (sidebarOverlay) sidebarOverlay.classList.remove("show");
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
            sidebar.classList.toggle("show");
            if (sidebarOverlay) sidebarOverlay.classList.toggle("show");
        } else {
            sidebar.classList.toggle("d-none");
            toggleBtn.classList.toggle("toggled");

            const isClosed = sidebar.classList.contains("d-none");
            saveSidebarState(isClosed);
        }
    });

    window.addEventListener("resize", function () {
        loadSidebarState();
    });
}
