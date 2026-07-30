@extends('layouts.relawan')

@section('title', 'Manage Users')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0">Manage Users</h3>
        <div class="text-muted small">Kelola akun pengguna beserta hak aksesnya.</div>
    </div>
    <span class="badge badge-soft-secondary">{{ $users->total() }} user</span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-2 mb-3">
            <div class="col-12 col-md-7">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Cari nama / email...">
            </div>
            <div class="col-8 col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2 d-grid">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            @if(request('q') || request('role'))
            <div class="col-12 d-grid d-md-block">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border btn-sm">Reset filter</a>
            </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th style="width:160px;">Role</th>
                        <th class="text-center" style="min-width:190px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="cell-title">{{ Str::title($u->name) }}</td>
                        <td data-label="Email">{{ $u->email }}</td>
                        <td data-label="Role">
                            @php
                            $roleClass = match(true) {
                            $u->role === 'admin' => 'badge-soft-danger',
                            str_starts_with($u->role, 'admin_') => 'badge-soft-info',
                            default => 'badge-soft-secondary',
                            };
                            @endphp
                            <span class="badge {{ $roleClass }}">{{ $roles[$u->role] ?? $u->role }}</span>
                        </td>
                        <td class="cell-actions text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil me-1"></i>Edit</a>
                                @if($u->id !== auth()->id())
                                <form method="post" class="d-inline swal-confirm"
                                    data-confirm="Hapus user {{ Str::title($u->name) }}?"
                                    action="{{ route('admin.users.destroy', $u) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                        <i class="bi bi-trash me-1"></i>Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="no-card">
                        <td colspan="4">
                            <div class="empty-state"><i class="bi bi-people"></i>Tidak ada user ditemukan.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total()
            }}</small>
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
