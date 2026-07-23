@php
    $meta = \App\Models\Pengajuan::STATUSES[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-soft-secondary', 'icon' => ''];
@endphp
<span class="badge {{ $meta['class'] }}">
    @if(!empty($meta['icon']))<i class="bi {{ $meta['icon'] }}"></i>@endif{{ $meta['label'] }}
</span>
