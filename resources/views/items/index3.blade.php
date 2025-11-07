@extends(Auth::check() && Auth::user()->status === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Daftar Produk')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/item-index.css') }}">
@endpush

@section('content')
<div class="px-4 py-4">
    @include('items.partials.filter')

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h4 class="fw-bold text-white text-shadow-sm mb-2 mb-md-0">
            <i class="bi bi-box-seam me-2"></i> Daftar Produk
        </h4>

        <div class="d-flex gap-2">
            {{-- Tambahan tombol lain bisa di sini kalau mau (misal export Excel) --}}
            <a href="#" id="btn-export-pdf" class="btn btn-danger shadow-sm" data-export-url="{{ route('items.exportPdf') }}">
                <i class="bi bi-filetype-pdf me-1"></i> Preview PDF
            </a>
            <button id="header-total-items" class="btn btn-light btn-sm ms-2" style="width: 70px">
                {{ number_format($totalItems ?? 0) }}
            </button>
        </div>
    </div>

    <!-- 🔹 Kontainer hasil produk -->
    <div id="item-container">
        @include('items.partials.item-table', ['items' => $items])
    </div>

    <!-- 🔹 Kontainer pagination -->
    <div id="pagination-container" class="mt-3 d-flex justify-content-center">
        @include('items.partials.pagination', [
            'page' => $page,
            'pageCount' => $pageCount,
            'queryParams' => request()->except('page')
        ])
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/items/items-index.js') }}"></script>
@endpush
