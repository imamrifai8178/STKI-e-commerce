@extends('layouts.app')

@section('title', 'Preprocessing')
@section('page-title', 'Preprocessing')

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="card mb-4 border-0 shadow-sm"
     style="background: linear-gradient(135deg,#1a56db,#0f172a); color:white; border-radius:16px;">
    <div class="card-body d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-cpu me-2"></i>Preprocessing Dokumen
            </h4>
            <small class="text-white-50">
                Tahap text mining: case folding, tokenizing, stopword removal, stemming
            </small>
        </div>

        <div class="d-flex gap-2">

            {{-- 🔙 BACK TO DASHBOARD --}}
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>

            <a href="{{ route('search.index') }}" class="btn btn-warning btn-sm">
                <i class="bi bi-search me-1"></i> Search
            </a>

        </div>
    </div>
</div>

{{-- ═══ TABLE CARD ═══ --}}
<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-table me-2 text-primary"></i>Daftar Dokumen
        </span>

        <span class="badge bg-primary">
            {{ $documents->total() }} dokumen
        </span>
    </div>

    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Judul Dokumen</th>
                        <th style="width:150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($documents as $doc)
                    <tr>
                        <td class="fw-semibold text-muted">
                            #{{ $doc->id }}
                        </td>

                        <td>
                            <div class="fw-semibold text-dark">
                                {{ $doc->title }}
                            </div>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('preprocessing.show', $doc) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            Tidak ada dokumen
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
    {{ $documents->links() }}
</div>

@endsection