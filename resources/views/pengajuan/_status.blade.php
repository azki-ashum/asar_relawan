@php
    $meta = \App\Models\Pengajuan::STATUSES[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-soft-secondary', 'icon' => ''];
@endphp
<span class="badge {{ $meta['class'] }} d-inline-flex align-items-center justify-content-center">
    @if(!empty($meta['icon']))<i class="bi {{ $meta['icon'] }} me-1"></i>@endif{{ $meta['label'] }}
</span>
