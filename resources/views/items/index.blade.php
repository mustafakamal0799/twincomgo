@extends('layout')

@section('content')

<style>
    .card {
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
            font-size: 14px;
        }

        .card {
            width: 100%;
            margin-bottom: 20px;
        }
        .title {
            font-size: 15px;
        }
        .table th,
        .table td {
            font-size: 10px;
        }
        .table-scroll-container {
            padding: 0 !important;
        }
        .pagination .page-link {
            font-size: 10px;
            padding: 2px 6px; /* biar tombol juga ikut kecil */
        }

        .form-control {
            font-size: 12px;
            padding: 4px 8px;
        }

        .form-check-label,
        .form-check-input {
            font-size: 12px;
        }

        .btn {
            font-size: 12px;
            padding: 4px 8px;
        }

        .btn i {
            font-size: 14px !important;
        }

        .form-check {
            margin-right: 5px !important;
        }
        .form-label {
        font-size: 13px;
        }
        .form-control, .form-select {
            font-size: 13px;
            padding: 6px;
        }
        .form-check-label {
            font-size: 13px;
        }
        .btn i {
            font-size: 14px;
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
            font-size: 13px;
            padding: 6px;
        }
    }
</style>

<div class="container-fluid py-4">
   <div class="p-3">
    <form action="{{ route('items.index') }}" method="GET">
        <div class="row g-2">
            {{-- Group Kategori + Stok Ready --}}
            <div class="col-lg-6 col-md-8 col-12 kategori-stok-group">
                <div class="row g-2">
                    {{-- Pilih Kategori --}}
                    <div class="col-md-6 col-6">
                        <label for="itemCategoryId" class="form-label">Pilih Kategori</label>
                        <select name="id" id="itemCategoryId" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category['id'] }}" {{ request('id') == $category['id'] ? 'selected' : '' }}>
                                    {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Stok Ready --}}
                    <div class="col-md-6 col-6">
                        <label for="stok_ada" class="form-label">Stok Ready</label>
                        <select class="form-select" name="stok_ada" id="stok_ada">
                            <option value="" {{ request('stok_ada') === '' ? 'selected' : '' }}>TIDAK</option>
                            <option value="1" {{ request('stok_ada') == '1' ? 'selected' : '' }}>YA</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Input Pencarian --}}
            <div class="col-lg-3 col-md-4 col-12">
                <label for="q" class="form-label">Cari Barang</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Contoh: Ketik Laptop, Gunakan % untuk kombinasi kata pencarian" value="{{ request('q') }}">
            </div>

            <div class="col-lg-2 col-md-4 col-12 d-flex align-items-end gap-2 tombol-aksi">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-success w-100">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>

        </div>
    </form>
</div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header p-3 d-flex justify-content-between align-items-center">
                    <h4 class="title text-center">DAFTAR BARANG</h4>                   
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (count($items) > 0)
                        <div class="table-responsive p-4 table-scroll-container">
                            <table class="table align-items-center mb-0 table-striped">
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-4">Kode</th>
                                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Nama Item</th>
                                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Stok</th>
                                        <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                    <tr>
                                        <!-- Nama item yang akan mengarah ke halaman detail -->
                                        <td class="ps-4 text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $item['no'] ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ Str::limit($item['name'], 50) ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $item['availableToSell'] ?? '-' }}</p>
                                        </td>
                                        <td class="align-middle text-center"><a href="{{ route('items.detail', ['id' => $item['id']]) }}" class="btn btn-warning">Detail</a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                    <div class="alert p-4">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>{{$search}} tidak ada,</strong> cek kembali penulisan kode atau nama barang!!!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif                    
                </div>

                <div class="">
                    <div class="card-footer py-2">
                        @if ($pagination->last_page > 1)
                            @php
                                // Tentukan berapa banyak link halaman yang ingin ditampilkan
                                $max_links = 3; // Anda bisa ubah angka ini sesuai kebutuhan
                                $half = floor($max_links / 2);
                                $start = max($pagination->current_page - $half, 1);
                                $end = min($start + $max_links - 1, $pagination->last_page);
                                
                                // Adjust jika tidak mencapai max_links
                                if ($end - $start + 1 < $max_links) {
                                    $start = max($end - $max_links + 1, 1);
                                }
                            @endphp
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    {{-- Previous Page Link --}}
                                    <li class="page-item {{ $pagination->current_page == 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $pagination->prev_page_url }}&q={{ request('q') }}&stok_ada={{ request('stok_ada') }}&id={{ request('id') }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>

                                    {{-- First Page Link --}}
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ route('items.index', ['page' => 1, 'q' => request('q'), 'stok_ada' => request('stok_ada'), 'id' => request('id')]) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @for ($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $pagination->current_page == $i ? 'active' : '' }}">
                                            <a class="page-link" href="{{ route('items.index', ['page' => $i, 'q' => request('q'), 'stok_ada' => request('stok_ada'), 'id' => request('id')]) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    {{-- Last Page Link --}}
                                    @if($end < $pagination->last_page)
                                        @if($end < $pagination->last_page - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ route('items.index', ['page' => $pagination->last_page, 'q' => request('q'), 'stok_ada' => request('stok_ada'), 'id' => request('id')]) }}">
                                                {{ $pagination->last_page }}
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    <li class="page-item {{ $pagination->current_page == $pagination->last_page ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $pagination->next_page_url }}&q={{ request('q') }}&stok_ada={{ request('stok_ada') }}&id={{ request('id') }}" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
