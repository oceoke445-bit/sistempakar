<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $tingkat = (string) $request->query('tingkat', '');

        $query = DB::table('diagnosa');

        if ($q !== '') {
            $like = db_like_op();
            $kodeFromNama = DB::table('penyakit')
                ->where(function ($w) use ($q, $like) {
                    $w->where('nama_penyakit', $like, '%'.$q.'%')
                        ->orWhere('kode_penyakit', $like, '%'.$q.'%');
                })
                ->pluck('kode_penyakit')
                ->all();
            $userIdsMatch = DB::table('users')
                ->where(function ($w) use ($q, $like) {
                    $w->where('nama_lengkap', $like, '%'.$q.'%')
                        ->orWhere('email', $like, '%'.$q.'%');
                })
                ->pluck('id')
                ->all();
            $query->where(function ($w) use ($q, $kodeFromNama, $userIdsMatch, $like) {
                $w->whereRaw(db_cast_text_like_sql('id'), ['%'.$q.'%'])
                    ->orWhere('hasil_penyakit', $like, '%'.$q.'%');
                if ($kodeFromNama !== []) {
                    $w->orWhereIn('hasil_penyakit', $kodeFromNama);
                }
                if ($userIdsMatch !== []) {
                    $w->orWhereIn('id_user', $userIdsMatch);
                }
            });
        }

        if (in_array($tingkat, ['ringan', 'berat'], true)) {
            $query->where(function ($w) use ($tingkat) {
                if ($tingkat === 'berat') {
                    $w->where('confidence', '>=', 0.5);
                } else {
                    $w->where(function ($inner) {
                        $inner->whereNull('confidence')->orWhere('confidence', '<', 0.5);
                    });
                }
            });
        }

        $rows = $query->orderByDesc('tanggal_diagnosa')->paginate(10)->withQueryString();

        $diagnosaIds = collect($rows->items())->pluck('id')->all();
        $gejalaByDiagnosa = [];
        if ($diagnosaIds !== []) {
            $details = DB::table('diagnosa_detail')
                ->whereIn('id_diagnosa', $diagnosaIds)
                ->orderBy('kode_gejala')
                ->get(['id_diagnosa', 'kode_gejala']);
            foreach ($details as $detail) {
                $gejalaByDiagnosa[$detail->id_diagnosa][] = $detail->kode_gejala;
            }
        }

        $userIds = collect($rows->items())->pluck('id_user')->unique()->filter()->values()->all();
        $namaUser = [];
        if ($userIds !== []) {
            $namaUser = DB::table('users')->whereIn('id', $userIds)->pluck('nama_lengkap', 'id')->all();
        }

        $allGejalaKodes = collect($gejalaByDiagnosa)->flatten()->unique()->values()->all();
        $namaGejala = $allGejalaKodes !== []
            ? DB::table('gejala')->whereIn('kode_gejala', $allGejalaKodes)->pluck('nama_gejala', 'kode_gejala')->all()
            : [];

        return view('admin.riwayat.index', [
            'rows' => $rows,
            'gejalaByDiagnosa' => $gejalaByDiagnosa,
            'namaGejala' => $namaGejala,
            'namaUser' => $namaUser,
            'notice' => $request->query('notice'),
            'error' => $request->query('error'),
            'q' => $q,
            'tingkat' => $tingkat,
        ]);
    }

    public function destroy(Request $request)
    {
        $id = (int) $request->input('id');
        if ($id <= 0) {
            return redirect('/admin/riwayat?error='.urlencode('ID diagnosa tidak valid.'));
        }

        $t = DB::table('diagnosa')->where('id', $id)->value('tanggal_diagnosa');
        if (! $t) {
            return redirect('/admin/riwayat?error='.urlencode('Riwayat diagnosa tidak ditemukan.'));
        }

        try {
            DB::table('diagnosa')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            return redirect('/admin/riwayat?error='.urlencode('Gagal menghapus riwayat diagnosa.'));
        }

        $when = format_date_id((string) $t);

        return redirect('/admin/riwayat?notice='.urlencode("Diagnosa #{$id} ({$when}) berhasil dihapus dari riwayat."));
    }
}
