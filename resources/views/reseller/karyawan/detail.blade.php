@extends('layout')

@section('content')
<style>
.detail-header {
    background: linear-gradient(135deg, #0d6efd, #2563eb);
    color: white;
    border-radius: 12px;
    padding: 1.5rem 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-bottom: 1.5rem;
}
.detail-header h3 {
    font-weight: 700;
    letter-spacing: 0.5px;
}

.filter-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.filter-card label {
    font-weight: 600;
    color: #333;
    margin-bottom: .3rem;
}

/* harga box */
.price-box {
    border-radius: 12px;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    background: #fff;
    transition: 0.3s;
}
.price-box:hover {
    transform: translateY(-3px);
}
.price-box .title {
    font-weight: 700;
}
.price-user {
    color: #198754;
    font-size: 1.6rem;
    font-weight: 700;
}
.price-reseller {
    color: #0d6efd;
    font-size: 1.6rem;
    font-weight: 700;
}

/* gudang */
.warehouse-card {
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
}
.warehouse-card .card-header {
    background: #f8f9fa;
    font-weight: 600;
}
</style>

<div class="container py-4">

    {{-- 🌈 HEADER --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3><i class="bi bi-box-seam me-2"></i> Detail Item</h3>
        <a href="{{ url()->previous() }}" class="btn btn-light fw-semibold">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </a>
    </div>

    {{-- 🎚️ FILTER --}}
    <div class="filter-card">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">
                    <i class="bi bi-building me-2"></i> Harga Berdasarkan Cabang
                </h5>
                <select id="branchSelect" class="form-select">
                    <option value="">Semua Cabang</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 💰 HARGA --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-md-4 text-center">
                @php
                    $imageUrl = isset($fileName[0])
                        ? route('proxy.image', ['fileName' => $fileName[0], 'session' => $session])
                        : asset('/images/noimage.jpg');
                @endphp
                <img src="{{ $imageUrl }}" alt="Gambar"
                    class="img-fluid rounded shadow-sm border" style="max-height:320px;object-fit:contain;">
            </div>

            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
                    <h4 class="fw-bold text-primary mb-2 mb-md-0 me-3">
                        {{ $item['name'] }}
                    </h4>
                    <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3">
                        <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="price-box p-3 h-100">
                            <div class="title text-success mb-2">
                                <i class="bi bi-person-circle me-1"></i> User
                            </div>
                            <p class="text-muted mb-1">Harga</p>
                            <h4 class="price-user" id="userPrice">
                                Rp {{ number_format($prices['user'] ?? 0, 0, ',', '.') }}
                            </h4>
                            <hr>
                            <p class="text-muted mb-1">Garansi</p>
                            <p class="fw-semibold">{{ $item['charField6'] ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="price-box p-3 h-100">
                            <div class="title text-primary mb-2">
                                <i class="bi bi-people-fill me-1"></i> Reseller
                            </div>
                            <p class="text-muted mb-1">Harga</p>
                            <h4 class="price-reseller" id="resellerPrice">
                                Rp {{ number_format($prices['reseller'] ?? 0, 0, ',', '.') }}
                            </h4>
                            <hr>
                            <p class="text-muted mb-1">Garansi</p>
                            <p class="fw-semibold">{{ $item['charField7'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🏬 GUDANG --}}
    @foreach (['Store', 'Tsc', 'Reseller', 'Konsinyasi'] as $type)
        @php
            $var = 'warehouses' . $type;
            $total = 'total' . $type;
        @endphp
        @if(!empty($$var) && count($$var) > 0)
            <div class="card warehouse-card">
                <div class="card-header">{{ $type }}</div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Lokasi</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include("partials.table{$type}", [$var => $$var])
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <strong>Total</strong>
                    <strong>{{ $$total }}</strong>
                </div>
            </div>
        @endif
    @endforeach

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let branchPage = 1;
    let totalBranchPage = 1;
    let isLoading = false;

    const tom = new TomSelect("#branchSelect", {
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Pilih Cabang...',
        preload: true,
        load: function(query, callback) {
            if (isLoading || branchPage > totalBranchPage) return callback();
            isLoading = true;
            fetch(`/branches?page=${branchPage}`)
                .then(res => res.json())
                .then(json => {
                    json.data.forEach(b => b.name = b.name || 'Tanpa Nama');
                    callback(json.data);
                    totalBranchPage = json.totalPage;
                    branchPage++;
                })
                .catch(() => callback())
                .finally(() => isLoading = false);
        },
        render: {
            option: function(item) {
                return `<div class="py-1 px-2">${item.name}</div>`;
            }
        }
    });

    const itemId = "{{ $item['id'] }}";

    tom.on('change', function(branchName) {
        if (!branchName && branchName !== "") return;

        const loader = document.getElementById('loader-display');
        loader.style.display = 'flex';

        fetch(`/item/${itemId}/price?branchName=${encodeURIComponent(branchName)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('userPrice').textContent = formatRupiah(data.user);
                document.getElementById('resellerPrice').textContent = formatRupiah(data.reseller);
            })
            .catch(err => console.error(err))
            .finally(() => loader.style.display = 'none');
    });

    function formatRupiah(number) {
        return 'Rp ' + (parseFloat(number || 0))
            .toFixed(0)
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
});
</script>
@endsection
