@extends('layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Tambah Promo Baru</h4>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('promo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="judul" class="form-label">Nama Barang</label>
                    <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh: Promo Spesial Akhir Tahun" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan detail promo di sini..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Promo</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                    <div class="form-text">Ukuran ideal: 418x200px</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="harga_asli" class="form-label">Harga Asli</label>
                        <input type="number" class="form-control" id="harga_asli" name="harga_asli" placeholder="Contoh: 100000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="harga_diskon" class="form-label">Harga Diskon</label>
                        <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" placeholder="Contoh: 75000" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('promo.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
