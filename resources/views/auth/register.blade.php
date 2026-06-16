@extends('layouts.auth')

@section('content')

<style>
body{
    background: radial-gradient(circle at top, #1e3c72, #2a5298, #0f172a);
    font-family: 'Inter', sans-serif;
}

.auth-wrapper{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}

.auth-card{
    width:100%;
    max-width:480px;
    border-radius:20px;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(18px);
    border:1px solid rgba(255,255,255,.2);
    box-shadow:0 25px 60px rgba(0,0,0,.4);
    overflow:hidden;
    animation: fadeIn .6s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

.auth-header{
    text-align:center;
    padding:30px 20px 10px;
    color:white;
}

.auth-icon{
    width:70px;
    height:70px;
    margin:auto;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.15);
    font-size:2rem;
    margin-bottom:12px;
}

.auth-body{
    padding:25px 30px 30px;
}

label{
    color:white;
    font-size:.9rem;
    margin-bottom:6px;
}

.form-control{
    border:none;
    border-radius:12px;
    padding:12px 14px;
    background:rgba(255,255,255,.9);
}

.form-control:focus{
    box-shadow:0 0 0 3px rgba(0,123,255,.25);
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

<div class="auth-wrapper">

    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h4 class="fw-bold">Buat Akun Baru</h4>
            <p class="small-text">STKI Berita Indonesia</p>
        </div>

        <div class="auth-body">

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="mb-3">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="bi bi-person-check me-2"></i> Register
                </button>

                <div class="text-center mt-3 small-text">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">Login</a>
                </div>

            </form>

        </div>

    </div>

</div>

@endsection