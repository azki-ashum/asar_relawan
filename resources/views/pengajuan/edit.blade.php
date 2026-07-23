@extends('layouts.relawan')

@section('title', 'Edit Pengajuan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">Edit Pengajuan</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('pengajuan.update', $pengajuan) }}" method="post">
            @csrf @method('PUT')
            @include('pengajuan._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                <a href="{{ route('pengajuan.show', $pengajuan) }}" class="btn btn-light border">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
