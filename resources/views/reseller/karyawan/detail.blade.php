@extends('layouts.app')

@section('content')
<style>
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
    letter-spacing: 0.5px;
}

.branch-loader {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #198754;
    margin-top: 6px;
    transition: opacity 0.3s ease;
}

.branch-select-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}
.branch-spinner {
    position: absolute;
    top: 50%;
    right: 30px;
    transform: translateY(-50%);
    z-index: 3;
    display: none;
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
    transition: all 0.3s ease;
}
.warehouse-card .card-header {
    background: #f8f9fa;
    font-weight: 600;
}

.warehouse-card[style*="display: none"] {
  opacity: 0;
  transform: scale(0.98);
}
</style>

<div class="container-fluid">

    {{-- 🌈 HEADER --}}
    <div class="detail-header d-flex justify-content-between align-items-center flex-wrap">
        <h3><i class="bi bi-box-seam me-2"></i> Detail Item</h3>        
        <div class="text-end mb-3">
            <a href="#" id="btn-back" class="btn btn-light fw-semibold">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </a>
            <a href="#" id="btn-export-pdf" class="btn btn-danger fw-semibold">
                <i class="bi bi-filetype-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- 🎚️ FILTER --}}
    <div class="filter-card">
        <div class="row g-3 align-items-end">
            {{-- 🏢 Select Cabang --}}
            <div class="col-md-4">
                <label for="branchName" class="form-label fw-semibold mb-2">Pilih Harga Cabang</label>
                <div class="d-flex align-items-center">
                    <select id="branchSelect" class="form-select shadow-sm me-2" style="border-radius: 20px; min-width: 250px;">
                        <option value="">Semua Cabang</option>
                    </select>
                    <div id="priceSpinner" class="spinner-border spinner-border-sm text-success" role="status" style="display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            {{-- 💰 Jenis Harga --}}
            <div class="col-md-4">
                <label for="priceType" class="form-label fw-semibold mb-2">Tampilkan Harga</label>
                <select id="priceType" class="form-select shadow-sm">
                    <option value="all" selected>Semua Harga</option>
                    <option value="user">User</option>
                    <option value="reseller">Reseller</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="warehouseFilter" class="form-label fw-semibold">Pilih lokasi</label>
                <select id="warehouseFilter" class="form-select shadow-sm" multiple style="border-radius:20px;">
                    <option value="store">Store</option>
                    <option value="tsc">TSC</option>
                    <option value="reseller">Reseller</option>
                    <option value="konsinyasi">Konsinyasi</option>
                    <option value="panda">Panda</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 💰 HARGA --}}
    <div class="card p-4 mb-4 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-md-4 text-center">
                <div id="itemImageCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @forelse ($images as $index => $file)
                            @php
                                $imageUrl = $file ? route('proxy.image', ['fileName' => $file, 'session' => $session]) : asset('/images/noimage.jpg');
                            @endphp
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="Gambar {{ $index + 1 }}"
                                    class="d-block w-100 img-fluid rounded shadow-sm"
                                    style="max-height: 300px; object-fit: contain;"
                                    onerror="this.onerror=null; this.src='{{ asset('/images/noimage.jpg') }}';"
                                >
                            </div>
                        @empty
                            <div class="carousel-item active">
                                <img
                                    src="{{ asset('/images/noimage.jpg') }}"
                                    alt="Gambar default"
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

            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-md-10">
                        <h4 class="fw-bold text-primary mb-2 mb-md-0 me-3">{{ $item['name'] }}</h4>
                    </div>
                    <div class="col-md-2">
                        <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2 rounded-3 fs-5">
                            <i class="bi bi-upc-scan me-1"></i> {{ $item['no'] ?? '-' }}
                        </span>
                    </div>
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
    @foreach (['Store', 'Tsc', 'Reseller', 'Konsinyasi', 'Panda'] as $type)
        @php
            $var = 'warehouses' . $type;
            $total = 'total' . $type;
        @endphp
        @if(!empty($$var) && count($$var) > 0)
            <div class="card warehouse-card" id="warehouse{{ $type }}">
                <div class="card-header">{{ $type }}</div>
                <div class="card-body p-2">
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
    const spinner = document.getElementById('priceSpinner');

    const tom = new TomSelect("#branchSelect", {
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Semua Cabang',
        preload: true,
        plugins: ['clear_button'], // ❌ tombol hapus pilihan
        clear_button: { title: 'Hapus pilihan' },
        load: function(query, callback) {
            if (isLoading || branchPage > totalBranchPage) return callback();
            isLoading = true;
            spinner.style.display = 'inline-block';

            fetch(`/branches?page=${branchPage}`)
                .then(res => res.json())
                .then(json => {
                    json.data.forEach(b => b.name = b.name || 'Tanpa Nama');
                    callback(json.data);
                    totalBranchPage = json.totalPage;
                    branchPage++;
                })
                .catch(() => callback())
                .finally(() => {
                    isLoading = false;
                    spinner.style.display = 'none';
                });
        },
        render: {
            option: function(item) {
                return `<div class="py-1 px-2">${item.name}</div>`;
            }
        }
    });

    const itemId = "{{ $item['id'] }}";
    tom.on('change', function(value) {
    const branchName = value || '';
    spinner.style.display = 'inline-block';

    fetch(`/karyawan/${itemId}/price?branchName=${encodeURIComponent(branchName)}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('userPrice').textContent = formatRupiah(data.user);
            document.getElementById('resellerPrice').textContent = formatRupiah(data.reseller);
        })
        .catch(err => console.error(err))
        .finally(() => {
            spinner.style.display = 'none';
        });
    });

    function formatRupiah(number) {
        return 'Rp ' + (parseFloat(number || 0))
            .toFixed(0)
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    const warehouseSelect = new TomSelect('#warehouseFilter', {
        plugins: ['remove_button'], // tampilkan tanda silang di setiap tag
        placeholder: 'Semua Lokasi.',
        maxItems: null, // biar bisa pilih banyak
        persist: false,
        create: false,
        hideSelected: true,
    });

    const warehouseCards = {
        store: document.getElementById('warehouseStore'),
        tsc: document.getElementById('warehouseTsc'),
        reseller: document.getElementById('warehouseReseller'),
        konsinyasi: document.getElementById('warehouseKonsinyasi'),
        panda: document.getElementById('warehousePanda')
    };

    function updateWarehouseDisplay(selected) {
        Object.entries(warehouseCards).forEach(([key, card]) => {
            if (!card) return;
            if (selected.length === 0 || selected.includes(key)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Jalankan setiap kali user ubah pilihan
    warehouseSelect.on('change', function() {
        updateWarehouseDisplay(warehouseSelect.getValue());
    });

    const backBtn = document.getElementById('btn-back');
    const lastUrl = localStorage.getItem('last_item_list_url') || "{{ route('items.index') }}";

    backBtn.addEventListener('click', e => {
        e.preventDefault();
        window.location.href = lastUrl;
    });
});


// 🔹 Filter tampilan harga
const priceType = document.getElementById('priceType');
const userBox = document.querySelector('.price-user').closest('.col-md-6');
const resellerBox = document.querySelector('.price-reseller').closest('.col-md-6');

priceType.addEventListener('change', function() {
    const value = this.value;

    if (value === 'user') {
        userBox.style.display = 'block';
        resellerBox.style.display = 'none';
    } else if (value === 'reseller') {
        userBox.style.display = 'none';
        resellerBox.style.display = 'block';
    } else {
        userBox.style.display = 'block';
        resellerBox.style.display = 'block';
    }
});

// === 📄 Export PDF Detail (otomatis sesuai filter aktif) ===
document.addEventListener('click', function(e) {
    const btn = e.target.closest('#btn-export-pdf');
    if (!btn) return;
    e.preventDefault();

    const itemId = "{{ $item['id'] }}";
    const encryptedId = "{{ request()->route('encrypted') }}"; // ambil dari URL

    // Ambil semua filter aktif
    const branchSelect = document.querySelector('#branchSelect');
    const priceTypeSelect = document.querySelector('#priceType');
    const warehouseSelect = document.querySelector('#warehouseFilter');

    const branchName = branchSelect ? branchSelect.value : '';
    const priceType = priceTypeSelect ? priceTypeSelect.value : 'all';
    const warehouses = warehouseSelect ? warehouseSelect.tomselect.getValue() : [];

    // Susun URL
    const params = new URLSearchParams();
    if (branchName) params.append('branchName', branchName);
    if (priceType) params.append('priceType', priceType);
    warehouses.forEach(w => params.append('warehouses[]', w));

    const pdfUrl = `/karyawan/${encryptedId}/export-pdf?${params.toString()}`;

    // Buka di tab baru
    window.open(pdfUrl, '_blank');
});


</script>
@endsection
