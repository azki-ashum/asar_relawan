@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mx-auto my-4" style="max-width:720px">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Edit Asset</h5>
            <a href="{{ route('admin.assets.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.assets.update', $asset) }}" method="post">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select id="type-select" name="type_id" class="form-select" required>
                        @foreach($types as $t)
                            <option value="{{ $t->id }}" {{ $t->id == $asset->type_id ? 'selected' : '' }}>{{ $t->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required value="{{ old('name', $asset->name) }}">
                </div>

                {{-- fuel removed from assets; captured via booking fuel photos for kendaraan assets --}}

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    @php $s = old('status', $asset->status ?? 'active'); @endphp
                    <select name="status" class="form-select">
                        <option value="active" {{ $s === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $s === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="maintenance" {{ $s === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="retired" {{ $s === 'retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input name="is_admin_only" id="is_admin_only_asset" type="checkbox" value="1" class="form-check-input" {{ old('is_admin_only', $asset->is_admin_only) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_admin_only_asset">
                        <span class="text-danger fw-semibold">Admin Only</span>
                        <small class="text-muted ms-1">— Hanya bisa dibooking oleh Admin</small>
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success ms-auto">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    (function(){
    // fuel UI removed from assets
    })();
</script>
@endpush

@endsection
