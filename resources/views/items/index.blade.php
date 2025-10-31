@extends('layout')

@section('content')
@if($status == 'admin')
    <style>
        .card-full {
            height: 90vh;
        }
    </style>
@else
    <style>
        .card-full {
            height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
        }
    </style>
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('css/item-users.css') }}">
@endpush

<div class="container-fluid px-0">
    <div class="row g-0 row-wrap">
        <div class="col-12">
            <div class="{{$status === 'admin' ? 'card mb-4 card-full border-0 rounded' : 'card mb-4 card-full border-0 rounded-0'}}">
                <div class="{{$status === 'admin' ? 'card-header p-3 rounded' : 'card-header p-3 shadow-sm border-0'}}">
                    <div class="row align-items-center">
                        <h3>DAFTAR ITEM</h3>                      
                        <form action="{{ route('items.index') }}" method="GET" id="filterForm">                            
                            <div class="row g-3 mt-2">
                                <div class="col-6 col-md-2">
                                    <label for="stok_ada" class="form-label">Stok Ready</label>
                                    <select class="form-select shadow-sm" name="stok_ada" id="stok_ada" style="border-radius: 20px;">
                                        <option value="1" {{ request('stok_ada') == '1' ? 'selected' : '' }}>Ya</option>
                                        <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="category_search" class="form-label">Cari Kategori </label>
                                    <select id="category_search" placeholder="Pilih / Cari Kategori">
                                        <option></option>
                                        @foreach ($allCategoriesForTomSelect as $cat)
                                            <option value="{{ $cat['id'] }}" {{ request('category_id') == $cat['id'] ? 'selected' : '' }}>
                                                {{ $cat['text'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="category_id" id="itemCategoryId" value="{{ request('category_id') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="min_price" class="form-label">Harga Minimum</label>
                                    <div class="input-group" style="border-radius: 20px;">                                            
                                        <span class="input-group-text shadow-sm" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">Rp</span>
                                        <input type="text" name="min_price" id="min_price" class="form-control shadow-sm" value="{{ old('min_price', request('min_price', $min_price ?? '')) }}" min="0" placeholder="Min Harga" style="border-start-end-radius: 20px; border-end-end-radius: 20px;">
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="max_price" class="form-label">Harga Maksimum</label>
                                    <div class="input-group" style="border-radius: 20px;"> 
                                        <span class="input-group-text shadow-sm" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">Rp</span>
                                        <input type="text" name="max_price" id="max_price" class="form-control shadow-sm" value="{{ old('max_price', request('max_price', $max_price ?? '')) }}" min="0" placeholder="Max Harga" style="border-start-end-radius: 20px; border-end-end-radius: 20px;">
                                    </div>
                                </div>                                    
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <!-- Input Pencarian -->
                                        <div style="min-width: 250px; flex-grow: 1;">
                                            <label for="search" class="form-label">
                                                Gunakan % untuk kombinasi kata pencarian.
                                            </label>
                                            <div class="input-group" style="border-radius: 20px;">
                                                <input type="text" name="search" id="search" class="form-control shadow-sm" placeholder="Kode / Nama Barang" value="{{ request('search') }}" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">
                                                <button class="btn btn-light shadow-sm" id="btnSearch" style="border-start-end-radius: 20px; border-end-end-radius: 20px; border: 1px solid #ced4da;" data-bs-toggle="tooltip" data-bs-placement="top" title="Cari">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
    
                                        <!-- Tombol Aksi -->
                                        <div class="d-flex gap-2">
                                            <button type="button" id="btnResetFilter" class="btn d-flex align-items-center gap-1 btn-icon shadow-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset" style="border-radius: 20px; background-color: #192f6e; color: #ffffff;">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>       
                    </div>
                </div>
                <div class="card-body p-0 bg-light position-relative" style="height: auto; ">
                    @if (count($items) > 0)
                    <div class="table-responsive p-0 table-scroll-container" id="table-container" data-pagecount="{{ $pageCount ?? 1 }}">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead>
                                <tr class="text-center">
                                    <th class="position-sticky top-0 z-10 text-uppercase text-xxs font-weight-bolder opacity-7 th-kode">Kode</th>
                                    <th class="position-sticky top-0 z-10 text-uppercase text-xxs font-weight-bolder opacity-7 th-name">Nama Item</th>
                                    <th class="position-sticky top-0 z-10 text-uppercase text-xxs font-weight-bolder opacity-7 th-harga">Harga</th>
                                    <th class="position-sticky top-0 z-10 text-uppercase text-xxs font-weight-bolder opacity-7 th-stok">Stok</th>
                                    <th class="position-sticky top-0 z-10 text-uppercase text-xxs font-weight-bolder opacity-7 th-satuan">Satuan</th>
                                </tr>
                            </thead>
                            <tbody id="item-table-body">
                                @include('partials.item-rows', ['items' => $items])
                            </tbody>
                        </table>                         
                    </div>
                    <div id="loader" style="display: none;">
                        <div class="loader-overlay d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="d-flex justify-content-center align-items-center mb-4">
                                <dotlottie-wc
                                src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
                                style="width: 100%; max-width: 300px; height: auto; display: block; margin: auto;"
                                speed="1"
                                autoplay
                                loop
                                ></dotlottie-wc>
                            </div>
                            <p style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.8); font-weight: 500; margin-top: -50px">
                                Mohon tunggu...
                            </p>
                        </div>
                    </div>

                    @else
                        @if(request('search'))
                            <div class="alert p-4">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>{{ request('search') }} tidak ada,</strong> cek kembali penulisan kode atau nama barang!!!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                    @endif                                   
                </div>
                <div class="card-footer p-2" style="box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.26); z-index: 100; background-color: #f7f7f7;">
                    <div class="d-flex justify-content-between align-items-center p-2">
                        <!-- Kiri: nama aplikasi -->
                        <div class="fw-bold">
                        TWINCOMGO
                        </div>
                        <!-- Tengah: tombol load more -->
                        <div>
                            <button id="load-more-btn" class="button" style="box-shadow:0 2px 2px rgb(0,0,0);">
                                <span class="button-content">Load More</span>
                            </button>
                            <span id="footer-status" class="text-muted small d-none"></span>
                        </div>
                        <!-- Kanan: informasi halaman -->
                        <div id="page-indicator" class="text-muted small">
                        Halaman <span id="current-page">1</span> / 
                        <span id="total-pages">{{ $pageCount ?? 1 }}</span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        // TomSelect untuk kategori
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
                placeholder: 'Pilih / Cari Kategori',
                allowEmptyOption: false,
                onChange: function(value) {
                    console.log('Kategori dipilih:', value);
                    // Set value ke hidden input agar bisa dikirim ke backend
                    document.getElementById('itemCategoryId').value = value;
                    performSearch(); // fungsi pencarian milikmu
                }
            });
        });
    </script>
@endpush

<script>

    function setLoadMoreLoading(isLoading) {
        const btn = document.getElementById('load-more-btn');
        if (!btn) return;
        const span = btn.querySelector('.button-content');
        if (isLoading) {
            btn.disabled = true;
            span.textContent = 'Loading...';
        } else {
            btn.disabled = false;
            span.textContent = 'Load More';
        }
    }


    // Helper: jalankan search sambil disable tombol, aktifkan lagi ketika loading selesai
    function triggerSearchWithBtnLock() {
        const btnSearch = document.getElementById('btnSearch');
        if (btnSearch) btnSearch.disabled = true;

        performSearch();

        (function waitUnlock() {
            if (!loading) {                 // loading milikmu: false = fetch selesai
            if (btnSearch) btnSearch.disabled = false;
            return;
            }
            requestAnimationFrame(waitUnlock);
        })();
    }
    // Input pencarian
    const searchInput = document.getElementById('search');
    let typingTimer;
    const doneTypingInterval = 1000; // waktu tunggu (ms) sebelum submit otomatis

    searchInput.addEventListener('input', function () {
        const btnSearch = document.getElementById('btnSearch');
        if (btnSearch) btnSearch.disabled = true;   // langsung disable saat user mulai mengetik
        
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            triggerSearchWithBtnLock();
        }, doneTypingInterval);
    });

    searchInput.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault(); // blokir Enter supaya tidak submit form
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('itemCategoryId');
        const stokReadySelect = document.getElementById('stok_ada');
        const btnSearch = document.getElementById('btnSearch');
        const resetBtn = document.getElementById('btnResetFilter');

        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                performSearch();
            });
        }

        if (stokReadySelect) {
            stokReadySelect.addEventListener('change', function () {
                performSearch();
            });
        }

        if (btnSearch && !btnSearch.__bound) {
            let searchBtnLocked = false;

            btnSearch.addEventListener('click', function (e) {
            e.preventDefault();

            // ⛔ Stop kalau semua filter kosong
            if (filtersEmpty()) {
                // opsional: kasih feedback kecil
                btnSearch.classList.add('btn-reset'); // pakai kelasmu untuk efek kecil
                setTimeout(() => btnSearch.classList.remove('btn-reset'), 300);
                return;
            }

            if (searchBtnLocked) return;           // abaikan klik dobel
            searchBtnLocked = true;
            btnSearch.disabled = true;             // feedback ke user

            performSearch();

            // Lepas lock begitu "loading" selesai (pakai flag loading yang sudah ada)
            (function waitUnlock() {
                if (!loading) {                      // loading = false -> fetch selesai
                searchBtnLocked = false;
                btnSearch.disabled = false;
                return;
                }
                requestAnimationFrame(waitUnlock);   // cek lagi frame berikutnya
            })();
            });

            btnSearch.__bound = true; // cegah double-binding
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                // bersihkan UI
            document.getElementById('stok_ada').value = '1';
            document.getElementById('category_search').tomselect?.clear();
            document.getElementById('itemCategoryId').value = '';
            document.getElementById('min_price').value = '';
            document.getElementById('max_price').value = '';
            document.getElementById('search').value = '';

            // Bersihkan query string di URL (tanpa reload)
            history.replaceState({}, '', '{{ route("items.index") }}');

            // Paksa ambil daftar awal (page 1) via AJAX
            queryString = '';          // penting: kosongkan query
            resetPagination();         // page=1, flags reset, show loader
            loadData('');              // ambil /item?page=1 (default)
            });
        }
    });

    // Format input harga
    document.getElementById('max_price').addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^,\d]/g, '');
        const split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            const separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        e.target.value = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    });

    document.getElementById('min_price').addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^,\d]/g, '');
        const split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        const ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            const separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        e.target.value = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    });

    // Variabel global untuk pagination dan filter
    let page = 2;
    let loading = false;
    let allDataLoaded = false;
    let maxEmptyPageSkip = parseInt(document.getElementById('table-container').dataset.pagecount) || 1;
    let emptyPageCount = 0;

    console.log('Max empty page skip:', maxEmptyPageSkip);

    const container = document.getElementById('table-container');
    const loader = document.getElementById('loader');
    const loadMoreBtn = document.getElementById('load-more-btn');

    let queryString = ''; // akan diupdate di performSearch()

    function buildQueryStringFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const parts = [];
        for (const key of ['search', 'min_price', 'max_price', 'stok_ada', 'category_id']) {
            const value = urlParams.get(key);
            if (value) {
                parts.push(`${key}=${encodeURIComponent(value)}`);
            }
        }
        return parts.length ? '&' + parts.join('&') : '';
    }

    queryString = buildQueryStringFromURL();

    function resetPagination() {
        page = 1;
        const tableEl = document.getElementById('table-container');
        const totalFromDataset = tableEl && tableEl.dataset ? (parseInt(tableEl.dataset.pagecount) || 1) : 1;
        setPageIndicator(1, totalFromDataset);

        loading = false;
        allDataLoaded = false;
        emptyPageCount = 0;

        const footerStatus = document.getElementById('footer-status');
        if (footerStatus) footerStatus.classList.add('d-none');   // <- sembunyikan pesan footer

        loader.innerHTML = `<div class="loader-overlay d-flex flex-column align-items-center justify-content-center py-4">
            <div class="d-flex justify-content-center align-items-center mb-4">
            <dotlottie-wc src="https://lottie.host/bfcdecd5-f791-4410-a25e-4e1ac854a90d/b6lBLjfRT3.json"
                style="width: 100%; max-width: 300px; height: auto; display: block; margin: auto;"
                speed="1" autoplay loop>
            </dotlottie-wc>
            </div>
            <p style="color: white; text-shadow: 2px 2px 6px rgba(0,0,0,0.8); font-weight: 500; margin-top: -50px">Mohon tunggu...</p>
        </div>`;
        loader.style.display = 'block';

        loadMoreBtn.style.display = 'block';                        // <- tampilkan tombol lagi
        document.getElementById('item-table-body').innerHTML = '';
    }

    function getEmptyMessage() {
        const stokAda = document.getElementById('stok_ada')?.value;
        if (stokAda === '0') return 'Stok kosong.';                 // filter stok=0 tapi tidak ada hasil
        return 'Tidak ada/Stok kosong.';                        // filter lain: keyword/kategori/harga
    }

    function loadData(currentQueryString) {
        if (loading || allDataLoaded) return;
        loading = true;
        loader.style.display = 'block';
        setLoadMoreLoading(true);
        let appendedThisRound = false;   // <- lacak ada baris yang berhasil ditambah
        
        const fetchPage = (targetPage) => {
            console.log(`Fetching page ${targetPage} => /item?page=${targetPage}${currentQueryString}`);
            return fetch(`/item?page=${targetPage}${currentQueryString}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(res => res.json());
        };

        const tryNextPage = () => {
            if (page > maxEmptyPageSkip) {
                // Semua page sudah dicoba
                loader.style.display = 'none';
                loadMoreBtn.style.display = 'none';

                const footerStatus = document.getElementById('footer-status');
                const tbody = document.getElementById('item-table-body');
                const hasAnyRows = !!tbody && tbody.querySelector('tr') !== null;  // << cek tabel sudah ada data sebelumnya

                if (footerStatus) {
                    footerStatus.textContent = (hasAnyRows || appendedThisRound)
                    ? 'Semua data sudah dimuat.'
                    : getEmptyMessage(); // 'Stok kosong.' atau 'Tidak ada data yang cocok.'
                    footerStatus.classList.remove('d-none');
                }

                allDataLoaded = true;
                loading = false;
                return;
            }

            fetchPage(page).then(({ html, pageCount }) => {
            if (page === 1 && pageCount) {
                maxEmptyPageSkip = pageCount;
                if (totalPagesEl) totalPagesEl.textContent = String(pageCount);
            }

            if (html && html.trim() !== '') {
                document.getElementById('item-table-body').insertAdjacentHTML('beforeend', html);
                appendedThisRound = true;
                setPageIndicator(page, maxEmptyPageSkip);

                page++; // siapkan untuk next page
                loader.style.display = 'none';
                loading = false;
                setLoadMoreLoading(false);
            } else {
                // halaman kosong → lanjut ke halaman berikutnya
                page++;
                tryNextPage();
            }
            }).catch(() => {
            loader.innerHTML = '<p class="text-danger text-center">Terjadi kesalahan saat mengambil data.</p>';
            loading = false;
            setLoadMoreLoading(false);
            });
        };

        tryNextPage();
    }


    const currentPageEl = document.getElementById('current-page');
    const totalPagesEl  = document.getElementById('total-pages');

    function setPageIndicator(curr, total) {
        if (currentPageEl) currentPageEl.textContent = String(curr);
        if (totalPagesEl)  totalPagesEl.textContent  = String(total);
    }

    function filtersEmpty() {
        const keyword    = document.getElementById('search')?.value.trim() || '';
        const minPrice   = document.getElementById('min_price')?.value.trim() || '';
        const maxPrice   = document.getElementById('max_price')?.value.trim() || '';
        const stokAda    = document.getElementById('stok_ada')?.value ?? ''; // stokAda punya default "1" di UI kamu
        const categoryId = document.getElementById('itemCategoryId')?.value || '';

        // Kalau kamu ingin "Stok Ready = 1" dianggap default (tidak dihitung filter),
        // anggap kosong ketika stokAda == '1'
        const stokConsideredEmpty = (stokAda === '');

        return (
        keyword === '' &&
        minPrice === '' &&
        maxPrice === '' &&
        categoryId === '' &&
        stokConsideredEmpty
        );
    }

    function performSearch() {
        // ⛔ kalau kosong semua, jangan request apa pun
        if (filtersEmpty()) {
            // kalau sebelumnya ada query di URL, kita bersihkan & reset tabel awal
            if (window.location.search) {
            history.replaceState({}, '', '{{ route("items.index") }}'); // bersihkan query string
            }
            // reset tampilan ke kondisi awal (tanpa memanggil API)
            document.getElementById('item-table-body').innerHTML = '';
            loader.style.display = 'none';
            loadMoreBtn.style.display = 'none';
            return;
        }

        const keyword = document.getElementById('search')?.value || '';
        const minPrice = document.getElementById('min_price')?.value || '';
        const maxPrice = document.getElementById('max_price')?.value || '';
        const stokAda = document.getElementById('stok_ada')?.value || '';
        const categoryId = document.getElementById('itemCategoryId')?.value || '';

        // Build query string
        const parts = [];
        if (keyword) parts.push(`search=${encodeURIComponent(keyword)}`);
        if (minPrice) parts.push(`min_price=${encodeURIComponent(minPrice)}`);
        if (maxPrice) parts.push(`max_price=${encodeURIComponent(maxPrice)}`);
        if (stokAda) parts.push(`stok_ada=${encodeURIComponent(stokAda)}`);
        if (categoryId) parts.push(`category_id=${encodeURIComponent(categoryId)}`);

        queryString = parts.length ? '&' + parts.join('&') : '';

        // Reset dan load ulang data dari page 1
        resetPagination();

        maxEmptyPageSkip = parseInt(document.getElementById('table-container').dataset.pagecount) || 1;
        console.log("Max empty page skip (refreshed):", maxEmptyPageSkip);

        loadData(queryString);
    }

    // Scroll to bottom trigger
    container.addEventListener('scroll', () => {
        const nearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 100;
        if (nearBottom && !loading) {
            loadData(queryString);
        }
    });

    // Load more button
    loadMoreBtn.addEventListener('click', () => {
        if (!loading && !allDataLoaded) {
            loadData(queryString);
        }
    });

</script>

@endsection
