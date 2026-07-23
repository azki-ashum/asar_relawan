@extends('layouts.relawan')

@section('title', 'Tambah Relawan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.relawan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">Tambah Relawan</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.relawan.store') }}" method="post">
            @csrf
            @include('admin.relawan._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                <a href="{{ route('admin.relawan.index') }}" class="btn btn-light border">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
