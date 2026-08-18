@php
    export_check_success_icon_data_uri();
    export_warning_icon_data_uri();
    $p = 'margin:0;font-family:Calibri,Arial,sans-serif;mso-line-height-rule:exactly;';
@endphp

<p style="{{ $p }}margin-bottom:12pt;font-size:10pt;color:#64748b;line-height:14pt;">
    Hasil Diagnosa #{{ $d->id }} &middot; {{ format_date_id($d->tanggal_diagnosa) }}
</p>

@if ($penyakit)
    @include('exports.partials.word-shade-open', ['bg' => '#ecfdf5'])
        <p style="{{ $p }}margin-bottom:8pt;line-height:1pt;font-size:1pt;">
            @include('exports.partials.word-img', ['cid' => 'checkicon', 'width' => 40, 'height' => 40, 'hspace' => 10])
        </p>
        <p style="{{ $p }}margin-bottom:4pt;font-size:13pt;font-weight:bold;color:#0f172a;line-height:17pt;">
            Diagnosa Selesai
        </p>
        <p style="{{ $p }}margin-bottom:12pt;font-size:11pt;color:#475569;line-height:15pt;">
            Berikut hasil identifikasi berdasarkan gejala yang Anda pilih.
        </p>
        <p style="{{ $p }}margin-bottom:8pt;font-size:11pt;line-height:15pt;">
            <span style="font-weight:600;color:#334155;">Jenis Kerusakan :</span>
            <span style="font-weight:bold;color:#0f172a;">{{ $penyakit->nama_penyakit }}</span>
        </p>
        <p style="{{ $p }}margin-bottom:8pt;font-size:11pt;line-height:15pt;">
            <span style="font-weight:600;color:#334155;">Tingkat Kerusakan :</span>
            <span style="font-weight:bold;color:#0f172a;">{{ $tingkatLabel }}</span>
        </p>
        <p style="{{ $p }}font-size:11pt;line-height:15pt;">
            <span style="font-weight:600;color:#334155;">Rekomendasi :</span>
            <span style="font-weight:bold;color:#0f172a;">{{ $rekomendasi }}</span>
        </p>
        <p style="{{ $p }}font-size:1pt;line-height:1pt;">&nbsp;</p>
    @include('exports.partials.word-shade-close')

    @if (count($penyebabLines ?? []) || ($penyakit->deskripsi ?? null))
        @include('exports.partials.word-shade-open', ['bg' => '#f8fafc'])
            <p style="{{ $p }}margin-bottom:8pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
                Penyebab Kerusakan
            </p>
            @if (count($penyebabLines ?? []))
                @foreach ($penyebabLines as $i => $line)
                    <p style="{{ $p }}margin-bottom:6pt;font-size:11pt;color:#334155;line-height:16pt;">
                        <span style="font-weight:bold;">{{ $i + 1 }}.</span> {{ $line }}
                    </p>
                @endforeach
            @else
                <p style="{{ $p }}font-size:11pt;color:#334155;line-height:16pt;">{{ $penyakit->deskripsi }}</p>
            @endif
        @include('exports.partials.word-shade-close')
    @endif

    @include('exports.partials.word-shade-open', ['bg' => '#eff6ff'])
        <p style="{{ $p }}margin-bottom:8pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
            Solusi Perbaikan
        </p>
        @if (count($solusiLines))
            @foreach ($solusiLines as $i => $line)
                <p style="{{ $p }}margin-bottom:6pt;font-size:11pt;color:#334155;line-height:16pt;">
                    <span style="font-weight:bold;">{{ $i + 1 }}.</span> {{ $line }}
                </p>
            @endforeach
        @elseif ($penyakit->solusi)
            <p style="{{ $p }}font-size:11pt;color:#334155;line-height:16pt;">{{ $penyakit->solusi }}</p>
        @else
            <p style="{{ $p }}font-size:11pt;color:#64748b;">Belum ada solusi tercatat untuk kerusakan ini.</p>
        @endif
    @include('exports.partials.word-shade-close')

    @if (count($pencegahanLines ?? []) || ($penyakit->pencegahan ?? null))
        @include('exports.partials.word-shade-open', ['bg' => '#fffbeb'])
            <p style="{{ $p }}margin-bottom:8pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
                Pencegahan
            </p>
            @if (count($pencegahanLines ?? []))
                @foreach ($pencegahanLines as $i => $line)
                    <p style="{{ $p }}margin-bottom:6pt;font-size:11pt;color:#334155;line-height:16pt;">
                        <span style="font-weight:bold;">{{ $i + 1 }}.</span> {{ $line }}
                    </p>
                @endforeach
            @else
                <p style="{{ $p }}font-size:11pt;color:#334155;line-height:16pt;">{{ $penyakit->pencegahan }}</p>
            @endif
        @include('exports.partials.word-shade-close')
    @endif

    @include('exports.partials.word-shade-open', ['bg' => '#fffbeb', 'mb' => '0pt'])
        <p style="{{ $p }}margin-bottom:8pt;line-height:1pt;font-size:1pt;">
            @include('exports.partials.word-img', ['cid' => 'warningicon', 'width' => 32, 'height' => 32, 'hspace' => 8])
        </p>
        <p style="{{ $p }}margin-bottom:6pt;font-size:12pt;font-weight:bold;color:#0f172a;line-height:16pt;">
            Rekomendasi
        </p>
        <p style="{{ $p }}font-size:11pt;color:#334155;line-height:16pt;">
            Jika kerusakan masih berlanjut, sebaiknya hubungi teknisi.
        </p>
        <p style="{{ $p }}font-size:1pt;line-height:1pt;">&nbsp;</p>
    @include('exports.partials.word-shade-close')
@else
    @include('exports.partials.word-shade-open', ['bg' => '#f8fafc', 'mb' => '0pt'])
        <p style="{{ $p }}margin-bottom:6pt;font-size:14pt;font-weight:bold;color:#1e293b;text-align:center;">Tidak ada kerusakan yang terdeteksi</p>
        <p style="{{ $p }}font-size:11pt;color:#64748b;text-align:center;">Kombinasi gejala tidak cocok dengan aturan yang ada.</p>
    @include('exports.partials.word-shade-close')
@endif
