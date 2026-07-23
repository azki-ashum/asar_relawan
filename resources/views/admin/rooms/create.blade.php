@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto my-4" style="max-width:720px">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Buat Ruangan</h5>
            <div>
                <a href="{{ route('bookings.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.rooms.store') }}" method="post">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                {{-- location and capacity removed per request --}}

                <div class="form-check mb-3">
                    <input name="is_active" type="checkbox" value="1" class="form-check-input" checked>
                    <label class="form-check-label">Active</label>
                </div>

                <div class="form-check mb-3">
                    <input name="is_admin_only" id="is_admin_only_room" type="checkbox" value="1" class="form-check-input" {{ old('is_admin_only') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_admin_only_room">
                        <span class="text-danger fw-semibold">Admin Only</span>
                        <small class="text-muted ms-1">— Hanya bisa dibooking oleh Admin</small>
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success ms-auto">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
