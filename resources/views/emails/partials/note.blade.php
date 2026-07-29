{{--
    Kotak catatan / callout.

    Pemakaian: @include('emails.partials.note', ['tone' => 'danger', 'title' => '...', 'body' => '...'])
    tone: success | info | warning | danger (default: info)

    Dirakit satu baris — lihat catatan di partials/detail.blade.php.
--}}
@php
    $tones = [
        'success' => ['bg' => '#ecfdf3', 'border' => '#bbf7d0', 'accent' => '#16a34a', 'title' => '#166534', 'text' => '#15803d'],
        'info'    => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'accent' => '#2563eb', 'title' => '#1e40af', 'text' => '#1d4ed8'],
        'warning' => ['bg' => '#fffbeb', 'border' => '#fde68a', 'accent' => '#d97706', 'title' => '#92400e', 'text' => '#b45309'],
        'danger'  => ['bg' => '#fef2f2', 'border' => '#fecaca', 'accent' => '#dc2626', 'title' => '#991b1b', 'text' => '#b91c1c'],
    ];
    $c = $tones[$tone ?? 'info'] ?? $tones['info'];

    $out = '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"'
        . ' style="width:100%;margin:24px 0;border-collapse:separate;border-spacing:0;">'
        . '<tr><td style="padding:16px 20px;background-color:' . $c['bg'] . ';border:1px solid ' . $c['border'] . ';'
        . 'border-left:4px solid ' . $c['accent'] . ';border-radius:10px;">';

    if (!empty($title)) {
        $out .= '<div style="color:' . $c['title'] . ';font-size:11px;font-weight:700;letter-spacing:0.06em;'
            . 'text-transform:uppercase;margin-bottom:6px;">' . e($title) . '</div>';
    }

    $out .= '<div style="color:' . $c['text'] . ';font-size:15px;line-height:1.6;">'
        . nl2br(e($body)) . '</div>'
        . '</td></tr></table>';
@endphp
{!! $out !!}
