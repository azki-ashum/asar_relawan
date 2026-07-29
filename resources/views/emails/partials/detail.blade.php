{{--
    Blok detail label/nilai untuk email.

    Pemakaian: @include('emails.partials.detail', ['rows' => ['Lokasi' => '...', ...]])
    Baris bernilai null/'' otomatis dilewati.

    HTML sengaja dirakit jadi SATU baris: keluaran blade ini masih dilewatkan
    parser Markdown, dan baris kosong di tengah blok HTML akan memutus blok itu.
--}}
@php
    $rows = collect($rows ?? [])->filter(fn ($v) => $v !== null && $v !== '')->all();

    $pad   = 'padding:13px 20px;vertical-align:top;';
    $sepTop = 'border-top:1px solid #eef2f6;';

    $out = '<table width="100%" cellpadding="0" cellspacing="0" role="presentation"'
        . ' style="width:100%;margin:26px 0;border-collapse:separate;border-spacing:0;'
        . 'background-color:#f8fafc;border:1px solid #e8ecf2;border-radius:12px;">';

    $first = true;
    foreach ($rows as $label => $value) {
        $sep = $first ? '' : $sepTop;
        $out .= '<tr>'
            . '<td style="' . $pad . $sep . 'width:36%;color:#64748b;font-size:11px;font-weight:700;'
            . 'letter-spacing:0.06em;text-transform:uppercase;line-height:1.5;">' . e($label) . '</td>'
            . '<td style="' . $pad . $sep . 'color:#0f172a;font-size:15px;font-weight:500;line-height:1.5;">'
            . e($value) . '</td>'
            . '</tr>';
        $first = false;
    }

    $out .= '</table>';
@endphp
{!! $out !!}
