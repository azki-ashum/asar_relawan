@extends('layouts.app')

@section('title', 'Admin - Ruangan')

@section('content')
<!-- Local styles: make rooms table match admin booking table look -->
<style>
    #admin-rooms .modern-table-wrapper { max-height: 60vh; }
    #admin-rooms .modern-table thead th{
        position: sticky; top: 0; z-index: 2;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.08);
    }
    #admin-rooms .modern-table :where(td, th){ padding: .9rem 1rem !important; vertical-align: middle; }
    /* Separator between rows instead of zebra */
    #admin-rooms .modern-table tbody td { border-top: 1px solid #eef0f2; }
    #admin-rooms .truncate-1, #admin-rooms .truncate-2{ display:-webkit-box; -webkit-box-orient:vertical; overflow:hidden; }
    #admin-rooms .truncate-1{ -webkit-line-clamp:1; }
    #admin-rooms .truncate-2{ -webkit-line-clamp:2; }
    #admin-rooms .badge-soft-success   { background:#d1e7dd; color:#0f5132; font-weight:600; }
    #admin-rooms .badge-soft-danger    { background:#f8d7da; color:#842029; font-weight:600; }
    #admin-rooms .btn-icon{ width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; }
</style>

<div id="admin-rooms" class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Admin - Ruangan</h3>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Ruangan
        </a>
    </div>

    <div class="table-responsive modern-table-wrapper">
    <table class="table table-hover align-middle mb-0 modern-table">
            <thead>
                <tr>
                    {{-- <th class="text-muted fw-semibold">ID</th> --}}
                    <th class="text-muted fw-semibold">Nama</th>
                    {{-- Lokasi and Kapasitas removed per request --}}
                    <th class="text-muted fw-semibold text-center" style="min-width:120px">Aktif</th>
                    <th class="text-muted fw-semibold text-center" style="min-width:110px">Admin Only</th>
                    <th class="text-muted fw-semibold text-center" style="min-width:110px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                    <tr>
                        {{-- <td class="text-muted small">#{{ $room->id }}</td> --}}
                        <td>{{ $room->name }}</td>
                        {{-- location and capacity removed --}}
                        <td class="text-center" style="width:80px;">
                            @if($room->is_active)
                                <span class="badge w-100 badge-soft-success">
                                    <i class="bi bi-check-circle-fill me-1"></i> Ya
                                </span>
                            @else
                                <span class="badge w-100 badge-soft-danger">
                                    <i class="bi bi-x-circle-fill me-1"></i> Tidak
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($room->is_admin_only)
                                <span class="badge badge-soft-danger"><i class="bi bi-lock-fill me-1"></i> Ya</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-light border btn-icon" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.rooms.destroy', $room) }}" method="post" class="swal-confirm" data-confirm="Hapus room ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light border btn-icon" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(method_exists($rooms, 'links'))
        <div class="d-flex justify-content-end mt-3">{{ $rooms->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
