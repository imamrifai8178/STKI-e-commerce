<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Berita - STKI Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #f8fafc;
            min-height: 100vh;
        }
        .search-hero {
            background: linear-gradient(135deg, #0f1117 0%, #1a56db 50%, #1e429f 100%);
            min-height: 100vh;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 40px 20px;
        }
        .hero-content { max-width: 700px; width: 100%; text-align: center; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
            border-radius: 100px; padding: 6px 16px;
            font-size: .8rem; color: rgba(255,255,255,.8); margin-bottom: 24px;
        }
        .hero-title {
            font-size: 3rem; font-weight: 800; color: #fff;
            line-height: 1.2; margin-bottom: 12px;
        }
        .hero-title span { color: #93c5fd; }
        .hero-subtitle {
            color: rgba(255,255,255,.65); font-size: 1rem; margin-bottom: 40px;
        }
        .search-form {
            background: #fff; border-radius: 16px;
            padding: 8px 8px 8px 20px;
            display: flex; gap: 8px; align-items: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }
        .search-input {
            flex: 1; border: none; outline: none;
            font-size: 1rem; color: #111827;
        }
        .search-input::placeholder { color: #9ca3af; }
        .search-btn {
            background: #1a56db; color: #fff; border: none;
            border-radius: 10px; padding: 12px 24px;
            font-weight: 600; font-size: .9rem; cursor: pointer;
            transition: background .2s;
            display: flex; align-items: center; gap: 8px;
        }
        .search-btn:hover { background: #1e429f; }
        .suggestions { margin-top: 20px; }
        .suggestion-chip {
            display: inline-block; background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 100px; padding: 6px 14px;
            font-size: .8rem; color: rgba(255,255,255,.8);
            margin: 4px; cursor: pointer; text-decoration: none;
            transition: all .2s;
        }
        .suggestion-chip:hover { background: rgba(255,255,255,.2); color: #fff; }
        .nav-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(15,17,23,.9); backdrop-filter: blur(10px);
            padding: 12px 24px;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 100;
        }
        .nav-brand { color: #fff; font-weight: 700; text-decoration: none; font-size: .95rem; }
        .stats-row {
            margin-top: 48px;
            display: flex; gap: 32px; justify-content: center;
        }
        .stat-item { text-align: center; }
        .stat-num { font-size: 1.5rem; font-weight: 700; color: #fff; }
        .stat-lbl { font-size: .75rem; color: rgba(255,255,255,.5); }
    </style>
</head>
<body>

<nav class="nav-bar">
    <a href="{{ route('search.index') }}" class="nav-brand">
        <i class="bi bi-search me-2"></i>STKI Berita
    </a>
    <div class="d-flex gap-2">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light">
                <i class="bi bi-grid-1x2 me-1"></i>Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Login</a>
        @endauth
    </div>
</nav>

<div class="search-hero" style="padding-top: 80px;">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="bi bi-stars"></i>
            Powered by TF-IDF & Cosine Similarity
        </div>

        <h1 class="hero-title">
            Cari <span>Berita Indonesia</span><br>dengan Cerdas
        </h1>

        <p class="hero-subtitle">
            Sistem Temu Kembali Informasi berbasis Vector Space Model.<br>
            Temukan artikel berita yang paling relevan dengan query Anda.
        </p>

        <form action="{{ route('search.results') }}" method="GET">
            <div class="search-form">
                <i class="bi bi-search text-muted"></i>
                <input type="text" name="q" class="search-input"
                       placeholder="Cari berita... (contoh: pemilu presiden 2024)"
                       value="{{ old('q') }}" required minlength="2">
                <button type="submit" class="search-btn">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <div class="suggestions">
            <small style="color:rgba(255,255,255,.4);">Populer: </small>
            @foreach(['pemilu 2024', 'ekonomi indonesia', 'kecerdasan buatan', 'bencana alam', 'pendidikan', 'korupsi'] as $s)
                <a href="{{ route('search.results', ['q' => $s]) }}" class="suggestion-chip">{{ $s }}</a>
            @endforeach
        </div>

        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-num">{{ \App\Models\Document::count() }}</div>
                <div class="stat-lbl">Artikel Berita</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">{{ \App\Models\Term::count() }}</div>
                <div class="stat-lbl">Kata dalam Index</div>
            </div>
            <div class="stat-item">
                <div class="stat-num">{{ \App\Models\SearchLog::count() }}</div>
                <div class="stat-lbl">Pencarian Dilakukan</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>