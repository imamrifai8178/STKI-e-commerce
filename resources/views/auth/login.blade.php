<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - STKI Berita Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f1117 0%, #1a1f2e 50%, #0f2027 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            overflow: hidden; width: 100%; max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #1a56db, #1e429f);
            padding: 36px 32px; text-align: center; color: #fff;
        }
        .login-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,.15);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 1.8rem;
        }
        .login-body { padding: 32px; }
        .form-control:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
        .btn-primary { background: #1a56db; border-color: #1a56db; }
        .btn-primary:hover { background: #1e429f; border-color: #1e429f; }
        .form-label { font-weight: 500; font-size: .875rem; color: #374151; }
        .input-group-text { background: #f9fafb; border-right: none; }
        .form-control { border-left: none; }
        .form-control:focus { border-left: none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <div class="login-icon">
            <i class="bi bi-search"></i>
        </div>
        <h4 class="fw-700 mb-1" style="font-weight:700;">STKI Berita Indonesia</h4>
        <p class="mb-0 opacity-75" style="font-size:.875rem;">Sistem Temu Kembali Informasi</p>
    </div>

    <div class="login-body">
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem;">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           placeholder="admin@stki.id" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-muted" for="remember" style="font-size:.875rem;">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-600" style="font-weight:600;">
    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
</button>

<div class="text-center mt-3">
    <small class="text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-decoration-none">
            Daftar di sini
        </a>
    </small>
</div>
        </form>

        <div class="mt-4 pt-3 border-top text-center">
            <small class="text-muted">Demo: admin@stki.id / password</small>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>