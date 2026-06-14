<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $query }} - Hasil Pencarian STKI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8fafc; }
        .search-bar-mini {
            background: linear-gradient(135deg, #0f1117, #1a56db);
            padding: 16px 24px;
        }
        .mini-form { max-width: 680px; }
        .mini-input {
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
            color: #fff; border-radius: 8px 0 0 8px; padding: 10px 16px;
        }
        .mini-input::placeholder { color: rgba(255,255,255,.5); }
        .mini-input:focus { background: rgba(255,255,255,.15); outline: none; box-shadow: none; border-color: rgba(255,255,255,.4); color: #fff; }
        .result-card {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 10px; padding: 20px;
            transition: box-shadow .15s;
        }
        .result-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
        .result-rank {
            width: 28px; height: 28px; background: #eff6ff;
            border-radius: 6px; display: flex; align-items: center;
            justify-content: center; font-size: .75rem; font-weight: 700;
            color: #1a56db; flex-shrink: 0;
        }
        .result-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; }
        .result-title:hover { color: #1a56db; }
        .score-bar { height: 4px; background: #e5e7eb; border-radius: 2px; }
        .score-fill { height: 100%; border-radius: 2px; background: linear-gradient(90deg, #1a56db, #3b82f6); }
        .keyword-tag {
            display: inline-block; background: #eff6ff; color: #1a56db;
            border-radius: 4px; padding: 2px 8px; font-size: .73rem; font-weight: 500;
        }
        mark { background: #fef08a; padding: 0 2px; border-radius: 2px; }
        .sidebar-info { position: sticky; top: 20px; }
    </style>
</head>
<body>
{{-- Mini search bar --}}
<div class="search-bar-mini">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('search.index') }}" class="text-white text-decoration-none">
            <i class="bi bi-search fs-5"></i>
        </a>
        <form action="{{ route('search.results') }}" method="GET" class="d-flex flex-grow-1 mini-form">
            <input type="text" name="q" class="form-control mini-input" value="{{ $query }}" required>
            <button type="submit" class="btn btn-warning px-4" style="border-radius:0 8px 8px 0;">
                <i class="bi bi-search"></i>
            </button>
        </form>
        @auth
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light d-none d-md-inline-flex">
            <i class="bi bi-grid-1x2"></i>
        </a>
        @endauth
    </div>
</div>

<div class="container-fluid py-4" style="max-width:1100px;">
    <div class="row g-4">

        {{-- ═══ HASIL PENCARIAN ═══ --}}
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="text-muted" style="font-size:.875rem;">
                        Menampilkan <strong>{{ $result_count }}</strong> hasil untuk
                        "<strong>{{ $query }}</strong>"
                        &nbsp;·&nbsp; {{ $execution_time }}ms
                    </span>
                </div>
            </div>

            @if($result_count === 0)
                <div class="text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">Tidak ditemukan hasil untuk "{{ $query }}"</h5>
                    <p class="text-muted small">Coba gunakan kata kunci lain atau lebih umum.</p>
                    @if(empty(\App\Models\Term::first()))
                        <div class="alert alert-warning mt-3 d-inline-block">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Index belum dibangun. <a href="{{ route('indexing.build') }}">Bangun index sekarang</a>.
                        </div>
                    @endif
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($results as $rank => $item)
                    @php $doc = $item['document']; @endphp
                    <div class="result-card">
                        <div class="d-flex gap-3">
                            <div class="result-rank">{{ $rank + 1 }}</div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <a href="{{ route('search.detail', [$doc, 'q' => $query]) }}"
                                       class="result-title text-decoration-none">
                                        {!! \App\Services\SearchService::class && false ? '' : $doc->title !!}
                                        {{ $doc->title }}
                                    </a>
                                    <div class="text-end ms-3 flex-shrink-0">
                                        <div class="fw-700 text-primary" style="font-size:.9rem;font-weight:700;">
                                            {{ number_format($item['score'] * 100, 1) }}%
                                        </div>
                                        <div style="font-size:.7rem;color:#9ca3af;">relevansi</div>
                                    </div>
                                </div>

                                <div class="score-bar mb-2">
                                    <div class="score-fill" style="width:{{ min($item['score_percent'], 100) }}%"></div>
                                </div>

                                <p class="text-muted mb-2" style="font-size:.875rem;line-height:1.6;">
                                    {!! $item['highlighted_text'] !!}...
                                </p>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    @if($doc->category)
                                        <span class="badge bg-light text-dark border" style="font-size:.7rem;">
                                            {{ $doc->category }}
                                        </span>
                                    @endif
                                    @if($doc->published_at)
                                        <span class="text-muted" style="font-size:.75rem;">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $doc->published_at->format('d M Y') }}
                                        </span>
                                    @endif
                                    @foreach($item['matched_terms'] as $term)
                                        <span class="keyword-tag">{{ $term }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══ SIDEBAR INFO ═══ --}}
        <div class="col-lg-4">
            <div class="sidebar-info">
                {{-- Query Info --}}
                <div class="card mb-3">
                    <div class="card-header" style="font-size:.875rem;">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Detail Query
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-2">
                            <small class="text-muted d-block">Query asli:</small>
                            <code style="font-size:.8rem;">{{ $query }}</code>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Setelah preprocessing:</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($query_tokens as $token)
                                    <span class="badge bg-primary" style="font-size:.7rem;">{{ $token }}</span>
                                @endforeach
                            </div>
                        </div>
                        @if(!empty($query_vector))
                        <div>
                            <small class="text-muted d-block mb-1">Vektor TF-IDF Query:</small>
                            @foreach(array_slice($query_vector, 0, 5, true) as $term => $weight)
                            <div class="d-flex justify-content-between" style="font-size:.75rem;">
                                <span>{{ $term }}</span>
                                <span class="text-primary fw-600">{{ number_format($weight, 4) }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Preprocessing steps --}}
                @if(!empty($preprocess_steps))
                <div class="card">
                    <div class="card-header" style="font-size:.875rem;">
                        <i class="bi bi-cpu me-2 text-success"></i>Langkah Preprocessing
                    </div>
                    <div class="card-body p-3">
                        @php $steps = $preprocess_steps; @endphp
                        <div class="mb-2">
                            <small class="text-muted fw-600">1. Case Folding:</small>
                            <div style="font-size:.78rem;color:#374151;">{{ Str::limit($steps['case_folded'], 60) }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted fw-600">2. Tokenisasi:</small>
                            <div style="font-size:.78rem;color:#374151;">{{ count($steps['tokens']) }} token</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted fw-600">3. Stopword Removal:</small>
                            <div style="font-size:.78rem;color:#374151;">{{ count($steps['after_stopword']) }} token tersisa</div>
                        </div>
                        <div>
                            <small class="text-muted fw-600">4. Stemming:</small>
                            <div style="font-size:.78rem;color:#374151;">
                                {{ implode(', ', array_slice($steps['stemmed'], 0, 8)) }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>