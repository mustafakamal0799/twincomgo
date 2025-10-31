@extends('layouts.admin')

@section('content')
<style>
    /* ===== Header ===== */
    .page-header {
        background: linear-gradient(90deg, #1f2937, #374151);
        color: white;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .page-header h4 {
        font-weight: 700;
        margin: 0;
    }

    .page-header .btn {
        transition: all 0.2s ease;
    }

    .page-header .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.4);
    }

    /* ===== Table Section ===== */
    .card {
        border: none;
        border-radius: 12px;
    }

    .card-body {
        padding: 0;
    }

    .table-wrapper {
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    thead th {
        background-color: #1f2937;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    tbody tr:hover {
        background-color: #f1f5f9;
        transition: 0.2s;
    }

    .badge {
        font-size: 12px;
        padding: 6px 10px;
    }

    /* ===== Scroll Area ===== */
    .table-scroll-container {
        max-height: calc(100vh - 320px);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    .table-scroll-container::-webkit-scrollbar {
        width: 8px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* ===== Pagination Info ===== */
    .pagination {
        justify-content: center;
    }

    .pagination .page-item.active .page-link {
        background-color: #1f2937;
        border-color: #1f2937;
    }

    .info-text {
        color: #6b7280;
        font-size: 14px;
    }

    /* ===== Alerts ===== */
    .alert {
        border-radius: 10px;
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            text-align: center;
        }

        .page-header h4 {
            margin-bottom: 10px;
        }

        .table-scroll-container {
            max-height: none;
        }

        table th, table td {
            font-size: 12px;
        }

        .page-header .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center page-header mb-4 flex-wrap gap-3">
        <h4><i class="bi bi-diagram-3 me-2"></i> Accurate Accounts (Kepala)</h4>
        <a href="{{ route('aa.create') }}" class="btn btn-light text-dark fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Tambah Kepala
        </a>
    </div>

    {{-- Alert sukses --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm table-wrapper">
        <div class="table-scroll-container">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Label</th>
                        <th>Company DB</th>
                        <th>Status</th>
                        <th>Expires At</th>
                        <th>Session</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $r->label ?? '—' }}</td>
                            <td>{{ $r->company_db_id ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $r->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ $r->expires_at ? $r->expires_at->format('Y-m-d H:i') : '—' }}
                            </td>
                            <td class="text-center">
                                {{ $r->session_id ? \Illuminate\Support\Str::limit($r->session_id, 10) : '—' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('aa.edit', $r->id) }}" 
                                   class="btn btn-sm btn-outline-primary me-1" 
                                   data-bs-toggle="tooltip" title="Edit Data">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('aa.destroy', $r->id) }}" 
                                      class="d-inline"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Belum ada data kepala
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination & Info --}}
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
        <p class="info-text mb-2 mb-md-0">
            Menampilkan <strong>{{ $rows->firstItem() }}</strong> –
            <strong>{{ $rows->lastItem() }}</strong> dari
            <strong>{{ $rows->total() }}</strong> hasil
        </p>
        <div>
            {{ $rows->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection
