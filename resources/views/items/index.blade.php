@extends('layout')

@section('content')

<style>
    .card-full {
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.8);
        }

    .table-scroll-container {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .dropdown {
        margin-left: 10px;
    }
    .check-box{
        margin-left: 10px;
    }

    @media only screen and (max-width: 768px) {
  /* CSS khusus untuk perangkat mobile */
        body {
            font-size: 10px;
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
            font-size: 8px;
        }
        .table-scroll-container {
            padding: 0 !important;
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
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 card-full border-0">
                <div class="card-header p-3 bg-secondary text-white">
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
                                    <label for="itemCategoryId" class="form-label">Pilih Kategori</label>
                                    <select name="category_id" id="itemCategoryId" class="form-select">
                                        <option value="">Semua Kategori</option>
                                        {!! $categoryOptions !!}
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
                                                <small>Gunakan <code>%</code> untuk kombinasi kata pencarian.</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="search" id="search" class="form-control" placeholder="Kode / Nama Barang" value="{{ request('search') }}">
                                                <button class="btn btn-light" type="submit">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
    
                                        <!-- Tombol Aksi -->
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('items.index') }}" class="btn btn-info d-flex align-items-center gap-1 btn-reset">
                                                <i class="bi bi-arrow-clockwise"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>       
                    </div>
                </div>
                <div class="card-body p-2 bg-light">
                    @if (count($items) > 0)
                    <div class="table-responsive p-1 table-scroll-container" id="table-container">
                        <table class="table align-items-center mb-0 table-hover ">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Kode</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Nama Item</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Harga</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Stok</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Satuan</th>
                                    <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody id="item-table-body">
                                @include('partials.item-rows', ['items' => $items])
                            </tbody>
                        </table>
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
                    <div id="loader" style="text-align: center; display: none;">
                        <div class="d-flex align-items-center p-4">
                            <strong>Loading...</strong>
                            <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                        </div>
                    </div>                    
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <button id="load-more-btn" class="btn btn-primary btn-sm">Muat Lebih Banyak</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('search');
    let typingTimer;
    const doneTypingInterval = 400; // waktu tunggu (ms) sebelum submit otomatis

    searchInput.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            this.form.submit();
        }, doneTypingInterval);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('itemCategoryId');
        const stokReadySelect = document.getElementById('stok_ada');

        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });
        }
        
        if (stokReadySelect) {
            stokReadySelect.addEventListener('change', function () {
                document.getElementById('filterForm').submit();
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
    let queryString = '';
    if (search) queryString += `&search=${encodeURIComponent(search)}`;
    if (minPrice) queryString += `&min_price=${encodeURIComponent(minPrice)}`;
    if (maxPrice) queryString += `&max_price=${encodeURIComponent(maxPrice)}`;
    if (stokAda) queryString += `&stok_ada=${encodeURIComponent(stokAda)}`;
    if (categoryId) queryString += `&category_id=${encodeURIComponent(categoryId)}`;

    function loadData() {
        if (loading) return;
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
                loader.innerHTML = '<p>Semua data sudah dimuat.</p>';
                loadMoreBtn.style.display = 'none'; // Sembunyikan tombol kalau sudah habis
            }
        })
        .catch(() => {
            loader.innerHTML = '<p>Gagal memuat data.</p>';
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
</script>

@endsection
