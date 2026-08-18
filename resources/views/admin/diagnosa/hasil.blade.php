@extends('layouts.dashboard')
@section('title', 'Hasil Diagnosa')
@section('content')
@php
    $tingkat = diagnosis_tingkat_label($d->confidence !== null ? (float) $d->confidence : null);
    $tingkatClass = $tingkat === 'Berat' ? 'border border-red-200 bg-red-50 text-red-800' : ($tingkat === 'Sedang' ? 'border border-amber-200 bg-amber-50 text-amber-900' : 'border border-emerald-200 bg-emerald-50 text-emerald-800');
    $penyebabLines = $penyakit && $penyakit->deskripsi ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->deskripsi))) : [];
    $solusiLines = $penyakit && $penyakit->solusi ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->solusi))) : [];
    $pencegahanLines = $penyakit && $penyakit->pencegahan ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->pencegahan))) : [];
@endphp
<div class="mx-auto max-w-4xl space-y-8">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#152238] sm:text-3xl">Hasil Diagnosa</h1>
            <p class="mt-1 text-[15px] text-slate-600">Berikut adalah hasil analisa berdasarkan gejala yang dipilih.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sm:text-sm">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">1</span>
                Pilih Gejala
            </div>
            <i class="bi bi-chevron-right text-slate-300"></i>
            <div class="flex items-center gap-2 rounded-full border border-blue-600 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800 sm:text-sm">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</span>
                Hasil Diagnosa
            </div>
            <button type="button" onclick="window.print()" class="ml-auto inline-flex items-center gap-2 rounded-xl border-2 border-brand-600 bg-white px-4 py-2 text-sm font-bold text-brand-600 shadow-sm hover:bg-blue-50 print:hidden">
                <i class="bi bi-printer"></i> Cetak hasil
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_8px_40px_rgba(15,23,42,0.08)] print:border-0 print:shadow-none">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-8 sm:px-8 print:bg-white">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="flex shrink-0 justify-center sm:block">
                    <img src="{{ asset('images/printer.png') }}" alt="" class="h-28 w-auto max-w-[200px] object-contain sm:h-32" width="200" height="200" decoding="async">
                </div>
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Diagnosa kerusakan</p>
                    @if ($penyakit)
                        <h2 class="mt-2 text-2xl font-bold leading-tight tracking-tight text-red-600 sm:text-3xl">{{ $penyakit->nama_penyakit }}</h2>
                    @else
                        <h2 class="mt-2 text-2xl font-bold text-slate-700">Tidak ada hasil spesifik</h2>
                    @endif
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $tingkatClass }}">Tingkat kerusakan: {{ $tingkat }}</span>
                        @if ($pct !== null)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Kecocokan {{ number_format($pct, 1) }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8 px-5 py-8 sm:px-8">
            @if (count($penyebabLines))
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Penyebab</h3>
                    <ul class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-700">
                        @foreach ($penyebabLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @elseif ($penyakit && $penyakit->deskripsi)
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Penyebab</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $penyakit->deskripsi }}</p>
                </div>
            @endif

            @if (count($solusiLines))
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Solusi</h3>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-700">
                        @foreach ($solusiLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ol>
                </div>
            @elseif ($penyakit && $penyakit->solusi)
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Solusi</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $penyakit->solusi }}</p>
                </div>
            @endif

            @if (count($pencegahanLines))
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Pencegahan</h3>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-700">
                        @foreach ($pencegahanLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ol>
                </div>
            @elseif ($penyakit && $penyakit->pencegahan)
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Pencegahan</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700">{{ $penyakit->pencegahan }}</p>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Gejala yang dipilih</h3>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($kodes as $k)
                        <li class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">{{ $namaGejala[$k] ?? $k }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-6 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <i class="bi bi-file-earmark-check text-xl"></i>
                    </div>
                    <p class="mt-4 font-bold text-emerald-950">Perbaikan sendiri</p>
                    <p class="mt-2 text-sm leading-relaxed text-emerald-900/85">Kerusakan ini dapat diperbaiki sendiri dengan langkah-langkah di atas jika kondisi aman.</p>
                    <form method="post" action="{{ route('admin.diagnosa.tindakan', ['id' => $d->id]) }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="tindakan" value="sendiri">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white shadow-md hover:bg-emerald-700 sm:w-auto sm:px-6">Lakukan sendiri</button>
                    </form>
                </div>
                <div class="rounded-2xl border border-orange-200/80 bg-orange-50/80 p-6 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500 text-white">
                        <i class="bi bi-wrench-adjustable text-xl"></i>
                    </div>
                    <p class="mt-4 font-bold text-orange-950">Panggil teknisi</p>
                    <p class="mt-2 text-sm leading-relaxed text-orange-900/85">Jika kerusakan tidak dapat diatasi atau tingkat berat, hubungi teknisi resmi.</p>
                    @php $teknisiPhone = config('app.teknisi_phone'); @endphp
                    <form method="post" action="{{ route('admin.diagnosa.tindakan', ['id' => $d->id]) }}" class="mt-5"
                          @if($teknisiPhone) onsubmit="window.open('https://wa.me/{{ $teknisiPhone }}','_blank')" @endif>
                        @csrf
                        <input type="hidden" name="tindakan" value="teknisi">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-orange-500 py-3 text-sm font-bold text-white shadow-md hover:bg-orange-600 sm:w-auto sm:px-6">Hubungi teknisi</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <a href="{{ route('admin.riwayat') }}" class="text-sm font-bold text-brand-600 hover:text-brand-700">Ke riwayat diagnosa →</a>
            </div>
        </div>
    </div>
</div>
@endsection
