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
    }
</style>

<div class="container-fluid py-4">
    <div class="p-2">                        
        <form action="{{ route('items.index') }}" method="GET" class="d-flex">                      
            <input type="text" name="q" class="form-control me-2" placeholder="Cari..." value="{{ request('q') }}">   
            <div class="form-check" style="margin-right: 10px">
                <input class="form-check-input" type="checkbox" name="stok_ada" id="stok_ada" value="1" {{ request('stok_ada') ? 'checked' : '' }}>
                <label class="form-check-label" for="stok_ada">
                    Stok Ada
                </label>
            </div>  
            <button type="submit" class="btn btn-secondary"><i class="bi bi-search" style="font-size: 15px"></i></button>                          

            <a href="{{ route('items.index')}}" class="btn btn-success" style="margin-left:10px; margin-right:10px;"><i class="bi bi-arrow-clockwise" style="font-size: 20px"></i></a>

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
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Kode</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stok</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                    <tr>
                                        <!-- Nama item yang akan mengarah ke halaman detail -->
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $item['no'] ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ Str::limit($item['name'], 50) ?? '-' }}</p>
                                        </td>
                                        <td>
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
                                        <a class="page-link" href="{{ $pagination->prev_page_url }}&q={{ request('q') }}&stok_ada={{ request('stok_ada')}}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                            
                                    {{-- First Page Link --}}
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ route('items.index', ['page' => 1, 'q' => request('q'), 'stok_ada' => request('stok_ada')]) }}">1</a>
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
                                            <a class="page-link" href="{{ route('items.index', ['page' => $i, 'q' => request('q'),'stok_ada' => request('stok_ada')]) }}">{{ $i }}</a>
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
                                            <a class="page-link" href="{{ route('items.index', ['page' => $pagination->last_page, 'q' => request('q'), 'stok_ada' => request('stok_ada')]) }}">
                                                {{ $pagination->last_page }}
                                            </a>
                                        </li>
                                    @endif
                            
                                    {{-- Next Page Link --}}
                                    <li class="page-item {{ $pagination->current_page == $pagination->last_page ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $pagination->next_page_url }}&q={{ request('q') }}&stok_ada={{ request('stok_ada') }}" aria-label="Next">
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
