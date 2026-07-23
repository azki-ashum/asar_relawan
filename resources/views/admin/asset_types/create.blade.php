@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto my-4" style="max-width:720px">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Buat Asset Type</h5>
            <div>
                <a href="{{ route('admin.assets.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.asset_types.store') }}" method="post">
                @csrf

                {{-- code removed: generated per-asset; keep only display name --}}

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="display_name" class="form-control" required value="{{ old('display_name') }}">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success ms-auto">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
