@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- ═══ STAT CARDS ═══ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($stats['total_documents']) }}</div>
                    <div class="stat-label">Total Dokumen</div>
                </div>
                <div class="stat-icon" style="background:#eff6ff;">
                    <i class="bi bi-file-text" style="color:#1a56db;"></i>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i>{{ $stats['indexed_docs'] }} terindeks</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($stats['total_queries']) }}</div>
                    <div class="stat-label">Total Pencarian</div>
                </div>
                <div class="stat-icon" style="background:#f0fdf4;">
                    <i class="bi bi-search" style="color:#16a34a;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($stats['total_terms']) }}</div>
                    <div class="stat-label">Vocabulary Size</div>
                </div>
                <div class="stat-icon" style="background:#fefce8;">
                    <i class="bi bi-book" style="color:#ca8a04;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ number_format($stats['avg_precision'] * 100, 1) }}%</div>
                    <div class="stat-label">Avg Precision</div>
                </div>
                <div class="stat-icon" style="background:#fdf4ff;">
                    <i class="bi bi-bar-chart" style="color:#9333ea;"></i>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-muted">Recall: {{ number_format($stats['avg_recall'] * 100, 1) }}%</small>
            </div>
        </div>
    </div>
</div>

{{-- ═══ CHARTS ROW ═══ --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Aktivitas Pencarian (7 Hari)</span>
            </div>
            <div class="card-body">
                <canvas id="searchActivityChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Kategori Dokumen
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TABLES ROW ═══ --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-fire me-2 text-danger"></i>Query Paling Sering Dicari
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Query</th><th>Jumlah</th></tr></thead>
                    <tbody>
                        @forelse($topQueries as $i => $q)
                        <tr>
                            <td class="ps-3">
                                @if($i < 3)
                                    <span class="badge" style="background:{{ ['#f59e0b','#9ca3af','#cd7f32'][$i] }};">{{ $i+1 }}</span>
                                @else
                                    <span class="text-muted">{{ $i+1 }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('search.results', ['q' => $q->query]) }}" class="text-decoration-none">
                                    {{ $q->query }}
                                </a>
                            </td>
                            <td><span class="badge bg-primary rounded-pill">{{ $q->count }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-primary"></i>Pencarian Terbaru</span>
                <a href="{{ route('history.index') }}" class="btn btn-sm btn-outline-primary">Semua</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Query</th><th>Hasil</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse($recentSearches as $log)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('search.results', ['q' => $log->query]) }}" class="text-decoration-none">
                                    {{ Str::limit($log->query, 30) }}
                                </a>
                            </td>
                            <td><span class="badge bg-secondary">{{ $log->result_count }}</span></td>
                            <td class="text-muted" style="font-size:.75rem;">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada pencarian</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══ QUICK ACTIONS ═══ --}}
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Aksi Cepat</div>
            <div class="card-body d-flex flex-wrap gap-2">
                <a href="{{ route('documents.import.form') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-upload me-1"></i> Import Dataset
                </a>
                <a href="{{ route('indexing.build') }}" class="btn btn-outline-success btn-sm"
                   onclick="return confirm('Rebuild index untuk semua dokumen?')">
                    <i class="bi bi-gear me-1"></i> Rebuild Index
                </a>
                <a href="{{ route('evaluation.run') }}" class="btn btn-outline-warning btn-sm"
                   onclick="return confirm('Jalankan evaluasi sistem?')">
                    <i class="bi bi-bar-chart me-1"></i> Jalankan Evaluasi
                </a>
                <a href="{{ route('search.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Cari Berita
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Chart aktivitas pencarian
const actCtx = document.getElementById('searchActivityChart').getContext('2d');
new Chart(actCtx, {
    type: 'line',
    data: {
        labels: @json($activityLabels),
        datasets: [{
            label: 'Jumlah Pencarian',
            data: @json($activityData),
            borderColor: '#1a56db',
            backgroundColor: 'rgba(26,86,219,.08)',
            fill: true, tension: .4, pointRadius: 4,
            pointBackgroundColor: '#1a56db',
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
            x: { grid: { display: false } }
        }
    }
});

// Chart kategori
const catCtx = document.getElementById('categoryChart').getContext('2d');
const categories = @json($categories);
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: categories.map(c => c.category || 'Umum'),
        datasets: [{
            data: categories.map(c => c.count),
            backgroundColor: ['#1a56db','#16a34a','#ca8a04','#9333ea','#dc2626','#0891b2','#d97706'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } }
    }
});
</script>
@endpush