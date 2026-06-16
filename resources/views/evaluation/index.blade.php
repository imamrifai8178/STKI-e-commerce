@extends('layouts.app')

@section('title', 'Evaluation')
@section('page-title', 'Evaluation')

@section('content')

{{-- ═══ HEADER ═══ --}}
<div class="card mb-4 border-0 shadow-sm"
     style="background: linear-gradient(135deg,#9333ea,#1a56db); color:white; border-radius:16px;">
    <div class="card-body d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-bar-chart me-2"></i>Evaluasi Sistem
            </h4>
            <small class="text-white-50">
                Precision, Recall, F1-Score, MAP, NDCG
            </small>
        </div>

        <div class="d-flex gap-2">

            {{-- 🔙 BACK TO DASHBOARD --}}
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>

            {{-- RUN EVALUATION --}}
            <form action="{{ route('evaluation.run') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-warning btn-sm"
            onclick="return confirm('Jalankan evaluasi sistem?')">
        <i class="bi bi-play-circle me-1"></i> Run Evaluation
    </button>
</form>

        </div>
    </div>
</div>

{{-- ═══ METRICS CARDS ═══ --}}
<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ number_format($averages['precision'], 3) }}</h4>
                <small>Precision</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="text-success">{{ number_format($averages['recall'], 3) }}</h4>
                <small>Recall</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="text-warning">{{ number_format($averages['f1']) }}</h4>
                <small>F1 Score</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="text-danger">{{ number_format($averages['ndcg'], 3) }}</h4>
                <small>NDCG</small>
            </div>
        </div>
    </div>

</div>

{{-- ═══ TABLE EVALUATION ═══ --}}
<div class="card shadow-sm border-0">

    <div class="card-header bg-white">
        <i class="bi bi-table me-2 text-primary"></i>Detail Evaluasi Query
    </div>

    <div class="card-body p-0">

        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Query</th>
                    <th>Precision</th>
                    <th>Recall</th>
                    <th>F1</th>
                    <th>NDCG</th>
                </tr>
            </thead>

            <tbody>
                @forelse($evaluations as $eval)
                <tr>
                    <td>{{ $eval->query }}</td>
                    <td>{{ number_format($eval->precision, 3) }}</td>
                    <td>{{ number_format($eval->recall, 3) }}</td>
                    <td>{{ number_format($eval->f1_score, 3) }}</td>
                    <td>{{ number_format($eval->ndcg_score, 3) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Belum ada data evaluasi
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection