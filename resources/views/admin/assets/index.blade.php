@extends('layouts.app')

@section('title', 'Admin - Kendaraan')

@section('content')
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Admin - Kendaraan</h3>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#assetTypesListModal"><i class="bi bi-gear pr-2"></i> Tipe Kendaraan</button>
    </div>

    <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 p-3">
                            <div>
                                <h5 class="mb-0 fw-semibold">Daftar Kendaraan</h5>
                            </div>
                            <div class="main-actions">
                                <a href="{{ route('admin.assets.create') }}" class="btn btn-primary btn-sm">+ Kendaraan</a>
                            </div>
                        </div>

                        <style>
                            /* local badge-soft helpers used across the app */
                            .badge-soft-success{ background:#d1e7dd; color:#0f5132; font-weight:600; }
                            .badge-soft-warning{ background:#fff3cd; color:#664d03; font-weight:600; }
                            .badge-soft-danger{ background:#f8d7da; color:#842029; font-weight:600; }
                            .badge-soft-green{ background:#d2f4d2; color:#0c8f0c; font-weight:600; }
                            .badge-soft-secondary{ background:#e2e3e5; color:#41464b; font-weight:600; }
                            .modern-table .badge { padding: .35rem .5rem; border-radius: .5rem; }
                        </style>

                        <div class="table-responsive modern-table-wrapper">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead>
                                    <tr>
                                        <th class="text-muted fw-semibold" style="width:110px">Code</th>
                                        <th class="text-muted fw-semibold text-start">Nama</th>
                                        <th class="text-muted fw-semibold">Tipe</th>
                                        <th class="text-muted fw-semibold">Status</th>
                                        <th class="text-muted fw-semibold text-center" style="min-width:120px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assets as $a)
                                    <tr>
                                        <td class="small text-muted">{{ $a->code ?? '—' }}</td>
                                        <td class="text-start">
                                            {{ $a->name }}
                                            @if($a->is_admin_only)
                                                <span class="badge bg-danger ms-1" style="font-size:0.65rem;">Admin Only</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $t = optional($a->type); @endphp
                                            @if($t->exists ?? $t)
                                                <span class="badge bg-light text-dark border">{{ $t->display_name ?? '—' }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $st = $a->status ?? 'active';
                                                $statusMap = [
                                                    'active' => ['label' => 'Active', 'class' => 'badge-soft-success', 'icon' => 'bi-check-circle'],
                                                    'inactive' => ['label' => 'Inactive', 'class' => 'badge-soft-danger', 'icon' => 'bi-x-circle'],
                                                    'maintenance' => ['label' => 'Maintenance', 'class' => 'badge-soft-warning', 'icon' => 'bi-hourglass'],
                                                    'retired' => ['label' => 'Retired', 'class' => 'badge-soft-danger', 'icon' => 'bi-x-circle'],
                                                ];
                                                $s = $statusMap[$st] ?? ['label' => ucfirst($st), 'class' => 'badge-soft-warning', 'icon' => ''];
                                            @endphp
                                            <span class="badge {{ $s['class'] }}">
                                                @if(!empty($s['icon']))<i class="bi {{ $s['icon'] }} me-1"></i>@endif{{ $s['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('admin.assets.edit', $a) }}" class="btn btn-sm btn-light border btn-icon" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.assets.destroy', $a) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus kendaraan ini?">
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

                        <!-- Modal: list of Asset Types with CRUD actions -->
                        <div class="modal fade" id="assetTypesListModal" tabindex="-1" aria-labelledby="assetTypesListModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="assetTypesListModalLabel">Tipe Kendaraan</h5>
                                        <div class="ms-3">
                                            <a href="{{ route('admin.asset_types.create') }}" class="btn btn-primary btn-sm">+ Tipe</a>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="small text-muted">ID</th>
                                                        <th>Display</th>
                                                        <th class="text-center" style="min-width:110px">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($types as $type)
                                                    <tr>
                                                        <td class="small text-muted">#{{ $type->id }}</td>
                                                        <td>{{ $type->display_name }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <a href="{{ route('admin.asset_types.edit', $type) }}" class="btn btn-sm btn-light border btn-icon" title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form action="{{ route('admin.asset_types.destroy', $type) }}" method="post" class="d-inline swal-confirm" data-confirm="Hapus tipe kendaraan ini?">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(method_exists($assets, 'links'))
                        <div class="card-footer bg-white border-0 d-flex justify-content-end pt-4 pt-md-2">
                            {{ $assets->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
@endsection
