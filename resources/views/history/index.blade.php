@extends('layouts.app')

@section('title', 'History')
@section('page-title', 'History Pencarian')

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="card mb-4 border-0 shadow-sm"
     style="background: linear-gradient(135deg,#0f172a,#1a56db); color:white; border-radius:16px;">
    <div class="card-body d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-clock-history me-2"></i>History Pencarian
            </h4>
            <small class="text-white-50">
                Riwayat query pengguna pada sistem IR
            </small>
        </div>

        <div class="d-flex gap-2">

            {{-- BACK TO DASHBOARD --}}
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>

            {{-- CLEAR ALL --}}
            <form action="{{ route('history.clear') }}" method="POST"
                  onsubmit="return confirm('Hapus semua history?')">
                @csrf
                <button class="btn btn-warning btn-sm">
                    <i class="bi bi-trash me-1"></i> Clear All
                </button>
            </form>

        </div>

    </div>
</div>

{{-- ═══ TABLE ═══ --}}
<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between">
        <span><i class="bi bi-list me-2 text-primary"></i>Daftar History</span>
        <span class="badge bg-primary">{{ $logs->total() }} data</span>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Query</th>
                        <th>Hasil</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <a href="{{ route('search.results', ['q' => $log->query]) }}">
                                {{ $log->query }}
                            </a>
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ $log->result_count }}
                            </span>
                        </td>

                        <td class="text-muted small">
                            {{ $log->created_at->diffForHumans() }}
                        </td>

                        <td>
                            <form action="{{ route('history.destroy', $log) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Belum ada history pencarian
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>

{{-- ═══ PAGINATION ═══ --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $logs->links() }}
</div>

@endsection