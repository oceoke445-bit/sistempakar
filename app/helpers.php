<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

if (! function_exists('verify_password_hash')) {
    /** Laravel bcrypt + hash bcryptjs (Next.js) */
    function verify_password_hash(string $plain, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        try {
            if (Hash::check($plain, $hash)) {
                return true;
            }
        } catch (\RuntimeException) {
            // bcryptjs / format hash lama — coba password_verify di bawah
        }

        return password_verify($plain, $hash);
    }
}

if (! function_exists('wib_from_db')) {
    function wib_from_db(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->utc()->timezone('Asia/Jakarta');
    }
}

if (! function_exists('format_date_id')) {
    function format_date_id(?string $iso): string
    {
        $dt = wib_from_db($iso);

        return $dt ? $dt->format('d/m/Y, H:i') : '—';
    }
}

if (! function_exists('db_like_op')) {
    /** Postgres: ilike; SQLite/MySQL: like (SQLite LIKE is ASCII case-insensitive). */
    function db_like_op(): string
    {
        return \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}

if (! function_exists('db_cast_text_like_sql')) {
    /** SQL fragment with one `?` binding, e.g. CAST(id AS TEXT) LIKE ? */
    function db_cast_text_like_sql(string $column): string
    {
        $op = db_like_op() === 'ilike' ? 'ILIKE' : 'LIKE';

        return "CAST({$column} AS TEXT) {$op} ?";
    }
}

if (! function_exists('format_date_id_long')) {
    /** Contoh: 29 Mei 2026 14:32 */
    function format_date_id_long(?string $iso): string
    {
        $dt = wib_from_db($iso);
        if (! $dt) {
            return '—';
        }
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $dt->format('j').' '.$months[(int) $dt->format('n')].' '.$dt->format('Y H:i');
    }
}

if (! function_exists('format_date_id_day')) {
    /** Contoh: 20 Mei 2026 */
    function format_date_id_day(?string $iso): string
    {
        $dt = wib_from_db($iso);
        if (! $dt) {
            return '—';
        }
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $dt->format('j').' '.$months[(int) $dt->format('n')].' '.$dt->format('Y');
    }
}

if (! function_exists('format_report_period_range')) {
    function format_report_period_range(?string $startYmd, ?string $endYmd): string
    {
        if ($startYmd && $endYmd) {
            return format_date_id_day($startYmd.'T00:00:00Z').' s/d '.format_date_id_day($endYmd.'T00:00:00Z');
        }

        return 'Semua periode';
    }
}

if (! function_exists('diagnosa_rekomendasi_label')) {
    function diagnosa_rekomendasi_label(?string $tindakan, ?string $penyakitTingkat = null): string
    {
        return match ($tindakan) {
            'sendiri' => 'Perbaiki Sendiri',
            'teknisi' => 'Hubungi Teknisi',
            default => strtolower((string) $penyakitTingkat) === 'berat' ? 'Hubungi Teknisi' : 'Perbaiki Sendiri',
        };
    }
}

if (! function_exists('diagnosis_tingkat_label')) {
    /** @param  float|null  $confidence  nilai 0–1 di kolom diagnosa.confidence */
    function diagnosis_tingkat_label(?float $confidence): string
    {
        if ($confidence === null) {
            return 'Ringan';
        }

        return (float) $confidence >= 0.5 ? 'Berat' : 'Ringan';
    }
}

if (! function_exists('diagnosis_tingkat_bucket')) {
    /** @return 'ringan'|'berat' */
    function diagnosis_tingkat_bucket(?float $confidence): string
    {
        if ($confidence === null) {
            return 'ringan';
        }

        return (float) $confidence >= 0.5 ? 'berat' : 'ringan';
    }
}

if (! function_exists('diagnosa_tindakan_badge')) {
    /** @return array{label: string, class: string} */
    function diagnosa_tindakan_badge(?string $tindakan): array
    {
        return match ($tindakan) {
            'sendiri' => [
                'label' => 'Lakukan Sendiri',
                'class' => 'inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 shadow-sm hover:bg-emerald-100/70 transition-colors',
            ],
            'teknisi' => [
                'label' => 'Teknisi',
                'class' => 'inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 shadow-sm hover:bg-amber-100/70 transition-colors',
            ],
            default => [
                'label' => 'Belum dipilih',
                'class' => 'inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-500',
            ],
        };
    }
}

if (! function_exists('export_printer_icon_data_uri')) {
    /** PNG data URI for PDF/Word export (DomPDF & Word do not render inline SVG reliably). */
    function export_printer_icon_data_uri(): string
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $dir = public_path('images');
        $path = $dir.DIRECTORY_SEPARATOR.'printer-diagnosa-export.png';

        if (! is_file($path)) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $w = 96;
            $h = 96;
            $im = imagecreatetruecolor($w, $h);
            imagesavealpha($im, true);
            imagealphablending($im, false);
            $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $transparent);
            imagealphablending($im, true);

            $dark = imagecolorallocate($im, 30, 41, 59);
            $white = imagecolorallocate($im, 255, 255, 255);
            $gray = imagecolorallocate($im, 148, 163, 184);
            $red = imagecolorallocate($im, 239, 68, 68);
            $slate = imagecolorallocate($im, 71, 85, 105);
            $yellow = imagecolorallocate($im, 234, 179, 8);
            $green = imagecolorallocate($im, 34, 197, 94);

            imagefilledrectangle($im, 32, 10, 64, 24, $white);
            imagerectangle($im, 32, 10, 64, 24, $gray);
            imagefilledrectangle($im, 28, 20, 72, 35, $slate);
            imagefilledrectangle($im, 12, 32, 84, 74, $dark);
            imagefilledrectangle($im, 22, 52, 78, 58, $dark);
            imagefilledrectangle($im, 24, 56, 76, 84, $white);
            imagerectangle($im, 24, 56, 76, 84, $gray);

            foreach ([62, 67, 72, 77] as $y) {
                imageline($im, 30, $y, 70, $y, $red);
            }

            imagefilledellipse($im, 20, 42, 5, 5, $yellow);
            imagefilledellipse($im, 27, 42, 5, 5, $green);

            imagepng($im, $path);
            imagedestroy($im);
        }

        $cached = 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));

        return $cached;
    }
}

if (! function_exists('export_png_icon_data_uri')) {
    /**
     * @param  callable(\GdImage): void  $draw
     */
    function export_png_icon_data_uri(string $filename, int $size, callable $draw): string
    {
        static $cache = [];
        if (isset($cache[$filename])) {
            return $cache[$filename];
        }

        $dir = public_path('images');
        $path = $dir.DIRECTORY_SEPARATOR.$filename;

        if (! is_file($path)) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if (! function_exists('imagecreatetruecolor')) {
                $cache[$filename] = '';

                return '';
            }

            $im = imagecreatetruecolor($size, $size);
            imagesavealpha($im, true);
            imagealphablending($im, false);
            $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $transparent);
            imagealphablending($im, true);

            $draw($im);

            imagepng($im, $path);
            imagedestroy($im);
        }

        $cache[$filename] = is_file($path)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path))
            : '';

        return $cache[$filename];
    }
}

if (! function_exists('export_check_success_icon_data_uri')) {
    /** Centang hijau — Diagnosa Selesai (PDF/Word). */
    function export_check_success_icon_data_uri(): string
    {
        return export_png_icon_data_uri('check-success-export.png', 80, function ($im): void {
            $green = imagecolorallocate($im, 34, 197, 94);
            $white = imagecolorallocate($im, 255, 255, 255);
            $cx = 40;
            $cy = 40;
            imagefilledellipse($im, $cx, $cy, 72, 72, $green);
            imagefilledpolygon($im, [22, 42, 34, 56, 58, 26, 52, 20, 34, 48, 28, 36], $white);
        });
    }
}

if (! function_exists('export_warning_icon_data_uri')) {
    /** Segitiga peringatan — Rekomendasi (PDF/Word). */
    function export_warning_icon_data_uri(): string
    {
        return export_png_icon_data_uri('warning-export.png', 64, function ($im): void {
            $amber = imagecolorallocate($im, 217, 119, 6);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagefilledpolygon($im, [32, 8, 56, 52, 8, 52], $amber);
            imagefilledrectangle($im, 29, 22, 35, 38, $white);
            imagefilledellipse($im, 32, 46, 8, 8, $white);
        });
    }
}

if (! function_exists('user_foto_profil_url')) {
    function user_foto_profil_url(?object $user): ?string
    {
        $path = $user->foto_profil ?? null;
        if (! $path || ! is_file(public_path($path))) {
            return null;
        }

        return asset($path);
    }
}

if (! function_exists('no_wa_has_allowed_prefix')) {
    /** Awalan nomor WA wajib 62, 0, atau 8 (cek 62 dulu agar tidak tertimpa 8). */
    function no_wa_has_allowed_prefix(string $digits): bool
    {
        return str_starts_with($digits, '62')
            || str_starts_with($digits, '0')
            || str_starts_with($digits, '8');
    }
}

if (! function_exists('normalize_no_wa')) {
    /** Normalisasi ke format 628xxxxxxxxxx (tanpa +). Kosong → null. */
    function normalize_no_wa(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        if (! no_wa_has_allowed_prefix($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return null;
    }
}

if (! function_exists('format_no_wa_display')) {
    function format_no_wa_display(?string $noWa): string
    {
        if (! $noWa) {
            return '—';
        }

        return $noWa;
    }
}

if (! function_exists('export_word_embed_paths')) {
    /** @return array<string, string> Content-ID => path untuk MHTML Word */
    function export_word_embed_paths(): array
    {
        export_printer_icon_data_uri();
        export_check_success_icon_data_uri();
        export_warning_icon_data_uri();

        $dir = public_path('images');

        return [
            'printericon' => $dir.DIRECTORY_SEPARATOR.'printer-diagnosa-export.png',
            'checkicon' => $dir.DIRECTORY_SEPARATOR.'check-success-export.png',
            'warningicon' => $dir.DIRECTORY_SEPARATOR.'warning-export.png',
        ];
    }
}

if (! function_exists('teknisi_phone_digits')) {
    function teknisi_phone_digits(): string
    {
        $raw = (string) config('app.teknisi_phone', '6281234567890');
        $digits = preg_replace('/\D/', '', $raw) ?? '';
        if ($digits === '') {
            return '6281234567890';
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }
}

if (! function_exists('teknisi_phone_display')) {
    function teknisi_phone_display(): string
    {
        $digits = teknisi_phone_digits();
        if (str_starts_with($digits, '62') && strlen($digits) >= 11) {
            $local = substr($digits, 2);

            return '+62 '.substr($local, 0, 3).'-'.substr($local, 3, 4).'-'.substr($local, 7);
        }

        return '+'.$digits;
    }
}
