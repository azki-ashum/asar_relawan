@extends('layouts.app')

@section('title', 'Pilih Dashboard')

@section('content')
<div class="choose-viewport d-flex align-items-center justify-content-center" style="height:100vh; overflow:hidden;">
    <div class="container py-2">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10">

                <div class="mx-auto mb-4" style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('images/logo-dark.png') }}" alt="ASAR" style="width:72px;height:72px;object-fit:contain;border-radius:14px;" onerror="this.onerror=null;this.src='{{ asset('images/logo-white.png') }}'">
                </div>

                <div class="text-center mb-4">
                    <h1 class="display-6 fw-bold">Pilih Dashboard</h1>
                    <p class="text-muted">Kelola booking ruangan atau peminjaman kendaraan. Pilih salah satu untuk melanjutkan.</p>
                </div>

                <div class="d-flex gap-4 justify-content-center flex-column flex-md-row align-items-stretch cards-row">
                    @auth
                    @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="card feature-card admin-choose-card border-0 shadow-sm text-decoration-none text-dark">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="icon-circle bg-dark text-white d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                            </div>
                            <h5 class="card-title mb-1">Admin Panel</h5>
                            <p class="card-text text-muted feature-desc">Kelola ruangan, kendaraan, pengguna, dan pengaturan sistem.</p>
                        </div>
                    </a>
                    @endif
                    @endauth

                    <a href="{{ route('dashboard') }}" class="card feature-card border-0 shadow-sm text-decoration-none text-dark">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="icon-circle bg-primary text-white d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-building-up" aria-hidden="true"></i>
                            </div>
                            <h5 class="card-title mb-1">Booking Ruangan</h5>
                            <p class="card-text text-muted feature-desc">Kelola reservasi ruangan, kalender, dan jadwal.</p>
                            {{-- <div class="feature-cta mt-3">Lihat Dashboard &rarr;</div> --}}
                        </div>
                    </a>

                    <a href="{{ route('dashboard.asset') }}" class="card feature-card border-0 shadow-sm text-decoration-none text-dark">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                            <div class="icon-circle bg-success text-white d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-car-front" aria-hidden="true"></i>
                            </div>
                            <h5 class="card-title mb-1">Booking Kendaraan</h5>
                            <p class="card-text text-muted feature-desc">Kelola peminjaman kendaraan, destinasi tujuan dan riwayat peminjaman.</p>
                        </div>
                    </a>
                </div>

                <div class="text-center mt-4">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-lg rounded-pill btn-outline-dark logout-btn d-flex align-items-center gap-2 px-4">
                            <i class="bi bi-box-arrow-right fs-5"></i>
                            <span class="fw-semibold">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    /* desktop-first: full-viewport fixed layout; override on mobile to allow scrolling */
    .choose-viewport { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%); overflow: hidden; }
    .choose-viewport .container { height: 100%; display:flex; align-items:center; justify-content:center; padding-top:0.5rem; padding-bottom:0.5rem; }
    .choose-viewport .col-12.col-md-10 { display:flex; flex-direction:column; justify-content:center; padding:0 1rem; }

    /* modern card hover */
    .card.shadow-sm { transition: transform .18s cubic-bezier(.2,.8,.2,1), box-shadow .18s, transform .18s; }
    .card.feature-card { border-radius: 12px; overflow: hidden; }
    .card.feature-card .card-body { padding: 2.2rem 1.5rem; }
    .card.feature-card .icon-circle { width:84px; height:84px; border-radius:999px; font-size:1.6rem; }
    .card.feature-card .icon-circle i { font-size:1.6rem; }
    .card.feature-card .feature-desc { max-width:70%; margin: .6rem auto 0; }
    .card.feature-card .feature-cta { color: #0b5ed7; font-weight:600; opacity:.95; }
    .card.feature-card:hover { transform: translateY(-8px); box-shadow: 0 22px 56px rgba(22,28,37,0.12); }

    /* coming soon variant */
    .card.coming-soon { position: relative; }
    .card.coming-soon { position: relative; background: linear-gradient(180deg, #f6f7f8 0%, #ffffff 60%); border: 1px solid rgba(0,0,0,0.04); }
    .card.coming-soon .coming-badge { position: absolute; top: 14px; right: 14px; background: #495057; color: #fff; padding: 6px 10px; border-radius: 999px; font-weight:700; font-size:.78rem; display:inline-flex; align-items:center; gap:.35rem; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
    .card.coming-soon .coming-icon { width:84px; height:84px; }
    .card.coming-soon .coming-icon i { color: rgba(0,0,0,0.18); font-size:1.4rem; }
    .card.coming-soon .feature-desc { opacity:.9; color: rgba(0,0,0,0.6); }
    .card.coming-soon:hover { transform: none; box-shadow: 0 6px 18px rgba(22,28,37,0.06); }
    .card.coming-soon .feature-cta { display:none; }

    /* equal-height cards row */
    .cards-row { align-items: stretch; width:100%; }
    .cards-row .card { display:flex; flex-direction:column; height:100%; min-height:140px; }
    .cards-row .card .card-body { flex:1 1 auto; display:flex; flex-direction:column; justify-content:center; align-items:center; padding-top: 1.25rem; padding-bottom: 1.25rem; }
    /* ensure text inside cards is centered and descriptions have consistent width */
    .cards-row .card .card-body { text-align: center; }
    .cards-row .card .card-body p.card-text { max-width: 62%; margin-top: .5rem; margin-bottom: 0; }

    /* two equal columns on desktop using CSS Grid for stability */
    @media (min-width: 768px) {
        .cards-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; }
        .cards-row .card { width: 100%; }
        .text-center.mb-4 { margin-bottom: 18px; }
    }

    /* admin card subtle distinction */
    .admin-choose-card { border: 1.5px solid rgba(0,0,0,0.10) !important; background: linear-gradient(160deg, #fafafa 0%, #f0f0f5 100%); }
    .admin-choose-card:hover { background: linear-gradient(160deg, #f0f0f5 0%, #e8e8f0 100%); }

    @media (max-width: 991px) {
        .card.feature-card .feature-desc { max-width: 86%; }
        .card.feature-card .card-body { padding: 1.5rem; }
        .card.feature-card .icon-circle { width:64px; height:64px; }
    }

    /* stacked cards on mobile */
    @media (max-width: 767px) {
        /* mobile-friendly override: allow natural scrolling and adjust spacing */
        .choose-viewport { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 50%); overflow: auto; padding-top: 1rem; padding-bottom: 1rem; }
        .choose-viewport .container { display:flex; align-items:center; justify-content:center; padding-top:0.5rem; padding-bottom:0.5rem; }
        .choose-viewport .col-12.col-md-10 { display:flex; flex-direction:column; justify-content:center; padding:0 1rem; }

        /* stacked full-width cards, larger description width for readability */
        .cards-row { display: flex; flex-direction: column; gap: 12px; }
        .cards-row .card { min-height:120px; width:100% !important; }
        .cards-row .card .card-body { padding: 1rem; }
        .cards-row .card .card-body p.card-text { max-width: 92%; margin: 0.25rem auto 0; }
        .card.feature-card .icon-circle { width:56px; height:56px; }
        .text-center.mb-4 { margin-bottom: 12px; }
    }

    /* logout button style */
    .logout-btn { border-width: 1.5px; transition: transform .12s ease, box-shadow .12s ease, background-color .12s ease; }
    .logout-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(22,28,37,0.08); background-color: #222; color: #fff; }
    .logout-btn i { transition: transform .12s ease; }
    .logout-btn:hover i { transform: translateX(4px); }
</style>
@endpush
