@php
    $tingkatLabel = $tingkat ? ucfirst(strtolower((string) $tingkat)) : '—';
    export_printer_icon_data_uri();
    $p = 'margin:0;font-family:Calibri,Arial,sans-serif;mso-line-height-rule:exactly;';
@endphp

<p style="{{ $p }}margin-bottom:12pt;font-size:10pt;color:#64748b;line-height:14pt;">
    Detail Riwayat Diagnosa #{{ $d->id }} &middot; {{ format_date_id($d->tanggal_diagnosa) }}
</p>

@include('exports.partials.word-shade-open', ['bg' => '#fef2f2'])
    <p style="{{ $p }}margin-bottom:8pt;line-height:1pt;font-size:1pt;">
        @include('exports.partials.word-img', ['cid' => 'printericon', 'width' => 72, 'height' => 72, 'hspace' => 12])
    </p>
    <p style="{{ $p }}margin-bottom:6pt;font-size:9pt;font-weight:bold;letter-spacing:0.6pt;text-transform:uppercase;color:#64748b;line-height:12pt;">
        Diagnosa Kerusakan
    </p>
    @if ($penyakit)
        <p style="{{ $p }}margin-bottom:8pt;font-size:18pt;font-weight:bold;color:#dc2626;line-height:22pt;">
            {{ $penyakit->nama_penyakit }}
        </p>
        <p style="{{ $p }}padding:5pt 12pt;background-color:#fee2e2;font-size:9pt;font-weight:bold;color:#b91c1c;line-height:12pt;">
            Tingkat Kerusakan : {{ $tingkatLabel }}
        </p>
    @else
        <p style="{{ $p }}font-size:16pt;font-weight:bold;color:#334155;line-height:20pt;">
            Tidak ada kerusakan terdeteksi
        </p>
    @endif
    <p style="{{ $p }}font-size:1pt;line-height:1pt;">&nbsp;</p>
@include('exports.partials.word-shade-close')

@if ($penyakit)
    <p style="{{ $p }}margin-bottom:6pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
        Penyebab
    </p>
    <p style="{{ $p }}margin-bottom:14pt;font-size:11pt;color:#334155;line-height:16pt;">
        {{ $penyakit->deskripsi }}
    </p>

    <p style="{{ $p }}margin-bottom:6pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
        Solusi
    </p>
    @if (count($solusiLines))
        @foreach ($solusiLines as $i => $line)
            <p style="{{ $p }}margin-bottom:6pt;font-size:11pt;color:#334155;line-height:16pt;">
                <span style="font-weight:bold;">{{ $i + 1 }}.</span> {{ $line }}
            </p>
        @endforeach
    @else
        <p style="{{ $p }}margin-bottom:14pt;font-size:11pt;color:#334155;line-height:16pt;">
            {{ $penyakit->solusi }}
        </p>
    @endif

    @if ($penyakit->pencegahan ?? null)
        <p style="{{ $p }}margin-bottom:6pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
            Pencegahan
        </p>
        @if (count($pencegahanLines ?? []))
            @foreach ($pencegahanLines as $i => $line)
                <p style="{{ $p }}margin-bottom:6pt;font-size:11pt;color:#334155;line-height:16pt;">
                    <span style="font-weight:bold;">{{ $i + 1 }}.</span> {{ $line }}
                </p>
            @endforeach
        @else
            <p style="{{ $p }}margin-bottom:14pt;font-size:11pt;color:#334155;line-height:16pt;">
                {{ $penyakit->pencegahan }}
            </p>
        @endif
    @endif
@endif

@include('exports.partials.word-shade-open', ['bg' => '#f8fafc', 'mb' => '0pt'])
    <p style="{{ $p }}margin-bottom:10pt;font-size:9pt;font-weight:bold;letter-spacing:0.6pt;text-transform:uppercase;color:#64748b;line-height:12pt;">
        Gejala yang terdeteksi
    </p>
    @if (count($kodes))
        @foreach ($kodes as $k)
            <p style="{{ $p }}margin-bottom:8pt;font-size:9pt;font-weight:600;color:#334155;line-height:12pt;">
                &#8226; {{ $namaGejala[$k] ?? $k }}
            </p>
        @endforeach
    @else
        <p style="{{ $p }}color:#94a3b8;">&mdash;</p>
    @endif
@include('exports.partials.word-shade-close')
