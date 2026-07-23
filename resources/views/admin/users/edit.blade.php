@extends('layouts.app')

@section('title', 'Edit Role User')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Manage Users
    </a>
</div>

<div class="card" style="max-width: 480px;">
    <div class="card-header fw-semibold">
        <i class="bi bi-person-gear me-1"></i> Edit Role: {{ Str::title($user->name) }}
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Email: {{ $user->email }}</p>
        <form method="post" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ $user->role === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
