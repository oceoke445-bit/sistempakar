@php
    $checkIcon = export_check_success_icon_data_uri();
    $warningIcon = export_warning_icon_data_uri();
@endphp
<div style="font-family: {{ $fontFamily ?? 'DejaVu Sans, sans-serif' }}; font-size: {{ $baseFontSize ?? '11px' }}; color: #334155; line-height: 1.6;">

    <p style="margin: 0 0 20px; font-size: {{ $metaFontSize ?? '10px' }}; color: #64748b;">
        Hasil Diagnosa #{{ $d->id }} · {{ format_date_id($d->tanggal_diagnosa) }}
    </p>

    @if ($penyakit)
        <div style="background-color: #ecfdf5; border-radius: 14px; padding: 18px 20px; margin-bottom: 20px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border: none;">
                <tr>
                    @if ($checkIcon !== '')
                        <td width="48" valign="top" style="padding-right: 12px; border: none;">
                            <img src="{{ $checkIcon }}" width="40" height="40" alt="" style="display: block;">
                        </td>
                    @endif
                    <td valign="top" style="border: none;">
                        <p style="margin: 0 0 4px; font-size: {{ $headingFontSize ?? '14px' }}; font-weight: bold; color: #0f172a;">
                            Diagnosa Selesai
                        </p>
                        <p style="margin: 0 0 14px; font-size: {{ $bodyFontSize ?? '11px' }}; color: #475569;">
                            Berikut hasil identifikasi berdasarkan gejala yang Anda pilih.
                        </p>
                    </td>
                </tr>
            </table>
            <div style="background-color: #ffffff; border-radius: 10px; padding: 14px 16px;">
                <p style="margin: 0 0 8px; font-size: {{ $bodyFontSize ?? '11px' }};">
                    <span style="font-weight: 600; color: #334155;">Jenis Kerusakan :</span>
                    <span style="font-weight: bold; color: #0f172a;">{{ $penyakit->nama_penyakit }}</span>
                </p>
                <p style="margin: 0 0 8px; font-size: {{ $bodyFontSize ?? '11px' }};">
                    <span style="font-weight: 600; color: #334155;">Tingkat Kerusakan :</span>
                    <span style="font-weight: bold; color: #0f172a;">{{ $tingkatLabel }}</span>
                </p>
                <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }};">
                    <span style="font-weight: 600; color: #334155;">Rekomendasi :</span>
                    <span style="font-weight: bold; color: #0f172a;">{{ $rekomendasi }}</span>
                </p>
            </div>
        </div>

        @if (count($penyebabLines ?? []) || ($penyakit->deskripsi ?? null))
            <div style="background-color: #f8fafc; border-radius: 14px; padding: 18px 20px; margin-bottom: 20px;">
                <p style="margin: 0 0 10px; font-size: {{ $headingFontSize ?? '13px' }}; font-weight: bold; color: #0f172a;">
                    Penyebab Kerusakan
                </p>
                @if (count($penyebabLines ?? []))
                    <ul style="margin: 0; padding-left: 20px; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65;">
                        @foreach ($penyebabLines as $line)
                            <li style="margin-bottom: 6px;">{{ $line }}</li>
                        @endforeach
                    </ul>
                @else
                    <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65; white-space: pre-line;">
                        {{ $penyakit->deskripsi }}
                    </p>
                @endif
            </div>
        @endif

        <div style="background-color: #eff6ff; border-radius: 14px; padding: 18px 20px; margin-bottom: 20px;">
            <p style="margin: 0 0 10px; font-size: {{ $headingFontSize ?? '13px' }}; font-weight: bold; color: #0f172a;">
                Solusi Perbaikan
            </p>
            @if (count($solusiLines))
                <ol style="margin: 0; padding-left: 20px; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65;">
                    @foreach ($solusiLines as $line)
                        <li style="margin-bottom: 6px;">{{ $line }}</li>
                    @endforeach
                </ol>
            @elseif ($penyakit->solusi)
                <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65; white-space: pre-line;">
                    {{ $penyakit->solusi }}
                </p>
            @else
                <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }}; color: #64748b;">
                    Belum ada solusi tercatat untuk kerusakan ini.
                </p>
            @endif
        </div>

        @if (count($pencegahanLines ?? []) || ($penyakit->pencegahan ?? null))
            <div style="background-color: #fffbeb; border-radius: 14px; padding: 18px 20px; margin-bottom: 20px;">
                <p style="margin: 0 0 10px; font-size: {{ $headingFontSize ?? '13px' }}; font-weight: bold; color: #0f172a;">
                    Pencegahan
                </p>
                @if (count($pencegahanLines ?? []))
                    <ol style="margin: 0; padding-left: 20px; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65;">
                        @foreach ($pencegahanLines as $line)
                            <li style="margin-bottom: 6px;">{{ $line }}</li>
                        @endforeach
                    </ol>
                @else
                    <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65; white-space: pre-line;">
                        {{ $penyakit->pencegahan }}
                    </p>
                @endif
            </div>
        @endif

        <div style="background-color: #fffbeb; border-radius: 12px; padding: 14px 16px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border: none;">
                <tr>
                    @if ($warningIcon !== '')
                        <td width="40" valign="top" style="padding-right: 10px; border: none;">
                            <img src="{{ $warningIcon }}" width="32" height="32" alt="" style="display: block;">
                        </td>
                    @endif
                    <td valign="top" style="border: none;">
                        <p style="margin: 0 0 6px; font-size: {{ $headingFontSize ?? '13px' }}; font-weight: bold; color: #0f172a;">
                            Rekomendasi
                        </p>
                        <p style="margin: 0; font-size: {{ $bodyFontSize ?? '11px' }}; color: #334155; line-height: 1.65;">
                            Jika kerusakan masih berlanjut, sebaiknya hubungi teknisi.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div style="background-color: #f8fafc; border-radius: 14px; padding: 20px; text-align: center;">
            <p style="margin: 0 0 8px; font-size: 16px; font-weight: bold; color: #1e293b;">
                Tidak ada kerusakan yang terdeteksi
            </p>
            <p style="margin: 0; font-size: 11px; color: #64748b;">
                Kombinasi gejala tidak cocok dengan aturan yang ada.
            </p>
        </div>
    @endif
</div>
