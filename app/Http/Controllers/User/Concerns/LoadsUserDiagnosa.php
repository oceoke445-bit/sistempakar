<?php

namespace App\Http\Controllers\User\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait LoadsUserDiagnosa
{
    /** @return array<string, mixed> */
    protected function loadUserDiagnosa(Request $request, int $id): array
    {
        $auth = $request->session()->get('auth');
        $d = DB::table('diagnosa')->where('id', $id)->where('id_user', $auth['id'])->first();
        if (! $d) {
            abort(404);
        }

        $penyakit = $d->hasil_penyakit
            ? DB::table('penyakit')->where('kode_penyakit', $d->hasil_penyakit)->first()
            : null;

        $kodes = DB::table('diagnosa_detail')->where('id_diagnosa', $id)->pluck('kode_gejala')->all();
        $namaGejala = [];
        if ($kodes !== []) {
            $namaGejala = DB::table('gejala')->whereIn('kode_gejala', $kodes)->pluck('nama_gejala', 'kode_gejala')->all();
        }

        $pct = $d->confidence !== null ? (float) $d->confidence * 100 : null;
        $tingkat = $penyakit && $penyakit->tingkat ? $penyakit->tingkat : 'ringan';
        $tingkatLabel = ucfirst(strtolower((string) $tingkat));
        $penyebabLines = $penyakit && $penyakit->deskripsi
            ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->deskripsi)))
            : [];
        $solusiLines = $penyakit && $penyakit->solusi
            ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->solusi)))
            : [];
        $pencegahanLines = $penyakit && $penyakit->pencegahan
            ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->pencegahan)))
            : [];
        $tindakanLabel = ($d->tindakan ?? null)
            ? diagnosa_tindakan_badge($d->tindakan)['label']
            : null;
        $rekomendasi = diagnosa_rekomendasi_label($d->tindakan ?? null, $penyakit->tingkat ?? null);
        $firstName = trim(explode(' ', trim($auth['nama_lengkap'] ?? 'Pengguna'))[0] ?: 'Pengguna');

        return compact('d', 'penyakit', 'kodes', 'namaGejala', 'pct', 'tingkat', 'tingkatLabel', 'penyebabLines', 'solusiLines', 'pencegahanLines', 'tindakanLabel', 'rekomendasi', 'firstName');
    }
}
