{{-- 🔹 Tombol Export PDF --}}
<div class="desktop-table">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Kode</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center" style="width: 10%">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr onclick="window.location='{{ route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]) }}'" style="cursor: pointer;">
                                <td class="text-center" style="padding: 12px;"><span>{{ $item['no'] ?? '-' }}</span></td>
                                <td>
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    @if(!empty($item['itemCategory']['name']))
                                        <small class="text-muted">{{ $item['itemCategory']['name'] }}</small>
                                    @endif
                                </td>
                                <td class="td-harga">
                                    <div class="harga-grid">
                                        <span class="harga-rp">Rp</span>
                                        <span class="harga-nominal">
                                            {{ is_numeric($item['price'] ?? null) ? number_format($item['price'], 0, ',', '.') : '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">{{ $item['availableToSell'] ?? 0 }}</td>
                                <td class="text-center">
                                    @php
                                        $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                    @endphp
                                    <span>{{ $unit }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                                    Tidak ada produk ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 📱 MOBILE -->
<div class="mobile-list">
    <div class="row g-2">
        @forelse($items as $item)
            <div class="col-12">
                <div class="product-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="product-title">{{ $item['name'] }}</div>
                        <div class="harga-grid">
                            <span class="harga-rp">Rp</span>
                            <span class="harga-nominal">{{ number_format($item['price'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if(!empty($item['itemCategory']['name']))
                        <div class="product-meta mt-1">{{ $item['itemCategory']['name'] }}</div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="product-meta">
                            Stok: <strong>{{ $item['availableToSell'] ?? 0 }}</strong> /
                            <strong>
                                @php
                                    $unit = preg_replace('/^[\d.,]+\s*(?=PCS\b)/i', '', trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-')));
                                @endphp
                                {{ $unit }}
                            </strong>
                        </div>
                        <a href="{{ route('karyawan.show', ['encrypted' => Hashids::encode($item['id'])]) }}" class="btn btn-success btn-sm btn-detail">Detail</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                    Tidak ada produk ditemukan.
                </div>
            </div>
        @endforelse
    </div>
</div>
