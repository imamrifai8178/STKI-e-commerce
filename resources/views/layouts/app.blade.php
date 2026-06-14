<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'STKI') - Sistem Temu Kembali Informasi</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    {{-- DataTables --}}
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary:       #1a56db;
            --primary-dark:  #1e429f;
            --sidebar-bg:    #0f1117;
            --sidebar-text:  #c9d1d9;
            --sidebar-hover: #21262d;
            --card-shadow:   0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);
        }

        * { font-family: 'Inter', sans-serif; }

        body { background: #f8fafc; min-height: 100vh; }

        /* ─── SIDEBAR ─── */
        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            overflow-y: auto;
            transition: transform .3s ease;
            display: flex; flex-direction: column;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid #21262d;
        }

        .sidebar-brand .brand-title {
            font-size: 1.1rem; font-weight: 700;
            color: #fff; letter-spacing: -.3px;
        }

        .sidebar-brand .brand-sub {
            font-size: .7rem; color: #6e7681;
            margin-top: 2px;
        }

        .sidebar-nav { padding: 12px 0; flex: 1; }

        .nav-section-title {
            font-size: .65rem; font-weight: 600;
            color: #6e7681; text-transform: uppercase;
            letter-spacing: .8px; padding: 12px 20px 4px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 20px;
            color: var(--sidebar-text);
            text-decoration: none; font-size: .875rem;
            border-left: 3px solid transparent;
            transition: all .15s;
        }

        .sidebar-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-link.active {
            background: rgba(26,86,219,.15);
            color: #6ea8fe;
            border-left-color: #1a56db;
        }

        .sidebar-link i { font-size: 1rem; width: 18px; text-align: center; }

        /* ─── TOPBAR ─── */
        #topbar {
            position: fixed; top: 0;
            left: var(--sidebar-width); right: 0;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center;
            padding: 0 24px;
            z-index: 999;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }

        /* ─── MAIN CONTENT ─── */
        #main {
            margin-left: var(--sidebar-width);
            padding-top: 60px;
            min-height: 100vh;
        }

        .page-content { padding: 28px 28px 40px; }

        /* ─── CARDS ─── */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 16px 20px;
            font-weight: 600;
        }

        /* ─── STAT CARDS ─── */
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: var(--card-shadow);
        }

        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        .stat-card .stat-value {
            font-size: 1.9rem; font-weight: 700;
            line-height: 1.1; color: #111827;
        }

        .stat-card .stat-label {
            font-size: .8rem; color: #6b7280;
            margin-top: 2px;
        }

        /* ─── BADGES ─── */
        .badge-category {
            font-size: .7rem; padding: 4px 8px; border-radius: 6px;
        }

        /* ─── SCORE BAR ─── */
        .score-bar-container { height: 6px; background: #f3f4f6; border-radius: 3px; }
        .score-bar { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #1a56db, #3b82f6); }

        /* ─── PREPROCESSING STEPS ─── */
        .step-badge {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700;
            background: var(--primary); color: #fff;
        }

        /* ─── HIGHLIGHT ─── */
        mark.bg-warning { border-radius: 3px; padding: 1px 2px; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #topbar { left: 0; }
            #main { margin-left: 0; }
        }

        /* ─── TABLE ─── */
        .table th { font-size: .8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
        .table td { font-size: .875rem; vertical-align: middle; }

        /* ─── ALERT ─── */
        .alert { border-radius: 8px; border: none; }

        /* ─── SEARCH HERO ─── */
        .search-hero {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            border-radius: 16px; padding: 48px 40px;
            color: #fff; text-align: center;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div style="background:#1a56db;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-search text-white" style="font-size:.9rem;"></i>
            </div>
            <div>
                <div class="brand-title">STKI</div>
                <div class="brand-sub">Temu Kembali Informasi</div>
            </div>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">Utama</div>

        <a href="{{ route('search.index') }}" class="sidebar-link {{ request()->routeIs('search.*') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Mesin Pencari
        </a>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <div class="nav-section-title">Dataset</div>

        <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') && !request()->routeIs('documents.import*') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i> Kelola Dokumen
        </a>
        <a href="{{ route('documents.import.form') }}" class="sidebar-link {{ request()->routeIs('documents.import*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Import Dataset
        </a>

        <div class="nav-section-title">Pemrosesan</div>

        <a href="{{ route('preprocessing.index') }}" class="sidebar-link {{ request()->routeIs('preprocessing.*') ? 'active' : '' }}">
            <i class="bi bi-cpu"></i> Preprocessing
        </a>
        <a href="{{ route('indexing.index') }}" class="sidebar-link {{ request()->routeIs('indexing.*') ? 'active' : '' }}">
            <i class="bi bi-table"></i> Indexing & TF-IDF
        </a>

        <div class="nav-section-title">Analisis</div>

        <a href="{{ route('evaluation.index') }}" class="sidebar-link {{ request()->routeIs('evaluation.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i> Evaluasi Sistem
        </a>
        <a href="{{ route('history.index') }}" class="sidebar-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Riwayat Pencarian
        </a>

        @if(auth()->check() && auth()->user()->isAdmin())
        <div class="nav-section-title">Manajemen</div>

        <a href="{{ route('users.index') }}"
           class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Pengguna
        </a>
    @endif
    </div>

    {{-- User Info di bawah sidebar --}}
    <div style="border-top:1px solid #21262d;padding:12px 16px;">
</nav>

{{-- ═══════════════ TOPBAR ═══════════════ --}}
<header id="topbar">
    <button class="btn btn-sm d-md-none me-3" id="toggleSidebar">
        <i class="bi bi-list fs-5"></i>
    </button>
    <div class="flex-grow-1">
        <h6 class="mb-0 fw-600 text-dark" style="font-weight:600;">@yield('page-title', 'Dashboard')</h6>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('search.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-search me-1"></i> Cari Berita
        </a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</header>

{{-- ═══════════════ MAIN CONTENT ═══════════════ --}}
<main id="main">
    <div class="page-content">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Toggle sidebar pada mobile
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('open');
    });

    // Setup CSRF token untuk AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
</script>

@stack('scripts')
</body>
</html>