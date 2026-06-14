@extends('layouts.auth')

@section('content')
<div class="container mt-5" style="max-width:500px">

    <div class="card">
        <div class="card-header">
            <h4>Register</h4>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label>Konfirmasi Password</label>
                    <input type="password"
                           name="password_confirmation"
                           class="form-control"
                           required>
                </div>

                <button type="submit"
                        class="btn btn-primary w-100">
                    Register
                </button>

            </form>

            <div class="text-center mt-3">
                Sudah punya akun?
                <a href="{{ route('login') }}">Login</a>
            </div>

        </div>
    </div>

</div>
@endsection