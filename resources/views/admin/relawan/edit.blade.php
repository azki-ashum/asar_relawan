@extends('layouts.relawan')

@section('title', 'Edit Relawan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.relawan.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h3 class="mb-0">Edit Relawan</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.relawan.update', $relawan) }}" method="post">
            @csrf @method('PUT')
            @include('admin.relawan._form')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                <a href="{{ route('admin.relawan.index') }}" class="btn btn-light border">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
