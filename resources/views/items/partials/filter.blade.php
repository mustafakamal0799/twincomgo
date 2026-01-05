<form method="GET" class="mb-4 filter-card shadow compact" id="filter-form">
    <div class="row g-2 justify-content-start">

        <!-- Stok Ready -->
        <div class="col-6 col-sm-6 col-md-6 col-lg-auto">
            <label class="form-label mb-1">Stok Ready</label>
            <select name="stok_ada" class="form-select shadow-sm">
                <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>Ya</option>
                <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
            </select>
        </div>

        <!-- Jenis Harga -->
        <div class="col-6 col-sm-6 col-md-6 col-lg-auto">
            <label class="form-label mb-1">Jenis Harga</label>
            <select name="price_mode" class="form-select shadow-sm">
                <option value="default" {{ request('price_mode', 'default') == 'default' ? 'selected' : '' }}>User</option>
                <option value="reseller" {{ request('price_mode') == 'reseller' ? 'selected' : '' }}>Reseller</option>
            </select>
        </div>

        <!-- Min Harga -->
        <div class="col-6 col-sm-6 col-md-6 col-lg-auto">
            <label class="form-label mb-1">Min Harga</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" name="min_price" id="min_price"
                    class="form-control shadow-sm"
                    value="{{ request('min_price') }}"
                    placeholder="0"
                    oninput="formatRupiahFilter(this)">
            </div>
        </div>

        <!-- Max Harga -->
        <div class="col-6 col-sm-6 col-md-6 col-lg-auto">
            <label class="form-label mb-1">Max Harga</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" name="max_price" id="max_price"
                    class="form-control shadow-sm"
                    value="{{ request('max_price') }}"
                    placeholder="0"
                    oninput="formatRupiahFilter(this)">
            </div>
        </div>

        <!-- Kategori -->
        <div class="col-12 col-sm-12 col-md-12 col-lg-2">
            <label class="form-label mb-1">Pilih Kategori</label>
            <select name="category_id[]" id="category_search" class="form-select shadow-sm" multiple>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}"
                        {{ collect(request('category_id'))->contains($cat['id']) ? 'selected' : '' }}>
                        {{ $cat['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search -->
        <div class="col-12 col-sm-12 col-md-12 col-lg-3">
            <label class="form-label mb-1">Gunakan % untuk kombinasi kata pencarian</label>
            <input type="text" name="search" class="form-control shadow-sm"
                value="{{ request('search') }}"
                placeholder="Kode / Nama barang">
        </div>

        <!-- Buttons -->
        <div class="col-12 col-md-6 col-lg-1 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                <i class="bi bi-search"></i>
            </button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary w-100 shadow-sm">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>

    </div>
</form>
