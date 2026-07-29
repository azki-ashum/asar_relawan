@extends('layouts.relawan')

@section('title', 'Login')

@section('content')
<div class="login-wrap d-flex align-items-center justify-content-center">
    <div class="login-col">
        <div class="card login-card border-0">
            <div class="card-body p-4 p-sm-5 text-center">
                <div class="login-logo mx-auto mb-4">
                    <img src="{{ asset('images/logo-dark.png') }}" alt="ASAR Humanity"
                        onerror="this.onerror=null;this.src='{{ asset('images/logo-white.png') }}'">
                </div>

                <h1 class="h4 mb-1">Sistem Pengajuan Relawan</h1>
                <p class="text-muted small mb-4">ASAR Humanity</p>

                <form method="POST" action="{{ route('login.google') }}">
                    @csrf
                    <button type="submit" class="btn-google w-100">
                        <img src="{{ asset('images/google.svg') }}" alt="" width="20" height="20">
                        <span>Masuk dengan Google</span>
                    </button>
                </form>

                <div class="login-note mt-4 d-flex align-items-start gap-2 text-start">
                    <i class="bi bi-shield-check mt-1"></i>
                    <span>Akses terbatas: hanya akun <strong>@asarhumanity.org</strong> yang dapat menggunakan
                        aplikasi ini.</span>
                </div>
            </div>
            <div class="login-footer">
                Butuh bantuan? Hubungi <a href="mailto:azki@asarhumanity.org">azki@asarhumanity.org</a>
            </div>
        </div>
    </div>
</div>

<style>
    .login-wrap {
        /* 100vh dikurangi container mt-4 (24px) + main pb-5 (48px) agar benar-benar center di viewport
           (halaman login selalu tampil sebagai guest, jadi navbar tidak ikut memakan tinggi). */
        min-height: calc(100vh - 72px);
        padding: 2rem 0;
    }

    .login-col {
        width: 100%;
        max-width: 420px;
    }

    .login-card {
        background: var(--surface);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        position: relative;
    }

    .login-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--brand-600), var(--brand-800));
    }

    .login-logo {
        width: 76px;
        height: 76px;
        border-radius: var(--radius);
        background: var(--brand-50);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(22, 163, 74, .16);
    }

    .login-logo img {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .btn-google {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .6rem;
        padding: .7rem 1rem;
        background: #fff;
        color: var(--ink-soft);
        font-weight: 600;
        font-size: .95rem;
        border: 1px solid var(--line);
        border-radius: var(--radius-sm);
        transition: all .15s ease;
    }

    .btn-google:hover {
        background: var(--line-soft);
        border-color: var(--faint);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .login-note {
        background: var(--brand-50);
        color: var(--brand-800);
        border: 1px solid var(--brand-100);
        border-radius: var(--radius-sm);
        padding: .65rem .85rem;
        font-size: .82rem;
        line-height: 1.5;
    }

    .login-note i {
        color: var(--brand-700);
    }

    .login-footer {
        background: var(--line-soft);
        border-top: 1px solid var(--line);
        padding: .9rem 1rem;
        text-align: center;
        font-size: .82rem;
        color: var(--muted);
    }

    .login-footer a {
        color: var(--brand-700);
        font-weight: 600;
    }
</style>
@endsection
