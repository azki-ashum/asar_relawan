@extends('layouts.relawan')

@section('title', 'Buat Pengajuan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('pengajuan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">Buat Pengajuan Relawan</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('pengajuan.store') }}" method="post">
            @csrf
            @include('pengajuan._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Ajukan</button>
                <a href="{{ route('pengajuan.index') }}" class="btn btn-light border">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
