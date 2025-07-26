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
            height: 94vh;
        }
    </style>
@endif

<style>
    body {
        overflow: hidden;
    }
    .card {
        border-radius: 0%;
        border--ra
    }

    .card-body {
        flex: 1 1 auto;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .container-fluid {
        padding: 0;
    }

    .table-scroll-container {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;

        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    .td-harga {
        width: 100px;
        text-align: right;
        vertical-align: middle;
    }

    .harga-grid {
        display: grid;
        grid-template-columns: 30px 1fr; /* Rp selalu 30px, nominal menyesuaikan */
        justify-content: end;
        align-items: center;
    }

    .harga-rp {
        text-align: left;
    }

    .harga-nominal {
        text-align: right;
    }

    .table th,
    .table td {
        padding-top: 16px;
        padding-bottom: 16px;
        vertical-align: middle;
    }
    .dropdown {
        margin-left: 10px;
    }
    .check-box{
        margin-left: 10px;
    }

    .loader-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.377);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
    }

    .loader-text {
        font-weight: bold;
        color: #000000;
        font-size: 16px;
        margin-top: 10px;
    }

    @keyframes pulse {
        0% { opacity: 0.3; }
        50% { opacity: 1; }
        100% { opacity: 0.3; }
    }

    .spinner-grow-custom {
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        opacity: 0;
        animation: growSmooth 1.2s infinite ease-in-out;
        }

        @keyframes growSmooth {
        0% {
            transform: scale(0.3);
            opacity: 0;
        }
        50% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(0.3);
            opacity: 0;
        }
    }

    .table-scroll-container::-webkit-scrollbar {
        height: 8px;
        /* width: 5px; */
        background-color: #f1f1f1;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background-color: #555;
    }

    .select2-container--default .select2-selection--single {
        height: calc(2.365rem + 2px); /* sama dengan tinggi .form-control di Bootstrap 5 */
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
    }

    /* Samakan tinggi teks di dalam Select2 */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }

    /* From Uiverse.io by Madflows */ 
    .button {
        position: relative;
        overflow: hidden;
        height: 2.5rem;
        padding: 0 1rem;
        border-radius: 1.5rem;
        background: #3d3a4e;
        background-size: 400%;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    .button:hover::before {
        transform: scaleX(1);
    }

    .button-content {
        position: relative;
        z-index: 1;
    }

    .button::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        transform: scaleX(0);
        transform-origin: 0 50%;
        width: 100%;
        height: inherit;
        border-radius: inherit;
        background: linear-gradient(
            82.3deg,
            rgba(150, 93, 233, 1) 10.8%,
            rgba(99, 88, 238, 1) 94.3%
        );
        transition: all 0.475s;
    }

    .btn-reset:hover {
        transform: scale(1.1);
    }
    
    .ts-wrapper.single .ts-control {
        border-radius: 20px !important;
        box-shadow: 2px 2px 2px rgb(0, 0, 0); /* shadow halus */
        border: 1px solid #ced4da; /* biar tetap terlihat seperti input normal */
        background-color: #fff;
        transition: box-shadow 0.2s ease;
    }

    @media only screen and (max-width: 768px) {
  /* CSS khusus untuk perangkat mobile */
        body {
            font-size: 12px;
        }

        .button {
            height: 1.5rem;
            padding: 0 0.5rem;
        }

        .card-full {
            height: 95vh;
            display: flex;
            flex-direction: column;
            
        }
        .table-scroll-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }
        .card {
            width: 100%;
            margin-bottom: 20px;
        }
        .title {
            font-size: 13px;
            margin-bottom: -100px;
        }
        .table th,
        .table td {
            font-size: 9px;
        }

        .table .th-harga {
            text-align: center;
        }
        .table .td-harga {
            text-align: end;
        }

        .table .th-name, .table .td-name {
            min-width: 150px;
            text-align: justify;
        }

        .harga-grid {
            display: flex;
            gap: 4px;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: nowrap;
        }

        .harga-rp {
            flex-shrink: 0;
        }

        .harga-nominal {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            text-align: right;
        }

        .table-scroll-container {
            padding: 0 !important;
            max-height: 400px; /* Sesuaikan tinggi untuk mobile */
        }
        .pagination .page-link {
            font-size: 10px;
            padding: 2px 6px; /* biar tombol juga ikut kecil */
        }

        .form-control {
            font-size: 10px;
            padding: 4px 8px;
        }

        .form-check-label,
        .form-check-input {
            font-size: 10px;
        }

        .btn {
            font-size: 8px;
            padding: 4px 8px;
        }
        .btn-reset {
            font-size: 8px;
            height: 30px;
        }
        .btn i {
            font-size: 8px !important;
        }

        .form-check {
            margin-right: 5px !important;
        }
        .form-label {
            font-size: 10px;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }
        .form-control, .form-select {
            font-size: 10px;
            padding: 6px;
        }
        .form-check-label {
            font-size: 10px;
        }
        .btn i {
            font-size: 10px;
        }
        .row.g-2 > div {
            margin-bottom: 10px;
        }
        .tombol-aksi {
            display: flex !important;
            flex-direction: row !important;
            gap: 6px !important;
            justify-content: space-between;
        }

        .tombol-aksi .btn {
            flex: 1 1 48%; /* Biar mereka punya lebar hampir separuh */
            padding: 4px 0;
        }
        .kategori-stok-group .form-label {
            font-size: 13px;
        }

        .kategori-stok-group .form-select {
            font-size: 10px;
            padding: 6px;
        }
        .custom-margin-mobile {
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-top: -50px;
        }
        #loader {
            font-size: 12px;
        }

        #loader .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        #loader .p-4 {
            padding: 1rem !important;
        }
        .input-group .input-group-text {
            font-size: 10px;
        }

        #min_price::placeholder,
        #max_price::placeholder {
            font-size: 10px;
        }
        .container-fluid {
            padding-top: 20px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .ts-control, .ts-dropdown, .ts-control input {
            font-size: 10px !important;
        }

        dotlottie-wc {
            width: 100px !important;
            height: 100px !important;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 card-full border-0 rounded">
                <div class="{{$status === 'admin' ? 'card-header p-3 bg-secondary text-white' : 'card-header p-3 bg-secondary text-white rounded-0'}}">
                    <div class="row align-items-center">
                        <h3 style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">Daftar Item</h3>                      
                        <form action="{{ route('items.index') }}" method="GET" id="filterForm">                            
                            <div class="row g-3 mt-2">
                                <div class="col-6 col-md-2">
                                    <label for="stok_ada" class="form-label" style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">Stok Ready</label>
                                    <select class="form-select" name="stok_ada" id="stok_ada" style="border-radius: 20px; box-shadow: 2px 2px 2px rgb(0, 0, 0);">
                                        <option value="1" {{ request('stok_ada') == '1' ? 'selected' : '' }}>Ya</option>
                                        <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="category_search" class="form-label" style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">Cari Kategori </label>
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
                                    <label for="min_price" class="form-label" style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">Harga Minimum</label>
                                    <div class="input-group" style="border-radius: 20px; box-shadow: 2px 2px 2px rgb(0, 0, 0);">                                            
                                        <span class="input-group-text" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">Rp</span>
                                        <input type="text" name="min_price" id="min_price" class="form-control" value="{{ old('min_price', request('min_price', $min_price ?? '')) }}" min="0" placeholder="Min Harga" style="border-start-end-radius: 20px; border-end-end-radius: 20px;">
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="max_price" class="form-label" style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">Harga Maksimum</label>
                                    <div class="input-group" style="border-radius: 20px; box-shadow: 2px 2px 2px rgb(0, 0, 0);"> 
                                        <span class="input-group-text" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">Rp</span>
                                        <input type="text" name="max_price" id="max_price" class="form-control" value="{{ old('max_price', request('max_price', $max_price ?? '')) }}" min="0" placeholder="Max Harga" style="border-start-end-radius: 20px; border-end-end-radius: 20px;">
                                    </div>
                                </div>                                    
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <!-- Input Pencarian -->
                                        <div style="min-width: 250px; flex-grow: 1;">
                                            <label for="search" class="form-label" style="text-shadow: 2px 2px 2px rgb(0, 0, 0);">
                                                Gunakan % untuk kombinasi kata pencarian.
                                            </label>
                                            <div class="input-group" style="border-radius: 20px; box-shadow: 2px 2px 2px rgb(0, 0, 0);">
                                                <input type="text" name="search" id="search" class="form-control" placeholder="Kode / Nama Barang" value="{{ request('search') }}" style="border-end-start-radius: 20px; border-start-start-radius: 20px;">
                                                <button class="btn btn-light" id="btnSearch" style="border-start-end-radius: 20px; border-end-end-radius: 20px;">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
    
                                        <!-- Tombol Aksi -->
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('items.index') }}" class="btn btn-info d-flex align-items-center gap-1 btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset" style="border-radius: 20px; box-shadow: 2px 2px 2px rgb(0, 0, 0);">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>       
                    </div>
                </div>
                <div class="card-body p-0 bg-light position-relative" style="height: auto; ">
                    @if (count($items) > 0)
                    <div class="table-responsive p-0 table-scroll-container" id="table-container">
                        <table class="table align-items-center mb-0 table-hover">
                            <thead class="table-light">
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
                <div class="card-footer" style="box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.26); z-index: 100;">
                    <div class="d-flex justify-content-center align-items-center">
                        <button id="load-more-btn" class="button" style="box-shadow: 0 2px 2px rgb(0, 0, 0);">
                            <span class="button-content">Load More</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        // TomSelect untuk kategori
        const selectedCategoryId = document.getElementById('category_search').value;

        document.addEventListener("DOMContentLoaded", function () {
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
    // Input pencarian
    const searchInput = document.getElementById('search');
    let typingTimer;
    const doneTypingInterval = 1000; // waktu tunggu (ms) sebelum submit otomatis

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            performSearch();
        }, doneTypingInterval);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('itemCategoryId');
        const stokReadySelect = document.getElementById('stok_ada');
        const btnSearch = document.getElementById('btnSearch');

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

        if (btnSearch) {
            btnSearch.addEventListener('click', function(event) {
                event.preventDefault();
                performSearch();
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
    const maxEmptyPageSkip = 10;
    let emptyPageCount = 0;

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
        loading = false;
        allDataLoaded = false;
        emptyPageCount = 0;
        loader.innerHTML = `<div class="loader-overlay d-flex flex-column align-items-center justify-content-center py-4">
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
                            </div>`;
        loader.style.display = 'block';
        loadMoreBtn.style.display = 'block';
        document.getElementById('item-table-body').innerHTML = '';
    }

    function loadData(currentQueryString) {
        if (loading || allDataLoaded) return;
        loading = true;
        loader.style.display = 'block';

        const fetchPage = (targetPage) => {
            console.log(`Fetching page ${targetPage} => /item?page=${targetPage}${currentQueryString}`);
            return fetch(`/item?page=${targetPage}${currentQueryString}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(res => res.text());
        };

        fetchPage(page).then(data => {
            if (data.trim() !== '') {
                document.getElementById('item-table-body').insertAdjacentHTML('beforeend', data);
                page++;
                emptyPageCount = 0;
                loader.style.display = 'none';
                loading = false;
            } else {
                // Recursive retry up to maxEmptyPageSkip
                const tryNextPage = () => {
                    if (emptyPageCount >= maxEmptyPageSkip) {
                        loader.innerHTML = '<p class="text-center mt-2">Semua data sudah dimuat.</p>';
                        loadMoreBtn.style.display = 'none';
                        allDataLoaded = true;
                        loading = false;
                        return;
                    }
                    page++;
                    emptyPageCount++;
                    fetchPage(page).then(nextData => {
                        if (nextData.trim() !== '') {
                            document.getElementById('item-table-body').insertAdjacentHTML('beforeend', nextData);
                            page++;
                            emptyPageCount = 0;
                            loader.style.display = 'none';
                            loading = false;
                        } else {
                            tryNextPage();
                        }
                    }).catch(() => {
                        loader.innerHTML = '<p>Gagal memuat data.</p>';
                        loading = false;
                    });
                };
                tryNextPage();
            }
        }).catch(() => {
            loader.innerHTML = '<p class="text-danger">Terjadi kesalahan saat mengambil data.</p>';
            loading = false;
        });
    }

    function performSearch() {
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
        loadData(queryString);
    }

    // Scroll to bottom trigger
    container.addEventListener('scroll', () => {
        const nearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight;
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
