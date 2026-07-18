<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyakitController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $builder = DB::table('penyakit');
        if ($q !== '') {
            $like = db_like_op();
            $builder->where(function ($w) use ($q, $like) {
                $w->where('nama_penyakit', $like, '%'.$q.'%')
                    ->orWhere('kode_penyakit', $like, '%'.$q.'%');
            });
        }
        $rows = $builder->orderBy('kode_penyakit')->paginate(10)->withQueryString();
        $editing = $request->query('edit')
            ? DB::table('penyakit')->where('kode_penyakit', $request->query('edit'))->first()
            : null;

        return view('admin.penyakit.index', [
            'rows' => $rows,
            'editing' => $editing,
            'success' => $request->query('success'),
            'notice' => $request->query('notice'),
            'error' => $request->query('error'),
            'q' => $q,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_penyakit' => 'required|string|max:50',
            'nama_penyakit' => 'required|string|max:255',
            'tingkat' => 'required|string|in:ringan,berat',
            'deskripsi' => 'nullable|string',
            'solusi' => 'nullable|string',
            'pencegahan' => 'nullable|string',
        ]);

        try {
            DB::table('penyakit')->insert([
                'kode_penyakit' => trim($request->input('kode_penyakit')),
                'nama_penyakit' => trim($request->input('nama_penyakit')),
                'tingkat' => $request->input('tingkat'),
                'deskripsi' => $request->input('deskripsi'),
                'solusi' => $request->input('solusi'),
                'pencegahan' => $request->input('pencegahan'),
            ]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'duplicate') || str_contains($e->getMessage(), 'unique')
                ? 'Kode kerusakan sudah ada. Gunakan kode lain atau edit data yang sudah tersimpan.'
                : 'Gagal menyimpan data.';

            return redirect('/admin/penyakit?error='.urlencode($msg));
        }

        return redirect('/admin/penyakit?success=1');
    }

    public function update(Request $request)
    {
        $request->validate([
            'kode_penyakit' => 'required|string|max:50',
            'nama_penyakit' => 'required|string|max:255',
            'tingkat' => 'required|string|in:ringan,berat',
            'deskripsi' => 'nullable|string',
            'solusi' => 'nullable|string',
            'pencegahan' => 'nullable|string',
        ]);

        $kode = trim($request->input('kode_penyakit'));
        if (! DB::table('penyakit')->where('kode_penyakit', $kode)->exists()) {
            return redirect('/admin/penyakit?error='.urlencode('Data kerusakan tidak ditemukan.'));
        }

        DB::table('penyakit')
            ->where('kode_penyakit', $kode)
            ->update([
                'nama_penyakit' => trim($request->input('nama_penyakit')),
                'tingkat' => $request->input('tingkat'),
                'deskripsi' => $request->input('deskripsi'),
                'solusi' => $request->input('solusi'),
                'pencegahan' => $request->input('pencegahan'),
            ]);

        return redirect('/admin/penyakit?success=1');
    }

    public function destroy(Request $request)
    {
        $kode = trim((string) $request->input('kode_penyakit'));
        if ($kode === '') {
            return redirect('/admin/penyakit?error='.urlencode('Kode kerusakan tidak valid.'));
        }

        if (! DB::table('penyakit')->where('kode_penyakit', $kode)->exists()) {
            return redirect('/admin/penyakit?error='.urlencode('Data kerusakan tidak ditemukan.'));
        }

        try {
            DB::table('penyakit')->where('kode_penyakit', $kode)->delete();
        } catch (\Throwable $e) {
            return redirect('/admin/penyakit?error='.urlencode('Gagal menghapus data kerusakan. Pastikan tidak ada ketergantungan data.'));
        }

        return redirect('/admin/penyakit?notice='.urlencode("Data kerusakan {$kode} berhasil dihapus."));
    }
}
