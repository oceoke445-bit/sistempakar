<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Consolidate separate counts into a single query to save 4 database roundtrips
        $counts = DB::select("
            SELECT 
              (SELECT COUNT(*) FROM penyakit) as penyakit_count,
              (SELECT COUNT(*) FROM gejala) as gejala_count,
              (SELECT COUNT(*) FROM users WHERE role = 'user') as users_count,
              (SELECT COUNT(*) FROM diagnosa) as diagnosa_count,
              (SELECT COUNT(*) FROM relasi) as relasi_count
        ")[0];

        $penyakit = (int) $counts->penyakit_count;
        $gejala = (int) $counts->gejala_count;
        $users = (int) $counts->users_count;
        $diagnosa = (int) $counts->diagnosa_count;
        $relasi = (int) $counts->relasi_count;

        $distinctKerusakan = (int) DB::table('diagnosa')
            ->whereNotNull('hasil_penyakit')
            ->selectRaw('count(distinct hasil_penyakit) as c')
            ->value('c');

        $recent = DB::table('diagnosa')
            ->orderByDesc('tanggal_diagnosa')
            ->paginate(10)
            ->withQueryString();

        $userIds = $recent->pluck('id_user')->unique()->filter()->values()->all();
        $namaUser = [];
        if ($userIds !== []) {
            $namaUser = DB::table('users')->whereIn('id', $userIds)->pluck('nama_lengkap', 'id')->all();
        }

        $kodes = $recent->pluck('hasil_penyakit')->filter()->unique()->values()->all();
        $namaPenyakit = [];
        if ($kodes !== []) {
            $namaPenyakit = DB::table('penyakit')->whereIn('kode_penyakit', $kodes)->pluck('nama_penyakit', 'kode_penyakit')->all();
        }

        // 2. Consolidate group-by queries for different ranges
        $today = Carbon::now();
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $hourExpr = "CAST(strftime('%H', tanggal_diagnosa) AS INTEGER)";
            $monthExpr = "strftime('%Y-%m', tanggal_diagnosa)";
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $hourExpr = 'HOUR(tanggal_diagnosa)';
            $monthExpr = "DATE_FORMAT(tanggal_diagnosa, '%Y-%m')";
        } else {
            $hourExpr = 'CAST(EXTRACT(HOUR FROM tanggal_diagnosa) AS INTEGER)';
            $monthExpr = "TO_CHAR(tanggal_diagnosa, 'YYYY-MM')";
        }

        // A. 30 Days (for 1 Month range calculations if needed) & 7 Days
        $thirtyDaysAgo = (clone $today)->subDays(29)->startOfDay();
        $diagnosaByDate = DB::table('diagnosa')
            ->whereDate('tanggal_diagnosa', '>=', $thirtyDaysAgo->format('Y-m-d'))
            ->selectRaw('DATE(tanggal_diagnosa) as date, COUNT(*) as count')
            ->groupByRaw('DATE(tanggal_diagnosa)')
            ->pluck('count', 'date')
            ->all();

        // 7 Days (Default)
        $chartLabels = [];
        $chartSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = (clone $today)->subDays($i)->startOfDay();
            $chartLabels[] = $day->format('d/m');
            $dateKey = $day->format('Y-m-d');
            $countVal = 0;
            foreach ($diagnosaByDate as $k => $v) {
                if (str_starts_with($k, $dateKey)) {
                    $countVal = $v;
                    break;
                }
            }
            $chartSeries[] = (int) $countVal;
        }

        // 3 Days
        $threeDaysLabels = [];
        $threeDaysSeries = [];
        for ($i = 2; $i >= 0; $i--) {
            $day = (clone $today)->subDays($i)->startOfDay();
            $threeDaysLabels[] = $day->format('d/m');
            $dateKey = $day->format('Y-m-d');
            $countVal = 0;
            foreach ($diagnosaByDate as $k => $v) {
                if (str_starts_with($k, $dateKey)) {
                    $countVal = $v;
                    break;
                }
            }
            $threeDaysSeries[] = (int) $countVal;
        }

        // Hari Ini (Hourly Buckets for Today)
        $todayStart = (clone $today)->startOfDay();
        $diagnosaToday = DB::table('diagnosa')
            ->where('tanggal_diagnosa', '>=', $todayStart->format('Y-m-d H:i:s'))
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as count")
            ->groupByRaw($hourExpr)
            ->pluck('count', 'hour')
            ->all();

        $todayLabels = ['06:00', '09:00', '12:00', '15:00', '18:00', '21:00'];
        $todaySeries = [];
        $hoursRange = [
            '06:00' => [6, 7, 8],
            '09:00' => [9, 10, 11],
            '12:00' => [12, 13, 14],
            '15:00' => [15, 16, 17],
            '18:00' => [18, 19, 20],
            '21:00' => [21, 22, 23, 0, 1, 2, 3, 4, 5],
        ];
        foreach ($todayLabels as $lbl) {
            $sum = 0;
            foreach ($hoursRange[$lbl] as $h) {
                $sum += (int) ($diagnosaToday[$h] ?? 0);
            }
            $todaySeries[] = $sum;
        }

        // 1 Bulan Terakhir / Per Bulan (Months of the year)
        $thisYearStart = (clone $today)->subMonths(11)->startOfMonth();
        $diagnosaByMonth = DB::table('diagnosa')
            ->where('tanggal_diagnosa', '>=', $thisYearStart->format('Y-m-d'))
            ->selectRaw("{$monthExpr} as ym, COUNT(*) as count")
            ->groupByRaw($monthExpr)
            ->pluck('count', 'ym')
            ->all();

        $monthLabels = [];
        $monthSeries = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        for ($i = 11; $i >= 0; $i--) {
            $monthObj = (clone $today)->subMonths($i)->startOfMonth();
            $ymKey = $monthObj->format('Y-m');
            $monthNum = (int) $monthObj->format('n');
            $monthLabels[] = $indonesianMonths[$monthNum];
            $monthSeries[] = (int) ($diagnosaByMonth[$ymKey] ?? 0);
        }

        // 3. Optimize confidence bucket counts directly in SQL (no massive data retrieval)
        $donutCounts = DB::table('diagnosa')
            ->selectRaw("
                COUNT(CASE WHEN confidence >= 0.5 THEN 1 END) as berat,
                COUNT(CASE WHEN confidence < 0.5 OR confidence IS NULL THEN 1 END) as ringan
            ")
            ->first();

        $donut = [
            'ringan' => (int) ($donutCounts->ringan ?? 0),
            'berat' => (int) ($donutCounts->berat ?? 0),
        ];

        $masterPreview = DB::table('penyakit')->orderBy('kode_penyakit')->limit(4)->get();
        $printersList = DB::table('printers')->orderBy('id', 'asc')->get();

        return view('admin.dashboard', compact(
            'penyakit',
            'gejala',
            'users',
            'diagnosa',
            'relasi',
            'distinctKerusakan',
            'recent',
            'namaUser',
            'namaPenyakit',
            'chartLabels',
            'chartSeries',
            'threeDaysLabels',
            'threeDaysSeries',
            'todayLabels',
            'todaySeries',
            'monthLabels',
            'monthSeries',
            'donut',
            'masterPreview',
            'printersList',
        ));
    }
}
