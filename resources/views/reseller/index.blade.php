@extends('layouts.app')

@section('title', 'Daftar Produk')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/item-index.css') }}">
<style>
    /* ======== DESKTOP NORMAL ======== */
    @media (min-width: 768px) {
        .mobile-list {
            display: none;
        }
    }

    /* ======== PERBAIKAN UNTUK MOBILE ======== */
    @media (max-width: 767.98px) {
        .page-wrapper {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
    }

    /* Styling tambahan biar kontras dengan background */
    .filter-card {
        background: rgba(0, 0, 0, 0.55);
        border-radius: 10px;
        padding: 20px;
    }

    label, h3, p, .form-label {
        color: #fff;
        text-shadow: 0 0 4px rgba(0,0,0,0.6);
    }

    select, input {
        background: rgba(255, 255, 255, 0.95) !important;
    }

    .badge.bg-info {
        background-color: #0dcaf0 !important;
    }

    
</style>
@endpush

@section('content')
<div class="px-4 py-4">
    <!-- 🔍 Filter -->
    <form method="GET" class="mb-4 filter-card shadow" id="filter-form">
        <div class="row g-2 justify-content-start">
            <div class="col-12 col-md-3 col-lg-2">
                <label for="stok_ada" class="form-label mb-1">Stok Ready</label>
                <select name="stok_ada" class="form-select shadow-sm">
                    <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            
            <!-- 🔸 Harga Minimum -->
            <div class="col-6 col-md-3 col-lg-2 price-input">
                <label for="min_price" class="form-label mb-1">Min Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="min_price" id="min_price"
                    class="form-control shadow-sm"
                    value="{{ request('min_price') }}" min="0"
                    placeholder="0" oninput="formatRupiahFilter(this)">
                </div>
            </div>
            
            <!-- 🔸 Harga Maksimum -->
            <div class="col-6 col-md-3 col-lg-2 price-input">
                <label for="max_price" class="form-label mb-1">Max Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="max_price" id="max_price"
                    class="form-control shadow-sm"
                    value="{{ request('max_price') }}" min="0"
                    placeholder="0" oninput="formatRupiahFilter(this)">
                </div>
            </div>
            
            <!-- 🔸 Kategori -->
            <div class="col-12 col-md-3 col-lg-2">
                <label for="category" class="form-label mb-1">Pilih kategori</label>
                <select name="category_id" id="category_search" class="form-select shadow-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['id'] }}" {{ request('category_id') == $cat['id'] ? 'selected' : '' }}>
                            {{ $cat['name'] }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="category_id" id="itemCategoryId" value="{{ request('category_id') }}">
            </div>

            <!-- 🔸 Pencarian -->
            <div class="col-12 col-md-6 col-lg-3">
                <label for="search" class="form-label mb-1">Gunakan % untuk kombinasi kata pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control shadow-sm" placeholder="Kode / Nama barang">
            </div>

            <!-- 🔸 Tombol -->
            <div class="col-12 col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('reseller.index') }}" class="btn btn-secondary w-100 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h4 class="fw-bold text-white text-shadow-sm mb-2 mb-md-0">
            <i class="bi bi-box-seam me-2"></i> Daftar Produk
        </h4>
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
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const itemContainer        = document.getElementById("item-container");
    const paginationContainer  = document.getElementById("pagination-container");
    const filterForm           = document.getElementById("filter-form");
    const headerTotalBtn       = document.querySelector("#header-total-items");
    const categorySelect       = document.getElementById("category_search");

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
            placeholder: "Semua kategori",
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
            const urlObj = new URL(url, window.location.origin);
            currentPage = parseInt(urlObj.searchParams.get("page") || "1", 10);

            // simpan URL terakhir untuk dipakai lagi
            localStorage.setItem("last_item_list_url", urlObj.toString());
            sessionStorage.setItem("last_item_list_url", urlObj.toString());

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
        if (!filterForm) return "{{ route('reseller.index') }}";

        const params = new URLSearchParams(new FormData(filterForm));

        return "{{ route('reseller.index') }}?" + params.toString();
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

    for (let item of window.lazyPrices) {

        let targets = document.querySelectorAll(
            `[data-id="${item.id}"][data-lazy-price]`
        );

        if (targets.length === 0) continue;

        // kasih efek loading
        targets.forEach(t => t.innerHTML = "…");

        try {
            let url = `/ajax/priceReseller?id=${item.id}&mode=RESELLER`;
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
