@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Daftar Produk')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/item-index.css') }}">
@endpush

@section('content')
<div class="px-4 py-4">
    @include('items.partials.filter')

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="fw-bold text-white text-shadow-sm mb-2 mb-md-0">
            <i class="bi bi-box-seam me-2"></i> Daftar Produk
        </h4>

        <div class="d-flex gap-2">
            {{-- Tambahan tombol lain bisa di sini kalau mau (misal export Excel) --}}
            <div class="d-flex align-items-center">
                <p class="mb-0 text-white">item perpage : </p>
            </div>
            <select id="per_page" class="form-select form-select-sm" style="width: 80px;">
                <option value="10" selected>10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <a href="#" id="btn-export-pdf" class="btn btn-danger shadow-sm" data-export-url="{{ route('items.exportPdf') }}">
                <i class="bi bi-filetype-pdf me-1"></i> Preview PDF
            </a>
        </div>
    </div>

    <!-- 🔹 Kontainer hasil produk -->
    <div id="item-container">
        @include('items.partials.item-table', ['items' => $items])
    </div>

    <!-- 🔹 Kontainer pagination -->
    <div id="pagination-container" class="mt-3 d-flex justify-content-center">
        @include('items.partials.pagination', [
            'page' => $page,
            'pageCount' => $pageCount,
            'queryParams' => request()->except('page')
        ])
    </div>
</div>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000">
    <div id="toastFilterError" class="toast align-items-center text-bg-warning border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold">
                Filter harga terlalu sempit.<br>Perbesar rentang harganya.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemContainer        = document.getElementById("item-container");
    const paginationContainer  = document.getElementById("pagination-container");
    const filterForm           = document.getElementById("filter-form");
    const headerTotalBtn       = document.querySelector("#header-total-items");
    const categorySelect       = document.getElementById("category_search");

    // ========= DETEKSI USER FORCE DARI URL =========
    const urlParams = new URL(window.location.href).searchParams;
    if (urlParams.get("force") === "1") {
        // User ini datang dari wait-page / override
        localStorage.setItem("force_item_queue", "1");
    }

    // Helper: apakah user ini termasuk "user antrian" (force mode)?
    function isForceUser() {
        return localStorage.getItem("force_item_queue") === "1";
    }

    // Helper: apply force=1 ke URL kalau user antrian
    function applyForceToUrl(rawUrl) {
        const u = new URL(rawUrl, window.location.origin);

        if (isForceUser()) {
            u.searchParams.set("force", "1");
        }

        return u;
    }

    const perPageSelect = document.getElementById("per_page");
    if (perPageSelect) {
        perPageSelect.addEventListener("change", function () {
            submitFilterAjax();
        });
    }

    let currentPage = parseInt(
        new URL(window.location.href).searchParams.get("page") || "1",
        10
    );
    let isLoading = false; // cegah spam klik

    // ==============================
    // 1. INIT TOM-SELECT (sekali saja)
    // ==============================
    if (categorySelect && !categorySelect.dataset.tsInit) {
        categorySelect.dataset.tsInit = "1";

        new TomSelect("#category_search", {
            valueField: "id",
            create: false,
            labelField: "text",
            plugins: ["remove_button"],
            searchField: "text",
            maxOptions: 9999,
            allowEmptyOption: false,
            onChange(value) {
                // update hidden input + submit filter pakai AJAX
                const hidden = document.getElementById("itemCategoryId");
                if (hidden) hidden.value = value || "";
                submitFilterAjax();
            },
        });
    }

    // =================================================
    // 2. FUNGSI SHOW/HIDE LOADER RINGAN (tanpa Lottie)
    // =================================================
    function showInlineLoader() {
        if (!itemContainer) return;

        let overlay = itemContainer.querySelector(".loading-overlay");
        if (!overlay) {
            overlay = document.createElement("div");
            overlay.className =
                "loading-overlay d-flex flex-column justify-content-center align-items-center";
            overlay.innerHTML = `
                <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;"></div>
            `;
            itemContainer.appendChild(overlay);
        }
        overlay.style.display = "flex";
    }

    function hideInlineLoader() {
        if (!itemContainer) return;
        const overlay = itemContainer.querySelector(".loading-overlay");
        if (overlay) overlay.style.display = "none";
    }

    // ===========================================
    // 3. FUNGSI UTAMA LOAD PAGE VIA AJAX (ANTI LAG)
    // ===========================================
    async function loadPage(url, options = {}) {
        if (!itemContainer || !paginationContainer) return;
        if (isLoading) return;          // cegah double request
        isLoading = true;
        showInlineLoader();

        try {
            // pastikan URL absolut + injek force=1 kalau perlu
            const urlObj = applyForceToUrl(url);
            currentPage = parseInt(urlObj.searchParams.get("page") || "1", 10);

            // // simpan URL terakhir untuk dipakai lagi
            // localStorage.setItem("last_item_list_url", urlObj.toString());
            // sessionStorage.setItem("last_item_list_url", urlObj.toString());

            const response = await fetch(urlObj.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (!response.ok) {
                // 🔍 Deteksi apakah ini error dari FILTER HARGA
                const urlTest = new URL(url, window.location.origin);
                const minP = urlTest.searchParams.get("min_price");
                const maxP = urlTest.searchParams.get("max_price");

                // 🔥 Jika filter harga aktif dan server error → notif khusus
                if ((minP || maxP) && response.status === 500) {
                    hideInlineLoader();
                    isLoading = false;

                    const overlays = itemContainer.querySelectorAll(".loading-overlay");
                    overlays.forEach(o => o.remove());

                    showFilterToast();

                    return; // STOP proses disini
                }

                // Error AJAX biasa → tetap tangani dengan cara default
                throw new Error("HTTP " + response.status);
            }

            const html = await response.text();

            // pakai wrapper <div> biasa (lebih ringan dari DOMParser)
            const wrapper = document.createElement("div");
            wrapper.innerHTML = html;

            const newItemContainer = wrapper.querySelector("#item-container");
            const newPagination    = wrapper.querySelector("#pagination-container");

            if (newItemContainer) {
                // ganti isi tanpa bikin elemen baru di luar
                itemContainer.innerHTML = newItemContainer.innerHTML;
                reloadPricesAfterAjax();
            }
            if (newPagination) {
                paginationContainer.innerHTML = newPagination.innerHTML;
                reloadPricesAfterAjax();
            }

            if (!options.skipHistory) {
                history.pushState(
                    { url: urlObj.toString() },
                    "",
                    urlObj.toString()
                );
            }

            // update total item di header (pakai data-total / data-original)
            const totalElement = wrapper.querySelector("[data-total]");
            if (totalElement && headerTotalBtn) {
                const originalCount =
                    totalElement.dataset.original ||
                    totalElement.dataset.total ||
                    "0";

                headerTotalBtn.textContent = Number(
                    originalCount
                ).toLocaleString("id-ID");
            }

            // scroll ke atas sedikit biar user tidak bingung
            if (!options.noScroll) {
                window.scrollTo({ top: 0, behavior: "instant" });
            }
        } catch (err) {
            console.error(err);
            const overlays = itemContainer.querySelectorAll(".loading-overlay");
            overlays.forEach(o => o.remove());
            // tampilkan pesan error ringan di bawah loader
            if (itemContainer) {
                let errorBox = itemContainer.querySelector(".load-error-message");
                if (!errorBox) {
                    errorBox = document.createElement("div");
                    errorBox.className = "load-error-message text-danger text-center mt-2";
                    itemContainer.appendChild(errorBox);
                }
                errorBox.textContent = "Gagal memuat data. Coba lagi.";
            }
        } finally {
            hideInlineLoader();
            isLoading = false;
        }
    }

    // ==================================
    // 4. SUBMIT FILTER DENGAN AJAX
    // ==================================
    function buildFilterUrl() {
        const params = new URLSearchParams(filterForm ? new FormData(filterForm) : {});
    
        // >>> Inject manual per_page because select is outside form
        const perPageSelect = document.getElementById("per_page");
        if (perPageSelect) {
            params.set("per_page", perPageSelect.value);
        }

        // force=1 logic tetap
        if (isForceUser()) {
            params.set("force", "1");
        }

        return "{{ route('items.index') }}?" + params.toString();
    }

    function submitFilterAjax() {
        const url = buildFilterUrl();
        loadPage(url);
    }

    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            submitFilterAjax();
        });
    }

    // ==================================
    // 5. EKSPORT PDF (IKUT FILTER + PAGE)
    // ==================================
    document.addEventListener("click", function (e) {
        const btn = e.target.closest("#btn-export-pdf");
        if (!btn) return;

        e.preventDefault();
        if (!filterForm) {
            window.open(btn.dataset.exportUrl, "_blank");
            return;
        }

        const params = new URLSearchParams(new FormData(filterForm));
        // >>> PERBAIKAN UTAMA <<<
        const perPageSelect = document.getElementById("per_page");
        if (perPageSelect) {
            params.set("per_page", perPageSelect.value);
        }

        params.set("page", currentPage.toString());

        const pdfUrl = `${btn.dataset.exportUrl}?${params.toString()}`;
        window.open(pdfUrl, "_blank");
    });

    // ==================================
    // 6. PAGINATION KLIK AJAX (EVENT DELEGATION)
    // ==================================
    document.addEventListener("click", function (e) {
        const link = e.target.closest(".page-link-ajax");
        if (!link) return;
        e.preventDefault();

        // loadPage sudah inject force=1 lewat applyForceToUrl,
        // jadi cukup kirim href apa adanya
        loadPage(link.href);
    });

    // ==================================
    // 7. SIMPAN URL AWAL (FIRST LOAD)
    // ==================================
    const currentUrl = window.location.href;
    if (!localStorage.getItem("last_item_list_url")) {
        localStorage.setItem("last_item_list_url", currentUrl);
    }
    if (!sessionStorage.getItem("last_item_list_url")) {
        sessionStorage.setItem("last_item_list_url", currentUrl);
    }

    window.addEventListener("popstate", function (e) {
        if (e.state && e.state.url) {
            loadPage(e.state.url, {
                skipHistory: true,
                noScroll: true
            });
        }
    });
});

// Format Rupiah (tetap sama, cuma dirapikan dikit)
function formatRupiahFilter(el) {
    if (!el || !el.value) return;
    const value = el.value.replace(/\D/g, "");
    el.value = new Intl.NumberFormat("id-ID").format(value);
}

// =========================================================
// ===============  GLOBAL LAZY PRICE ENGINE ===============
// =========================================================

// Format angka Rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat("id-ID").format(angka);
}

// Ambil ulang semua item harga setelah pagination / filtering
function collectLazyPrices() {
    const map = {};        // untuk menghilangkan duplikat per product ID

    document.querySelectorAll("[data-lazy-price]").forEach(el => {
        const id   = el.dataset.id;
        const mode = el.dataset.mode;

        if (!id) return;

        // kalau id belum pernah disimpan, baru masukin
        if (!map[id]) {
            map[id] = { id, mode };
        }
    });

    // hasil akhir: 1 item Accurate = 1 entry
    window.lazyPrices = Object.values(map);
}

// Loader harga
async function loadPrices() {
    if (!window.lazyPrices || window.lazyPrices.length === 0) return;

    const forceMode = localStorage.getItem("force_item_queue") === "1";

    for (let item of window.lazyPrices) {

        let targets = document.querySelectorAll(
            `[data-id="${item.id}"][data-lazy-price]`
        );

        if (targets.length === 0) continue;

        // kasih efek loading
        targets.forEach(t => t.innerHTML = "…");

        try {
            let url = `/ajax/price?id=${item.id}&mode=${item.mode}`;
            if (forceMode) {
                url += "&force=1";
            }

            let res = await fetch(url);
            let data = await res.json();
            let price = new Intl.NumberFormat("id-ID").format(data.price ?? 0);

            targets.forEach(t => t.innerHTML = price);

        } catch (e) {
            targets.forEach(t => t.innerHTML = "0");
        }

        await new Promise(r => setTimeout(r, 150));
    }
}

// =========================================================
// ====  PENTING!! CALL ENGINE SETIAP KALI DOM DIGANTI  ====
// =========================================================

// Call untuk first load
document.addEventListener("DOMContentLoaded", () => {
    collectLazyPrices();
    loadPrices();
});

// Fungsi bantu agar AJAX pagination/filter ikut reload harga
function reloadPricesAfterAjax() {
    collectLazyPrices();
    loadPrices();
}

function showFilterToast() {
    const toastEl = document.getElementById("toastFilterError");
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}
</script>

@endpush

