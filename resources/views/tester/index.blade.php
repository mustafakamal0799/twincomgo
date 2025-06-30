@extends('layout')

@section('content')
<form id="search-form" method="GET" action="/item-test" style="margin-bottom: 10px;">
    <input type="text" name="search" placeholder="Cari item..." value="{{ $search ?? '' }}">
    <button type="submit">Cari</button>

    @if (!empty($search))
        <a href="/item-test" class="btn-reset" style="text-decoration: none; padding: 6px 12px; background: #eee; border: 1px solid #ccc;">
            Reset
        </a>
    @endif

    <select name="category_id">
        <option value="">-- Semua Kategori --</option>
        @foreach ($allCategories as $cat)
            <option value="{{ $cat['id'] }}" {{ request('category_id') == $cat['id'] ? 'selected' : '' }}>
                {{ $cat['name'] }}
            </option>
        @endforeach
    </select>


    <div class="col-md-4">
        <div class="form-check mt-4">
            <input type="hidden" name="stok_ada" value="0">
            <input class="form-check-input" type="checkbox" name="stok_ada" id="stok_ada" value="1"
                {{ request('stok_ada', 1) == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="stok_ada">
                Stok Ready
            </label>
        </div>
    </div>

    <div class="col-md-4">
        <label for="min_price" class="form-label">Harga Minimum</label>
        <input type="number" class="form-control" name="min_price" id="min_price"
            value="{{ request('min_price') }}">
    </div>

    <div class="col-md-4">
        <label for="max_price" class="form-label">Harga Maksimum</label>
        <input type="number" class="form-control" name="max_price" id="max_price"
            value="{{ request('max_price') }}">
    </div>
</form>
<a href="/item-test" class="btn btn-success">
    <i class="bi bi-arrow-clockwise"></i>
</a>
<div id="table-container" style="height: 500px; overflow-y: auto;">
    <table class="table align-items-center mb-0 table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody id="item-table-body">
            @include('partials.item-list', ['items' => $items])
        </tbody>
    </table>
</div>

<div id="loader" style="text-align: center; display: none;">
    <p>Loading...</p>
</div>

<script>
    let page = 2;
    let loading = false;

    const container = document.getElementById('table-container');
    const loader = document.getElementById('loader');

    const urlParams = new URLSearchParams(window.location.search);
    const search = urlParams.get('search') || '';
    const searchParam = search.trim() !== '' ? `&search=${encodeURIComponent(search)}` : '';

    container.addEventListener('scroll', function () {
        const nearBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 100;

        if (nearBottom && !loading) {
            loading = true;
            loader.style.display = 'block';

            fetch(`/item-test?page=${page}${searchParam}`, {
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
                }
            })
            .catch(() => {
                loader.innerHTML = '<p>Gagal memuat data.</p>';
            });
        }
    });
</script>

@endsection