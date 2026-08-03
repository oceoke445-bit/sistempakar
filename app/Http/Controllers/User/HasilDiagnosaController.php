<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\LoadsUserDiagnosa;
use App\Support\WordMhtmlExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HasilDiagnosaController extends Controller
{
    use LoadsUserDiagnosa;

    public function show(Request $request, int $id)
    {
        return view('user.hasil-diagnosa', $this->loadUserDiagnosa($request, $id));
    }

    public function exportPdf(Request $request, int $id)
    {
        $data = $this->loadUserDiagnosa($request, $id);
        $filename = 'hasil-diagnosa-'.$id.'.pdf';

        return Pdf::loadView('exports.hasil-diagnosa-konsultasi-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function exportWord(Request $request, int $id)
    {
        $data = $this->loadUserDiagnosa($request, $id);
        $filename = 'hasil-diagnosa-'.$id.'.doc';
        $embeds = export_word_embed_paths();

        return WordMhtmlExporter::download(
            view('exports.hasil-diagnosa-konsultasi-word', $data)->render(),
            $filename,
            [
                'checkicon' => $embeds['checkicon'],
                'warningicon' => $embeds['warningicon'],
            ]
        );
    }

    public function setTindakan(Request $request, int $id)
    {
        $request->validate([
            'tindakan' => 'required|in:sendiri,teknisi',
        ]);

        $auth = $request->session()->get('auth');
        $row = DB::table('diagnosa')->where('id', $id)->where('id_user', $auth['id'])->first();
        if (! $row) {
            abort(404);
        }

        DB::table('diagnosa')
            ->where('id', $id)
            ->where('id_user', $auth['id'])
            ->update(['tindakan' => $request->input('tindakan')]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'redirect' => route('user.riwayat'),
            ]);
        }

        return redirect()->route('user.riwayat', ['notice' => 'Diagnosa telah disimpan ke riwayat.']);
    }

    public function simpanRiwayat(Request $request, int $id)
    {
        $auth = $request->session()->get('auth');
        $row = DB::table('diagnosa')->where('id', $id)->where('id_user', $auth['id'])->first();
        if (! $row) {
            abort(404);
        }

        if (! $row->tindakan) {
            $penyakit = $row->hasil_penyakit
                ? DB::table('penyakit')->where('kode_penyakit', $row->hasil_penyakit)->first()
                : null;
            $tingkat = strtolower((string) ($penyakit->tingkat ?? 'sedang'));
            $tindakan = $tingkat === 'berat' ? 'teknisi' : 'sendiri';

            DB::table('diagnosa')
                ->where('id', $id)
                ->where('id_user', $auth['id'])
                ->update(['tindakan' => $tindakan]);
        }

        return redirect('/user/riwayat?notice='.urlencode('Diagnosa telah disimpan ke riwayat.'));
    }
}
