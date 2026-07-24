@extends('layouts.relawan')

@section('title', 'Manage Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0"><i class="bi bi-people me-2"></i>Manage Users</h1>
    <span class="badge bg-secondary">{{ $users->total() }} user</span>
</div>

<form method="get" class="row g-2 mb-3 align-items-center">
    <div class="col-sm-5 col-md-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama / email...">
    </div>
    <div class="col-sm-4 col-md-3">
        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- Semua Role --</option>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Cari</button>
        @if(request('q') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        @endif
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-stack mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 d-none d-md-table-cell" style="width:50px;">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th style="width:160px;">Role</th>
                        <th class="text-end pe-3" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    <tr>
                        <td class="ps-3 text-muted small d-none d-md-table-cell">{{ $users->firstItem() + $i }}</td>
                        <td class="cell-title">{{ Str::title($u->name) }}</td>
                        <td class="text-muted small" data-label="Email">{{ $u->email }}</td>
                        <td data-label="Role">
                            @php
                                $roleClass = match(true) {
                                    $u->role === 'admin'                    => 'bg-danger',
                                    $u->role === 'user'                     => 'bg-secondary',
                                    str_starts_with($u->role, 'admin_')     => 'bg-primary',
                                    default                                 => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $roleClass }}">{{ $roles[$u->role] ?? $u->role }}</span>
                        </td>
                        <td class="cell-actions text-end pe-3">
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @if($u->id !== auth()->id())
                            <form method="post" class="d-inline swal-confirm" data-confirm="Hapus user {{ Str::title($u->name) }}?" action="{{ route('admin.users.destroy', $u) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="no-card"><td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>Tidak ada user ditemukan.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }}</small>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

