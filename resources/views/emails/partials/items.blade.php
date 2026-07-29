{{--
    Daftar bernomor untuk rincian kebutuhan / relawan yang ditugaskan.

    Pemakaian: @include('emails.partials.items', ['items' => [['label' => '...', 'meta' => '...'], ...]])
    'meta' opsional.

    Dirakit satu baris — lihat catatan di partials/detail.blade.php.
--}}
@php
    $items = array_values($items ?? []);

    $out = '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"'
        . ' style="width:100%;margin:8px 0 26px;border-collapse:separate;border-spacing:0;">';

    foreach ($items as $i => $item) {
        $isLast = $i === count($items) - 1;
        $out .= '<tr>'
            . '<td width="30" style="padding:11px 12px 11px 0;vertical-align:top;'
            . ($isLast ? '' : 'border-bottom:1px solid #f1f5f9;') . '">'
            . '<span style="display:inline-block;width:22px;height:22px;line-height:22px;text-align:center;'
            . 'background-color:#ecfdf3;color:#16a34a;font-size:11px;font-weight:700;border-radius:11px;">'
            . ($i + 1) . '</span></td>'
            . '<td style="padding:11px 0;vertical-align:top;' . ($isLast ? '' : 'border-bottom:1px solid #f1f5f9;') . '">'
            . '<span style="color:#0f172a;font-size:15px;font-weight:600;line-height:1.5;">' . e($item['label']) . '</span>';

        if (!empty($item['meta'])) {
            $out .= '<br><span style="color:#64748b;font-size:13px;line-height:1.5;">' . e($item['meta']) . '</span>';
        }

        $out .= '</td></tr>';
    }

    $out .= '</table>';
@endphp
{!! $out !!}
