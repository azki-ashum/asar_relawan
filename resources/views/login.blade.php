@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(180deg, #f7f9fc 0%, #ffffff 40%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body p-4 p-sm-5 text-center">
                        <div class="mx-auto mb-3"
                            style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;">
                            <img src="{{ asset('images/logo-dark.png') }}" alt="ASAR"
                                style="width:72px;height:72px;object-fit:contain;border-radius:14px;"
                                onerror="this.onerror=null;this.src='{{ asset('images/logo-white.png') }}'">
                        </div>

                        <h2 class="h5 mb-1 fw-semibold">Sistem Pengajuan Relawan</h2>
                        <p class="text-muted small mb-5">ASAR Humanity</p>

                        {{-- <p class="text-muted mb-4 small">Akses hanya untuk staf internal dengan email berakhiran
                            <strong>@asarhumanity.org</strong>. Masuk menggunakan akun Google organisasi untuk
                            melanjutkan.</p> --}}

                        <form method="POST" action="{{ route('login.google') }}"
                            class="d-flex align-items-center justify-content-center">
                            @csrf
                            <button type="submit"
                                class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2"
                                style="padding: .7rem 1rem;">
                                <img src="{{ asset('images/google.svg') }}" alt="Google"
                                    style="width:20px;height:20px;">
                                <span class="fw-medium">Masuk dengan Google</span>
                            </button>
                        </form>

                        {{-- <div class="mt-3 d-flex justify-content-center">
                            <small class="text-muted">Dengan masuk, Anda menyetujui kebijakan penggunaan.</small>
                        </div> --}}

                        <div class="mt-5">
                            <div class="alert alert-info p-2 small mb-0" role="alert">
                                Akses terbatas: hanya akun <strong>@asarhumanity.org</strong> yang dapat menggunakan
                                aplikasi ini.
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center py-3">
                        <small class="text-muted">Butuh bantuan? Hubungi <a
                                href="mailto:azki@asarhumanity.org">azki@asarhumanity.org</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection