@extends('layouts.app')

@section('content')
<style>
/* 🌈 HEADER */
.detail-header {
    background: linear-gradient(90deg, #1f2937, #374151);
    color: white;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}
.detail-header h3 { 
    font-weight: 700; 
    letter-spacing: 0.3px; 
    font-size: 1.35rem;
}

/* 💰 HARGA BOX */
.price-box {
    border-radius: 12px;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    background: #fff;
    padding: 1.25rem;
    transition: 0.3s;
}
.price-box:hover { transform: translateY(-3px); }
.price-box .title {
    font-weight: 700;
    margin-bottom: .5rem;
}
.text-user {
    color: #6c757d;
    text-decoration: line-through;
    font-size: 1rem;
    margin-right: .6rem;
}
.text-reseller {
    color: #000;
    font-weight: 700;
    font-size: 1.5rem;
}

/* 🏬 GUDANG */
.warehouse-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 1rem;
}
.warehouse-card .card-header {
    font-weight: 600;
    background: #f8f9fa;
}
.warehouse-card .table {
    font-size: .9rem;
}

/* 🖼️ CAROUSEL IMAGE */
.carousel-item img {
    border-radius: 12px;
}

/* 💎 RESPONSIVE TUNING */
@media (max-width: 1199.98px) {
    .detail-header h3 { font-size: 1.2rem; }
}

@media (max-width: 991.98px) {
    .price-box { padding: 1rem; }
    .text-reseller { font-size: 1.3rem; }
    .text-user { font-size: 0.95rem; }
    .detail-header {
        padding: 15px 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}

@media (max-width: 767.98px) {
    .container-fluid {
        padding: 0 12px;
    }
    .detail-header {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }
    .detail-header h3 {
        font-size: 1.1rem;
        width: 100%;
        margin-bottom: 10px;
    }
    .detail-header a.btn {
        width: 100%;
        border-radius: 8px;
    }

    .card.p-4 {
        padding: 1rem !important;
    }
    .text-reseller { 
        font-size: 1.1rem; 
        display: block;
    }
    .text-user { 
        font-size: .85rem; 
        display: block;
        margin-bottom: 4px;
    }

    .fw-bold.text-primary {
        font-size: 1rem;
        text-align: center;
    }

    /* Gambar ke atas, harga ke bawah */
    .row.g-4.align-items-center {
        flex-direction: column;
    }

    /* Tabel gudang agar tidak melebar */
    .warehouse-card .table {
        font-size: .8rem;
    }
    .warehouse-card .card-header {
        font-size: .9rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .price-box { padding: 0.75rem; }
    .price-box .title { font-size: .9rem; }
    .text-reseller { font-size: 1rem; }
    .text-user { font-size: .8rem; }
    .fw-semibold { font-size: .85rem; }
    .card-footer strong { font-size: .9rem; }
    .badge.bg-secondary {
        font-size: 0.85rem !important;
        padding: 4px 8px;
    }
}

/* Warehouse Dashboard Styles */
.warehouse-section {
    padding: 1.5rem 0;
}

.warehouse-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
    overflow: hidden;
    background: white;
}

.warehouse-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.warehouse-card .card-header {
    background: linear-gradient(135deg, #2c3e50, #4a6572);
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 1.25rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.warehouse-card .card-header::before {
    content: "📦";
    font-size: 1.2rem;
}

.warehouse-card .card-body {
    padding: 0;
}

.warehouse-card .table {
    margin-bottom: 0;
    font-size: 0.9rem;
}

.warehouse-card .table thead {
    background-color: #f8f9fa;
}

.warehouse-card .table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.warehouse-card .table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-color: #f1f3f4;
}

.warehouse-card .table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.05);
}

.warehouse-card .table tbody tr:last-child td {
    border-bottom: 1px solid #dee2e6;
}

.total-badge {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.95rem;
    box-shadow: 0 2px 4px rgba(39, 174, 96, 0.2);
}

.warehouse-card .bg-light {
    background-color: #f8f9fa !important;
    border-top: 1px solid #e9ecef !important;
    padding: 1rem 1.25rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .warehouse-card .card-header {
        font-size: 1rem;
        padding: 0.875rem 1rem;
    }
    
    .warehouse-card .table th,
    .warehouse-card .table td {
        padding: 0.5rem 0.75rem;
    }
    
    .total-badge {
        padding: 0.3rem 0.8rem;
        font-size: 0.9rem;
    }
}

/* Custom colors for different warehouse types */
#warehouse_store .card-header {
    background: linear-gradient(135deg, #0b7710, #0aa10a);
}

#warehouse_tsc .card-header {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
}

#warehouse_reseller .card-header {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

#warehouse_konsinyasi .card-header {
    background: linear-gradient(135deg, #f39c12, #d35400);
}

#warehouse_panda .card-header {
    background: linear-gradient(135deg, #bc1a1a, #a01616);
}

/* Animation for card appearance */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.warehouse-card {
    animation: fadeInUp 0.5s ease forwards;
}

/* Stagger animation for multiple cards */
.warehouse-card:nth-child(1) { animation-delay: 0.1s; }
.warehouse-card:nth-child(2) { animation-delay: 0.2s; }
.warehouse-card:nth-child(3) { animation-delay: 0.3s; }
.warehouse-card:nth-child(4) { animation-delay: 0.4s; }
.warehouse-card:nth-child(5) { animation-delay: 0.5s; }

</style>

<div class="container-fluid">

    {{-- 🌈 HEADER --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3><i class="bi bi-box-seam me-2"></i> Detail Produk</h3>
        <button class="btn btn-light fw-semibold me-2" onclick="history.back()">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
        </button>
    </div>

    {{-- 💰 HARGA & GAMBAR --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row g-4 align-items-center">
            {{-- 🖼️ Gambar --}}
            <div class="col-md-4 text-center">
                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @forelse ($images as $index => $file)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img 
                                    src="{{ route('proxy.image', ['file' => $file, 'session' => $session]) }}"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                    onerror="this.onerror=null; this.src='{{ asset('images/noimage.jpg') }}';"
                                >
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <img 
                                    src="{{ asset('images/noimage.jpg') }}" 
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                >
                            </div>
                        @endforelse

                        <button class="carousel-control-prev" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Sebelumnya</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#itemImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Selanjutnya</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 💰 Harga --}}
            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-md-10">
                        <h4 class="fw-bold text-primary mb-2 mb-md-0 me-3">{{ $item['name'] }}</h4>
                    </div>
                    <div class="col-md-2 text-md-end text-center">
                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3 fs-6">
                            <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="price-box">
                    <div class="title">
                        <i class="bi bi-cash-stack me-1"></i> Harga
                    </div>
                    <hr class="my-1">
                    @if(!empty($unitPrices) && ($hasMultiUnitPrices ?? false))
                        {{-- 🔹 ADA LEBIH DARI 1 UNIT → TAMPIL PER UNIT --}}
                        @foreach($unitPrices as $unitName => $p)
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-semibold">
                                    {{ strtoupper($unitName) }}
                                </div>
                                <div class="text-end">
                                    @if(isset($p['user']) && $p['user'] > 0)
                                        <div class="text-muted" style="text-decoration: line-through;">
                                            Rp {{ number_format($p['user'], 0, ',', '.') }}
                                        </div>
                                    @endif

                                    @if(isset($p['reseller']) && $p['reseller'] > 0)
                                        <div class="fw-bold">
                                            Rp {{ number_format($p['reseller'], 0, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    @else
                        {{-- 🔹 TIDAK ADA / CUMA 1 UNITPRICE → PAKAI HARGA UTAMA --}}
                        <div class="d-flex flex-wrap align-items-baseline mb-3">
                            @if(isset($prices['user']) && $prices['user'] > 0)
                                <span class="text-user d-inline-block me-2">
                                    Rp {{ number_format($prices['user'], 0, ',', '.') }}
                                </span>
                            @endif

                            <span class="text-reseller d-inline-block">
                                Rp {{ number_format($prices['reseller'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="small text-muted">Garansi</div>
                            <div class="fw-semibold">{{ $item['charField7'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🏬 GUDANG --}}
    @foreach([
        'store' => 'Store',
        'tsc' => 'TSC',
        'reseller' => 'Reseller',
        'konsinyasi' => 'Konsinyasi',
        'panda' => 'Panda'
    ] as $key => $label)
        @php
            $var = 'warehouses' . ucfirst($key);
        @endphp
        @if(isset($$var) && count($$var) > 0)
            <div class="warehouse-card card" id="warehouse_{{ $key }}">
                <div class="card-header fw-bold">{{ $label }}</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Lokasi</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satuan</th>
                        </tr>
                        </thead>
                        <tbody>
                            @include("partials.table" . ucfirst($key), [ $var => $$var ])
                        </tbody>
                    </table>
                </div>

                {{-- ===== TOTAL DI BAWAH CARD ===== --}}
                <div class="px-3 py-2 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>
                    <span class="total-badge" id="total_{{ $key }}">
                        {{ number_format($$var->sum('balance'), 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @endif
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const itemId = "{{ $item['id'] }}";
    const session = "{{ $session }}";

    /* ============================================================
    7. UPDATE TOTAL
    ============================================================ */
    function updateTotals() {
        const groups = ["store", "tsc", "reseller", "konsinyasi", "panda"]; // ← HAPUS TITIK DI SINI

        groups.forEach(group => {
            let total = 0;
            const rows = document.querySelectorAll(`#warehouse_${group} tbody tr`);

            rows.forEach(row => {
                if (row.style.display !== "none") {
                    const tdStock = row.querySelector('[id^="stock_"]');
                    if (tdStock) {
                        total += parseFloat(tdStock.textContent) || 0;
                    }
                }
            });

            const totalSpan = document.getElementById(`total_${group}`);
            if (totalSpan) totalSpan.textContent = total.toLocaleString("id-ID");
        });
    }

    /* ============================================================
    6. UPDATE VISIBILITAS ROW (hide jika stok 0)
    ============================================================ */
    function updateRowVisibility(tdElement, newStock) {
        const tr = tdElement.closest("tr");
        if (!tr) return;

        tr.style.display = newStock <= 0 ? "none" : "";
    }

    /* ============================================================
    8. REALTIME STOCK UPDATE
    ============================================================ */
    function updateRealtimeStock() {
        return new Promise(resolve => {
            const rows = document.querySelectorAll('[id^="stock_"]');
            let done = 0;

            if (rows.length === 0) return resolve();

            rows.forEach(row => {
                const warehouseName = row.closest("tr").children[0].innerText.trim();
                const branchName = document.querySelector("#branchSelect")?.value || '';

                fetch(`/twincomgo/ajax/warehouse-stock?id={{ $item['id'] }}&warehouse=${encodeURIComponent(warehouseName)}&branchName=${encodeURIComponent(branchName)}`)
                    .then(res => res.json())
                    .then(json => {
                        if (json.stock !== undefined) {
                            row.textContent = json.stock;
                            updateRowVisibility(row, json.stock);
                        }
                    })
                    .finally(() => {
                        done++;
                        if (done === rows.length) resolve();
                    });
            });
        });
    }

    /* ============================================================
    9. JALANKAN SEKALI + INTERVAL ANTI-DUPLIKAT
    ============================================================ */
    // 🛑 CEGAH INTERVAL BERLIPAT-LIPAT
    if (!window.stockUpdaterRunning) {
        window.stockUpdaterRunning = true;

        // First run
        updateRealtimeStock().then(updateTotals);

        // Every 120 detik (2 menit)
        setInterval(() => {
            updateRealtimeStock().then(updateTotals);
        }, 120000);
    }

    /* ============================================================
    10. UPDATE VISIBILITAS GUDANG (jika tidak ada stok sama sekali)
    ============================================================ */
    function updateWarehouseVisibility() {
        const groups = ["store", "tsc", "reseller", "konsinyasi", "panda"];

        groups.forEach(group => {
            const card = document.getElementById(`warehouse_${group}`);
            if (!card) return;

            const rows = card.querySelectorAll("tbody tr");
            const visibleRows = Array.from(rows).filter(
                row => row.style.display !== "none"
            );

            // Jika tidak ada row yang tampil → sembunyikan card
            card.style.display = visibleRows.length === 0 ? "none" : "block";
        });
    }

    // Panggil juga fungsi visibility setelah update
    if (window.stockUpdaterRunning) {
        updateRealtimeStock().then(() => {
            updateTotals();
            updateWarehouseVisibility();
        });
    }

});
</script>
@endsection
