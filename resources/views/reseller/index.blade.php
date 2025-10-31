@extends('layout')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/item-index.css') }}">
@endpush

<style>
    /* ======== DESKTOP NORMAL ======== */
    @media (min-width: 768px) {
        .mobile-list {
            display: none;
        }
    }
</style>

<div class="container py-4">
    <h3 class="text-center mb-4 fw-bold">
        <i class="bi bi-box-seam me-2"></i> Daftar Produk
    </h3>
    
    <!-- 🔍 Filter Pencarian & Kategori -->
    <form method="GET" class="mb-4">
        <div class="row g-2 justify-content-center">
            <div class="col-12 col-md-2">
                <label for="stok_ada" class="form-label mb-1">Stok Ready</label>
                <select name="stok_ada" class="form-select shadow-sm">
                    <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>
                        Ya
                    </option>
                    <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>
                        Tidak
                    </option>
                </select>
            </div>
            
            <!-- 🔸 Kategori -->
            <div class="col-12 col-md-2">
                <label for="category" class="form-label mb-1">Pilih kategori</label>
                <select name="category_id" id="category_search" class="form-select shadow-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['id'] }}" 
                            {{ request('category_id') == $cat['id'] ? 'selected' : '' }}>
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
                <a href="{{ route('reseller.index') }}" class="btn btn-secondary w-100 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- 📦 Daftar Produk -->
    <div class="desktop-table">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Kode</th>
                                <th>Nama Produk</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center" style="width: 10%">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr onclick="window.location='{{ route('reseller.detail', ['encrypted' => Hashids::encode($item['id'])]) }}'" style="cursor: pointer;">
                                    <td class="text-center" style="padding: 12px;"><span>{{ $item['no'] ?? '-' }}</span></td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $item['name'] }}
                                        </div>
                                        @if(!empty($item['itemCategory']['name']))
                                            <small class="text-muted">{{ $item['itemCategory']['name'] }}</small>
                                        @endif
                                    </td>
                                    <td class="td-harga">
                                        <div class="harga-grid">
                                            <span class="harga-rp">Rp</span>
                                            <span class="harga-nominal">
                                            {{ is_numeric($item['price'] ?? null) ? number_format($item['price'], 0, ',', '.') : '-' }}
                                            </span>
                                        </div>
                                        {{-- Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }} --}}
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ ($item['availableToSell'] ?? 0) > 0}}">
                                            {{ $item['availableToSell'] ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                        @endphp
                                        <span>{{ $unit }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                                        Tidak ada produk ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 📱 MOBILE: tampilan rapi dan kecil -->
    <div class="mobile-list">
        <div class="row g-2">
            @forelse($items as $item)
                <div class="col-12">
                    <div class="product-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="product-title">{{ $item['name'] }}</div>
                            <div class="harga-grid">
                                <span class="harga-rp">Rp</span>
                                <span class="harga-nominal">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @if(!empty($item['itemCategory']['name']))
                            <div class="product-meta mt-1">{{ $item['itemCategory']['name'] }}</div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="product-meta">
                                Stok: <strong>{{ $item['availableToSell'] ?? 0 }}</strong> / 
                                <strong>
                                    @php
                                        $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                    @endphp
                                    {{ $unit }}
                                </strong>
                            </div>
                            <a href="{{ route('reseller.detail', ['encrypted' => Hashids::encode($item['id'])]) }}" class="btn btn-success btn-sm btn-detail">Detail</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                        Tidak ada produk ditemukan.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 🔁 Pagination --}}
    @php
        $queryParams = request()->except('page');
        $queryString = http_build_query($queryParams);
    @endphp
    
    <div class="mt-4 d-flex justify-content-center">
        @if($page > 1)
            <a href="?page={{ $page - 1 }}&{{ $queryString }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Sebelumnya
            </a>
        @endif
        
        <span class="align-self-center mx-2 text-muted">    
            Halaman {{ $page }} dari {{ $pageCount }}
        </span>

        @if($page < $pageCount) 
            <a href="?page={{ $page + 1 }}&{{ $queryString }}" class="btn btn-outline-secondary">
                Berikutnya <i class="bi bi-arrow-right"></i>
            </a>
        @endif
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const catEl = document.getElementById('category_search');
        if (!catEl) return;
        if (catEl.dataset.tsInit === '1') return; // anti double init
        catEl.dataset.tsInit = '1';

        const selectedCategoryId = catEl.value; // aman: cuma lokal di callback
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
                console.log('Kategori dipilih:', value);
                // Set value ke hidden input agar bisa dikirim ke backend
                document.getElementById('itemCategoryId').value = value;
                performSearch(); // fungsi pencarian milikmu
            }
        });
    });

    function onlyNumber(el) {
        el.value = el.value.replace(/\D/g, ''); // cuma angka
    }

    function formatRupiah(el) {
    if (!el.value) return;
        const value = el.value.replace(/\D/g, '');
        el.value = new Intl.NumberFormat('id-ID').format(value);
    }

</script>
@endsection