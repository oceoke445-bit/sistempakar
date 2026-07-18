<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\LoadsUserDiagnosa;
use App\Support\WordMhtmlExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    use LoadsUserDiagnosa;
    public function index(Request $request)
    {
        $auth = $request->session()->get('auth');
        $q = trim((string) $request->query('q', ''));
        $tingkat = (string) $request->query('tingkat', '');

        $query = DB::table('diagnosa')->where('id_user', $auth['id']);

        if ($q !== '') {
            $like = db_like_op();
            $kodeFromNama = DB::table('penyakit')
                ->where(function ($w) use ($q, $like) {
                    $w->where('nama_penyakit', $like, '%'.$q.'%')
                        ->orWhere('kode_penyakit', $like, '%'.$q.'%');
                })
                ->pluck('kode_penyakit')
                ->all();
            $query->where(function ($w) use ($q, $kodeFromNama, $like) {
                $w->where('hasil_penyakit', $like, '%'.$q.'%')
                    ->orWhereRaw(db_cast_text_like_sql('id'), ['%'.$q.'%']);
                if ($kodeFromNama !== []) {
                    $w->orWhereIn('hasil_penyakit', $kodeFromNama);
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
        $penyakitMap = DB::table('penyakit')->get()->keyBy('kode_penyakit');
        $firstName = trim(explode(' ', trim($auth['nama_lengkap'] ?? 'Pengguna'))[0] ?: 'Pengguna');

        return view('user.riwayat.index', [
            'rows' => $rows,
            'penyakitMap' => $penyakitMap,
            'notice' => $request->query('notice'),
            'q' => $q,
            'tingkat' => $tingkat,
            'firstName' => $firstName,
        ]);
    }

    public function show(Request $request, int $id)
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

        return view('user.riwayat.show', compact('d', 'penyakit', 'kodes', 'namaGejala', 'pct'));
    }

    public function exportPdf(Request $request, int $id)
    {
        $data = $this->loadUserDiagnosa($request, $id);
        $filename = 'detail-riwayat-diagnosa-'.$id.'.pdf';

        return Pdf::loadView('exports.hasil-diagnosa-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function exportWord(Request $request, int $id)
    {
        $data = $this->loadUserDiagnosa($request, $id);
        $filename = 'detail-riwayat-diagnosa-'.$id.'.doc';
        $embeds = export_word_embed_paths();

        return WordMhtmlExporter::download(
            view('exports.hasil-diagnosa-word', $data)->render(),
            $filename,
            ['printericon' => $embeds['printericon']]
        );
    }

    public function destroy(Request $request)
    {
        $auth = $request->session()->get('auth');
        $id = (int) $request->input('id');
        if (! $id) {
            return redirect('/user/riwayat');
        }

        $before = DB::table('diagnosa')->where('id', $id)->where('id_user', $auth['id'])->value('tanggal_diagnosa');
        DB::table('diagnosa')->where('id', $id)->where('id_user', $auth['id'])->delete();

        $when = $before ? format_date_id((string) $before) : '#'.$id;

        return redirect('/user/riwayat?notice='.urlencode("Riwayat diagnosa tanggal {$when} berhasil dihapus."));
    }
}
