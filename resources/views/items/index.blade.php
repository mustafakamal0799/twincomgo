@extends('layout')

@section('content')

<style>
    body {
        overflow: hidden;
    }
    .card {
        border-radius: 0%;
    }
    
    .card-full {
        height: 94vh;
        display: flex;
        flex-direction: column;
        
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
    @media only screen and (max-width: 768px) {
  /* CSS khusus untuk perangkat mobile */
        body {
            font-size: 12px;
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
    }



</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 card-full border-0">
                <div class="card-header p-3 bg-secondary text-white rounded-0">
                    <div class="row align-items-center">
                        <h3>Daftar Item</h3>                      
                        <form action="{{ route('items.index') }}" method="GET" id="filterForm">                            
                            <div class="row g-3 mt-2">
                                <div class="col-6 col-md-2">
                                    <label for="stok_ada" class="form-label">Stok Ready</label>
                                    <select class="form-select" name="stok_ada" id="stok_ada">
                                        <option value="1" {{ request('stok_ada') == '1' ? 'selected' : '' }}>Ya</option>
                                        <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="hidden" name="category_id" id="itemCategoryId">
                                    <label for="category_search" class="form-label">Cari Kategori </label>
                                    <select id="category_search" id="itemCategoryId" style="width: 100%;">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="min_price" class="form-label">Harga Minimum</label>
                                    <div class="input-group">                                            
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="min_price" id="min_price" class="form-control" value="{{ old('min_price', request('min_price', $min_price ?? '')) }}" min="0" placeholder="Min Harga">
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label for="max_price" class="form-label">Harga Maksimum</label>
                                    <div class="input-group"> 
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="max_price" id="max_price" class="form-control" value="{{ old('max_price', request('max_price', $max_price ?? '')) }}" min="0" placeholder="Max Harga">
                                    </div>
                                </div>                                    
                                <div class="col-12 col-md-4">
                                    <div class="d-flex flex-wrap align-items-end gap-2">
                                        <!-- Input Pencarian -->
                                        <div style="min-width: 250px; flex-grow: 1;">
                                            <label for="search" class="form-label">
                                                Gunakan % untuk kombinasi kata pencarian.
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="search" id="search" class="form-control" placeholder="Kode / Nama Barang" value="{{ request('search') }}">
                                                <button class="btn btn-light" id="btnSearch">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
    
                                        <!-- Tombol Aksi -->
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('items.index') }}" class="btn btn-info d-flex align-items-center gap-1 btn-reset" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>       
                    </div>
                </div>
                <div class="card-body p-0 bg-light position-relative" style="height: auto;">
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
                                    {{-- <th class="position-sticky top-0 bg-white z-10 text-uppercase text-xxs font-weight-bolder opacity-7">Action</th> --}}
                                </tr>
                            </thead>
                            <tbody id="item-table-body">
                                @include('partials.item-rows', ['items' => $items])
                            </tbody>
                        </table>                         
                    </div>
                    <div id="loader" style="display: none;">
                        <div class="loader-overlay d-flex flex-column align-items-center justify-content-center py-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="spinner-grow-custom bg-light me-2" style="animation-delay: 0s;"></div>
                                <div class="spinner-grow-custom bg-light me-2" style="animation-delay: 0.2s;"></div>
                                <div class="spinner-grow-custom bg-light" style="animation-delay: 0.4s;"></div>
                            </div>
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
                <div class="card-footer">
                    <div class="d-flex justify-content-center align-items-center">
                        <button id="load-more-btn" class="btn btn-outline-primary btn-sm">Load more ...</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>

    $(document).ready(function() {
        $('#category_search').select2({
            placeholder: 'Ketik nama kategori...',
            allowClear: true,
            ajax: {
                url: '{{ route('categories.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '' // kirim kosong kalau kosong
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Saat select
        $('#category_search').on('select2:select', function(e) {
            const data = e.params.data;
            $('#itemCategoryId').val(data.id);
            performSearch(); // kalau kamu ingin langsung cari
        });

        $('#category_search').on('select2:open', function () {
            setTimeout(function () {
                const searchField = $('.select2-search__field');
                searchField.val('').trigger('input').trigger('keyup');
            }, 100); // delay biar field-nya sudah siap
        });

        // Saat clear (klik X)
        $('#category_search').on('select2:clear', function () {
            $('#itemCategoryId').val('');
            performSearch();
        });

        // Saat dikosongkan manual
        $('#category_search').on('change', function () {
            if (!$(this).val()) {
                $('#itemCategoryId').val('');
                performSearch();
            }
        });
    });

    </script>
@endpush


<script>
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
    
    let page = 2;
    let loading = false;
    let allDataLoaded = false;

    const container = document.getElementById('table-container');
    const loader = document.getElementById('loader');
    const loadMoreBtn = document.getElementById('load-more-btn');

    const urlParams = new URLSearchParams(window.location.search);
    const search = urlParams.get('search') || '';
    const minPrice = urlParams.get('min_price') || '';
    const maxPrice = urlParams.get('max_price') || '';
    const stokAda = urlParams.get('stok_ada') || '';
    const categoryId = urlParams.get('category_id') || '';

    // Bangun query param manual (kalau kosong tidak ikut)
    // queryString harus global dan diupdate oleh performSearch
    var queryString = '';
    if (search) queryString += `&search=${encodeURIComponent(search)}`;
    if (minPrice) queryString += `&min_price=${encodeURIComponent(minPrice)}`;
    if (maxPrice) queryString += `&max_price=${encodeURIComponent(maxPrice)}`;
    if (stokAda) queryString += `&stok_ada=${encodeURIComponent(stokAda)}`;
    if (categoryId) queryString += `&category_id=${encodeURIComponent(categoryId)}`;

    function loadData() {
        if (loading || allDataLoaded) return; // ⬅️ Cegah load kalau sudah habis
        loading = true;
        loader.style.display = 'block';

        fetch(`/item?page=${page}${queryString}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() !== '') {
                document.getElementById('item-table-body').insertAdjacentHTML('beforeend', data);
                page++;
                loading = false;
                loader.style.display = 'none';
            } else {
                loader.innerHTML = '<p class="text-center mt-2">Semua data sudah dimuat.</p>';
                loadMoreBtn.style.display = 'none';
                allDataLoaded = true; // ⬅️ Tandai sudah habis
                loading = false;
            }
        })
        .catch(() => {
            loader.innerHTML = '<p>Gagal memuat data.</p>';
            loading = false;
        });
    }


    // Trigger via scroll
    container.addEventListener('scroll', function () {
        const nearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 100;
        if (nearBottom && !loading) {
            loadData();
        }
    });

    // Trigger via button
    loadMoreBtn.addEventListener('click', function () {
        loadData();
    });
    function performSearch() {
        const keyword = document.getElementById('search').value;
        const minPrice = document.getElementById('min_price')?.value || '';
        const maxPrice = document.getElementById('max_price')?.value || '';
        const stokAda = document.getElementById('stok_ada')?.value || '';
        const categoryId = document.getElementById('itemCategoryId')?.value || '';

        // Bangun query string
        let query = `?search=${encodeURIComponent(keyword)}`;
        if (minPrice) query += `&min_price=${encodeURIComponent(minPrice)}`;
        if (maxPrice) query += `&max_price=${encodeURIComponent(maxPrice)}`;
        if (stokAda) query += `&stok_ada=${encodeURIComponent(stokAda)}`;
        if (categoryId) query += `&category_id=${encodeURIComponent(categoryId)}`;

        // Reset
        page = 2;
        loading = false;
        allDataLoaded = false;
        loader.innerHTML = '<div class="loader-overlay d-flex flex-column align-items-center justify-content-center py-4"><div class="d-flex justify-content-center align-items-center"><div class="spinner-grow-custom bg-light me-2" style="animation-delay: 0s;"></div><div class="spinner-grow-custom bg-light me-2" style="animation-delay: 0.2s;"></div><div class="spinner-grow-custom bg-light" style="animation-delay: 0.4s;"></div></div></div>'; // reset with spinner
        loader.style.display = 'block';
        loadMoreBtn.style.display = 'block';
        document.getElementById('item-table-body').innerHTML = '';

        // Simpan query global (PASTIKAN var queryString global, bukan let)
        if (keyword || minPrice || maxPrice || stokAda || categoryId) {
            queryString = '&' + query.slice(1);
        } else {
            queryString = '';
        }

        fetch(`/item${query}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('item-table-body').innerHTML = data;
            loader.style.display = 'none';
        })
        .catch(() => {
            document.getElementById('item-table-body').innerHTML = '<tr><td colspan="3">Gagal mengambil data.</td></tr>';
            loader.innerHTML = '<p class="text-danger">Terjadi kesalahan saat mengambil data.</p>';
            loader.style.display = 'block';
            loading = false;
        });
    }

</script>

@endsection
