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
body{
    font-family: 'Inter', sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background: radial-gradient(circle at top, #1e3c72, #2a5298, #0f172a);
    overflow:hidden;
}

/* floating blur effect */
.bg-blur{
    position:absolute;
    width:400px;
    height:400px;
    background:rgba(255,255,255,.15);
    filter:blur(120px);
    border-radius:50%;
    top:-100px;
    left:-100px;
}

.bg-blur2{
    position:absolute;
    width:400px;
    height:400px;
    background:rgba(0,200,255,.15);
    filter:blur(120px);
    border-radius:50%;
    bottom:-120px;
    right:-120px;
}

.login-card{
    position:relative;
    width:100%;
    max-width:450px;
    border-radius:20px;
    backdrop-filter: blur(18px);
    background: rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.2);
    box-shadow:0 25px 60px rgba(0,0,0,.4);
    overflow:hidden;
    animation: fadeIn .6s ease-in-out;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

.login-header{
    text-align:center;
    padding:35px 30px 20px;
    color:white;
}

.login-icon{
    width:70px;
    height:70px;
    margin:auto;
    background:rgba(255,255,255,.15);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    margin-bottom:15px;
}

.login-body{
    padding:30px;
}

.form-control{
    background:rgba(255,255,255,.9);
    border:none;
    border-radius:12px;
    padding:12px 15px;
}

.form-control:focus{
    box-shadow:0 0 0 3px rgba(0,123,255,.25);
}

.input-group-text{
    background:rgba(255,255,255,.9);
    border:none;
    border-radius:12px 0 0 12px;
}

.btn-primary{
    background:linear-gradient(135deg,#1a56db,#2563eb);
    border:none;
    border-radius:12px;
    padding:12px;
    font-weight:600;
    transition:.3s;
}

.btn-primary:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(0,0,0,.2);
}

.small-text{
    font-size:.85rem;
    color:rgba(255,255,255,.8);
}

a{
    color:#fff;
    font-weight:500;
}
</style>
</head>

<body>

<div class="bg-blur"></div>
<div class="bg-blur2"></div>

<div class="login-card">

    <div class="login-header">
        <div class="login-icon">
            <i class="bi bi-search"></i>
        </div>
        <h4 class="fw-bold">STKI Berita Indonesia</h4>
        <p class="small-text">Sistem Temu Kembali Informasi Modern</p>
    </div>

    <div class="login-body">

        @if($errors->any())
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="text-white mb-2">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="admin@stki.id" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-white mb-2">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4 text-white small">
                <div>
                    <input type="checkbox" name="remember"> Ingat saya
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
            </button>

            <div class="text-center mt-3 small-text">
                Belum punya akun?
                <a href="{{ route('register') }}">Daftar</a>
            </div>
        </form>

        <div class="text-center mt-4 small-text">
            Demo: admin@stki.id / password
        </div>

    </div>
</div>

</body>
</html>