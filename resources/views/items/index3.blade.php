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
        <div class="row g-2 justify-content-center">
            <div class="col-12 col-md-1">
                <label for="stok_ada" class="form-label mb-1">Stok Ready</label>
                <select name="stok_ada" class="form-select shadow-sm">
                    <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>

            <!-- 🔸 Jenis Harga -->
            <div class="col-12 col-md-1">
                <label for="price_mode" class="form-label mb-1">Jenis Harga</label>
                <select name="price_mode" class="form-select shadow-sm">
                    <option value="default" {{ request('price_mode', 'default') == 'default' ? 'selected' : '' }}>User</option>
                    <option value="reseller" {{ request('price_mode') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                </select>
            </div>

            <!-- 🔸 Kategori -->
            <div class="col-12 col-md-2">
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

            <!-- 🔸 Harga Minimum -->
            <div class="col-6 col-md-2 price-input">
                <label for="min_price" class="form-label mb-1">Min Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="min_price" id="min_price"
                        class="form-control shadow-sm"
                        value="{{ request('min_price') }}" min="0"
                        placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>

            <!-- 🔸 Harga Maksimum -->
            <div class="col-6 col-md-2 price-input">
                <label for="max_price" class="form-label mb-1">Max Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="max_price" id="max_price"
                        class="form-control shadow-sm"
                        value="{{ request('max_price') }}" min="0"
                        placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>

            <!-- 🔸 Pencarian -->
            <div class="col-12 col-md-3">
                <label for="search" class="form-label mb-1">Gunakan % untuk kombinasi kata pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control shadow-sm" placeholder="Kode / Nama barang">
            </div>

            <!-- 🔸 Tombol -->
            <div class="col-12 col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary w-100 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>

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

    {{-- 🔹 Tombol aksi gaya Accurate --}}
    <div class="action-sidebar">
        <button id="btnExportPdf" class="btn btn-danger shadow d-flex flex-column align-items-center mb-2">
            <i class="bi bi-filetype-pdf fs-4"></i>
        </button>

        <button class="btn btn-success shadow d-flex flex-column align-items-center mb-2">
            <i class="bi bi-file-earmark-excel fs-4"></i>
        </button>

        <button class="btn btn-primary shadow d-flex flex-column align-items-center">
            <i class="bi bi-printer fs-4"></i>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const catEl = document.getElementById('category_search');
    if (catEl && !catEl.dataset.tsInit) {
        catEl.dataset.tsInit = '1';
        new TomSelect("#category_search", {
            valueField: 'id',
            create: false,
            labelField: 'text',
            plugins: ['remove_button'],
            searchField: 'text',
            maxOptions: 9999,
            placeholder: 'Semua kategori',
            allowEmptyOption: false,
            onChange: function(value) {
                document.getElementById('itemCategoryId').value = value;
                performSearch();
            }
        });
    }

    // Fungsi AJAX pagination
    async function loadPage(url) {
        const itemContainer = document.getElementById('item-container');
        const paginationContainer = document.getElementById('pagination-container');

        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay d-flex flex-column justify-content-center align-items-center';
        overlay.innerHTML = `
            <div class="loader-overlay text-center py-4">
                <dotlottie-wc src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
                    style="width:100%;max-width:250px;height:auto;margin:auto;" autoplay loop></dotlottie-wc>
                <p style="color:white;text-shadow:2px 2px 6px rgba(0,0,0,0.8);margin-top:10px">Mohon tunggu...</p>
            </div>`;
        itemContainer.appendChild(overlay);

        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            itemContainer.innerHTML = doc.querySelector('#item-container').innerHTML;
            paginationContainer.innerHTML = doc.querySelector('#pagination-container').innerHTML;

            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            console.error(err);
            overlay.innerHTML = `<div class="text-danger mt-3">Gagal memuat data.</div>`;
        } finally {
            overlay.remove();
        }
    }

    // Klik pagination
    document.addEventListener('click', function (e) {
        const link = e.target.closest('.page-link-ajax');
        if (!link) return;
        e.preventDefault();
        loadPage(link.href);
    });

    // Filter form submit (tanpa reload)
    document.getElementById('filter-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const url = "{{ route('items.index') }}?" + new URLSearchParams(new FormData(this)).toString();
        loadPage(url);
    });
});

function formatRupiah(el) {
    if (!el.value) return;
    const value = el.value.replace(/\D/g, '');
    el.value = new Intl.NumberFormat('id-ID').format(value);
}

document.getElementById('btnExportPdf').addEventListener('click', async () => {
    const btn = document.getElementById('btnExportPdf');
    btn.disabled = true;
    const original = btn.innerHTML;
    btn.innerHTML = `<div class="spinner-border spinner-border-sm text-white"></div>`;

    try {
        // 🔹 ambil semua parameter filter dari URL
        const currentUrl = new URL(window.location.href);
        const query = currentUrl.search; // ?stok_ada=1&price_mode=reseller&...
        const exportUrl = "{{ route('items.export.pdf') }}" + query;

        // 🔹 download PDF
        const res = await fetch(exportUrl);
        if (!res.ok) throw new Error("Gagal");
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = "daftar-produk.pdf";
        a.click();
        URL.revokeObjectURL(url);
    } catch (e) {
        console.error(e);
        alert('Gagal membuat PDF.');
    } finally {
        btn.innerHTML = original;
        btn.disabled = false;
    }
});
</script>
@endpush
