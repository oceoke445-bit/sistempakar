<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $legacyMap = [
        'G001' => 'G01',
        'G002' => 'G02',
        'G003' => 'G03',
        'G004' => 'G05',
        'G005' => 'G09',
        'G006' => 'G06',
        'G007' => 'G08',
        'G008' => 'G04',
        'G009' => 'G11',
        'G010' => 'G10',
        'G011' => 'G03',
        'G012' => 'G07',
    ];

    /** @var array<int, array{0: string, 1: string}> */
    private array $gejala = [
        ['G01', 'Printer tidak menyala'],
        ['G02', 'Lampu indikator mati'],
        ['G03', 'Printer tidak merespon perintah'],
        ['G04', 'Hasil cetak kosong'],
        ['G05', 'Hasil cetak bergaris'],
        ['G06', 'Tinta tidak keluar'],
        ['G07', 'Kertas tidak tertarik'],
        ['G08', 'Kertas Macet (Paper Jam)'],
        ['G09', 'Hasil cetak buram'],
        ['G10', 'Printer mengeluarkan suara kasar'],
        ['G11', 'Cartridge tidak terdeteksi'],
        ['G12', 'Muncul pesan error di komputer'],
    ];

    /** @var array<int, array{0: string, 1: string}> */
    private array $relasi = [
        ['K001', 'G01'], ['K001', 'G02'],
        ['K002', 'G02'], ['K002', 'G10'],
        ['K003', 'G05'], ['K003', 'G09'],
        ['K004', 'G08'], ['K004', 'G07'],
        ['K005', 'G06'], ['K005', 'G11'],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            DB::table('relasi')->delete();

            foreach ($this->gejala as [$kode, $nama]) {
                if (! DB::table('gejala')->where('kode_gejala', $kode)->exists()) {
                    DB::table('gejala')->insert([
                        'kode_gejala' => $kode,
                        'nama_gejala' => $nama,
                    ]);
                }
            }

            foreach ($this->legacyMap as $old => $new) {
                DB::table('diagnosa_detail')->where('kode_gejala', $old)->update(['kode_gejala' => $new]);
            }

            $validCodes = array_column($this->gejala, 0);
            DB::table('diagnosa_detail')->whereNotIn('kode_gejala', $validCodes)->delete();

            DB::table('gejala')->whereNotIn('kode_gejala', $validCodes)->delete();

            foreach ($this->gejala as [$kode, $nama]) {
                DB::table('gejala')->updateOrInsert(
                    ['kode_gejala' => $kode],
                    ['nama_gejala' => $nama]
                );
            }

            foreach ($this->relasi as [$penyakit, $gejalaKode]) {
                if (! DB::table('penyakit')->where('kode_penyakit', $penyakit)->exists()) {
                    continue;
                }
                if (! DB::table('gejala')->where('kode_gejala', $gejalaKode)->exists()) {
                    continue;
                }

                DB::table('relasi')->insert([
                    'kode_penyakit' => $penyakit,
                    'kode_gejala' => $gejalaKode,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Irreversible — re-run MjmscanDataSeeder manually if needed.
    }
};
