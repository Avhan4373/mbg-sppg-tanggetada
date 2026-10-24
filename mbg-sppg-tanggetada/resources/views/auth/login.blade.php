@extends('layouts.app')

@section('title', 'Masuk — SIPRES MBG Tanggetada')

@section('content')
    <div class="auth-wrap">
        <div class="brand-line">
            <div class="brand-logo">🥗</div>
            <h1 style="margin:0;font-size:22px">Masuk ke SIPRES MBG</h1>
            <p style="color:var(--muted);margin:4px 0 0;font-size:13.5px">Kelola data bahan, transaksi, dan riwayat prediksi.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="card">
            @csrf

            <div class="field" style="margin-bottom:14px">
                <label for="email">Alamat email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <div class="field" style="margin-bottom:14px">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="check-row" style="margin-bottom:16px">
                <input id="remember" type="checkbox" name="remember" value="1">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Masuk</button>

            <p style="margin:16px 0 0;text-align:center;font-size:13px;color:var(--muted)">
                Halaman prediksi dapat diakses tanpa login.
                <a href="{{ route('home') }}">Kembali ke prediksi</a>
            </p>
        </form>

        <div class="card tight" style="margin-top:14px;background:var(--surface-2)">
            <div class="section-title" style="margin-bottom:6px">Akun demo</div>
            <div style="font-size:13px;line-height:1.9">
                <div><strong>Admin:</strong> admin@mbg.test</div>
                <div><strong>Ahli gizi:</strong> gizi@mbg.test</div>
                <div><strong>Kepala SPPG:</strong> kepala@mbg.test</div>
                <div><strong>Password:</strong> password</div>
            </div>
        </div>
    </div>
@endsection
