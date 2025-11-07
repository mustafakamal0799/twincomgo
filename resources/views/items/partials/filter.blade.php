<!-- 🔍 Filter -->
    <form method="GET" class="mb-4 filter-card shadow" id="filter-form">
        <div class="row g-2 justify-content-center">
            <div class="col-12 col-md-1">
                <label for="stok_ada" class="form-label mb-1">Stok Ready</label>
                <select name="stok_ada" class="form-select shadow-sm">
                    <option value="1" {{ request('stok_ada', '1') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ request('stok_ada') == '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>

            <!-- 🔸 Jenis Harga -->
            <div class="col-12 col-md-1">
                <label for="price_mode" class="form-label mb-1">Jenis Harga</label>
                <select name="price_mode" class="form-select shadow-sm">
                    <option value="default" {{ request('price_mode', 'default') == 'default' ? 'selected' : '' }}>User</option>
                    <option value="reseller" {{ request('price_mode') == 'reseller' ? 'selected' : '' }}>Reseller</option>
                </select>
            </div>

            <!-- 🔸 Kategori -->
            <div class="col-12 col-md-2">
                <label for="category" class="form-label mb-1">Pilih kategori</label>
                <select name="category_id" id="category_search" class="form-select shadow-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['id'] }}" {{ request('category_id') == $cat['id'] ? 'selected' : '' }}>
                            {{ $cat['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 🔸 Harga Minimum -->
            <div class="col-6 col-md-2 price-input">
                <label for="min_price" class="form-label mb-1">Min Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="min_price" id="min_price"
                        class="form-control shadow-sm"
                        value="{{ request('min_price') }}" min="0"
                        placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>

            <!-- 🔸 Harga Maksimum -->
            <div class="col-6 col-md-2 price-input">
                <label for="max_price" class="form-label mb-1">Max Harga</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="max_price" id="max_price"
                        class="form-control shadow-sm"
                        value="{{ request('max_price') }}" min="0"
                        placeholder="0" oninput="formatRupiah(this)">
                </div>
            </div>

            <!-- 🔸 Pencarian -->
            <div class="col-12 col-md-3">
                <label for="search" class="form-label mb-1">Gunakan % untuk kombinasi kata pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="form-control shadow-sm" placeholder="Kode / Nama barang">
            </div>

            <!-- 🔸 Tombol -->
            <div class="col-12 col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary w-100 shadow-sm">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>