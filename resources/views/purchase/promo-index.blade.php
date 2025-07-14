@extends('layout')

@section('content')
<div class="container-fluid mt-5">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Daftar Promo</h4>
            <div class="row mt-4">
                <div class="col-sm-9">
                    <a href="{{ route('promo.create') }}" class="btn btn-primary btn-sm" title="Tambah Promo" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="bi bi-plus-circle fs-5"></i>
                    </a>
                </div>
                <div class="col-sm-3">
                    <form method="GET" action="{{ route('promo.index') }}">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari promo..." value="{{ request('search') }}" style="width: 300px">
                            <button class="btn btn-primary" type="submit" data-bs-toggle="tooltip" data-bs-placement="top" title="Cari promo">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Deskripsi</th>
                            <th>Harga Awal</th>
                            <th>Harga Diskon</th>
                            <th>Gambar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promos as $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                            <td>{{ $item->name ?? $item->judul }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>Rp {{ number_format($item->harga_asli, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_diskon, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" width="100" class="img-thumbnail">
                            </td>
                            <td class="text-center">
                                <a href="{{ route('promo.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('promo.destroy', $item->id) }}" method="POST" style="display: inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada promo tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


@endsection
