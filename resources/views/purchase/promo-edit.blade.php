@extends('layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Promo</h4>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('promo.update', $promo->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Judul --}}
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="judul" value="{{ old('judul', $promo->judul) }}" class="form-control @error('judul') is-invalid @enderror" required>
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" required>{{ old('deskripsi', $promo->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Harga Asli --}}
                <div class="mb-3">
                    <label class="form-label">Harga Asli</label>
                    <input type="number" name="harga_asli" value="{{ old('harga_asli', $promo->harga_asli) }}" class="form-control @error('harga_asli') is-invalid @enderror" required>
                    @error('harga_asli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Harga Diskon --}}
                <div class="mb-3">
                    <label class="form-label">Harga Diskon</label>
                    <input type="number" name="harga_diskon" value="{{ old('harga_diskon', $promo->harga_diskon) }}" class="form-control @error('harga_diskon') is-invalid @enderror" required>
                    @error('harga_diskon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Gambar Saat Ini --}}
                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label><br>
                    <img src="{{ Storage::url($promo->gambar) }}" alt="Gambar Promo" class="img-thumbnail" width="200">
                </div>

                {{-- Ganti Gambar --}}
                <div class="mb-3">
                    <label class="form-label">Ganti Gambar (Opsional)</label>
                    <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror">
                    @error('gambar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol --}}
                <div class="d-flex justify-content-between">
                    <a href="{{ route('promo.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
