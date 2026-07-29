{{--
    Pill status kecil di bawah judul email.

    Pemakaian: @include('emails.partials.badge', ['tone' => 'success', 'text' => 'Disetujui'])
    tone: success | info | warning | danger | neutral (default: neutral)
--}}
@php
    $tones = [
        'success' => ['bg' => '#ecfdf3', 'fg' => '#15803d', 'border' => '#bbf7d0'],
        'info'    => ['bg' => '#eff6ff', 'fg' => '#1d4ed8', 'border' => '#bfdbfe'],
        'warning' => ['bg' => '#fffbeb', 'fg' => '#b45309', 'border' => '#fde68a'],
        'danger'  => ['bg' => '#fef2f2', 'fg' => '#b91c1c', 'border' => '#fecaca'],
        'neutral' => ['bg' => '#f1f5f9', 'fg' => '#475569', 'border' => '#e2e8f0'],
    ];
    $c = $tones[$tone ?? 'neutral'] ?? $tones['neutral'];

    $out = '<span style="display:inline-block;padding:5px 12px;background-color:' . $c['bg'] . ';color:' . $c['fg'] . ';'
        . 'border:1px solid ' . $c['border'] . ';border-radius:999px;font-size:11px;font-weight:700;'
        . 'letter-spacing:0.06em;text-transform:uppercase;">' . e($text) . '</span>';
@endphp
{!! $out !!}
