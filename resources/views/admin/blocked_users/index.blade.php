@extends('layouts.app')

@section('title', 'Blocked Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0">Daftar User Diblokir</h1>
    <div class="d-flex gap-2">
        {{-- <a href="{{ route('admin.blocked_users.index') }}" class="btn btn-outline-secondary btn-sm">Refresh</a> --}}
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#blockedUserModal">
            <i class="bi bi-shield-lock me-1"></i> Blokir User
        </button>
    </div>
    @if($errors->any())
        <div class="w-100 mt-2">
            <div class="alert alert-danger py-2 mb-0">
                Terdapat kesalahan input. Periksa form.
            </div>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Alasan</th>
                        <th>Ditambahkan Oleh</th>
                        <th>Waktu</th>
                        <th style="width:100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blockedUsers as $i => $b)
                    <tr>
                        <td>{{ $blockedUsers->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ isset($userNames[$b->email]) ? Str::title($userNames[$b->email]) : '—' }}</td>
                        <td class="small">{{ $b->email }}</td>
                        <td class="text-muted small">{{ $b->reason ?? '-' }}</td>
                        <td class="small">{{ optional($b->admin)->name ? Str::title(optional($b->admin)->name) : '—' }}</td>
                        <td class="small">{{ $b->created_at?->format('d-m-Y H:i') }}</td>
                        <td>
                            <form method="post" class="d-inline swal-confirm" data-confirm="Hapus dari daftar blokir?" action="{{ route('admin.blocked_users.destroy', $b) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-success btn-sm" type="submit">Unblock</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada user yang diblokir.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2">
        {{ $blockedUsers->links() }}
    </div>
</div>
{{-- Modal Blokir Email --}}
<div class="modal fade" id="blockedUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.blocked_users.store') }}" id="blocked-user-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-shield-lock me-1"></i> Blokir User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <select id="email_select" name="email" class="form-select @error('email') is-invalid @enderror" data-old="{{ old('email') }}">
                                <option value="">-- Pilih Email --</option>
                                @if(old('email'))
                                        @php $ou = \App\Models\User::where('email', old('email'))->first(); @endphp
                                        @if($ou)
                                                <option value="{{ $ou->email }}" selected>{{ Str::title($ou->name) }} ({{ $ou->email }})</option>
                                        @endif
                                @endif
                        </select>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Ketik nama atau email untuk mencari.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan (opsional)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="form-control @error('reason') is-invalid @enderror" placeholder="Misal: Penyalahgunaan, spam, dll">
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-plus-circle me-1"></i> Tambah ke Blokir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('head')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: calc(2.25rem + 2px); }
    .select2-container--default .select2-selection--single { border:1px solid #ced4da; padding:.375rem .5rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:1.4; }
    .select2-container--default .select2-results__option--highlighted { background:#0d6efd; }
    .select2-container--default .select2-results__option[aria-selected=true] { background:#0d6efd; color:#fff; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
        var modalEl = document.getElementById('blockedUserModal');
        if(!modalEl) return;
        modalEl.addEventListener('shown.bs.modal', function(){
                var selectEl = $('#email_select');
                if(selectEl.data('select2')) return; // already initialized
                selectEl.select2({
                        dropdownParent: $('#blockedUserModal'),
                        placeholder: '-- Pilih Email --',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                                url: '{{ route('admin.blocked_users.search') }}',
                                dataType: 'json',
                                delay: 200,
                                data: function (params) { return { q: params.term || '' }; },
                                processResults: function (data) { return { results: data.results }; }
                        }
                });
                setTimeout(function(){
                        $('#blockedUserModal .select2-container input.select2-search__field').trigger('focus');
                }, 120);
        });

        // Auto-open modal if there are validation errors
        @if($errors->any())
                var autoModal = new bootstrap.Modal(modalEl);
                autoModal.show();
        @endif
});
</script>
@endpush
@endsection
