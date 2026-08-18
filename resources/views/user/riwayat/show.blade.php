@extends('layouts.dashboard')
@section('title', 'Detail Riwayat')
@section('content')

@php
    $tingkat = $penyakit && $penyakit->tingkat ? ucfirst(strtolower((string) $penyakit->tingkat)) : 'Sedang';
    $penyebabLines = $penyakit && $penyakit->deskripsi ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->deskripsi))) : [];
    $solusiLines = $penyakit && $penyakit->solusi ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->solusi))) : [];
    $pencegahanLines = $penyakit && $penyakit->pencegahan ? array_filter(preg_split('/\r\n|\r|\n/', trim($penyakit->pencegahan))) : [];
@endphp

<div class="mx-auto max-w-5xl space-y-6 riwayat-detail-page">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <a href="/user/riwayat" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors mb-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-[#152238] sm:text-3xl">Detail Riwayat Diagnosa</h1>
            <p class="mt-1 text-[15px] text-slate-600">Diagnosa #{{ $d->id }} — {{ format_date_id($d->tanggal_diagnosa) }}</p>
        </div>
        <div class="shrink-0 flex items-center gap-2">
            @include('partials.diagnosa-export-dropdown', ['diagnosaId' => $d->id, 'exportContext' => 'detail'])
            <form method="post" action="{{ route('user.riwayat.hapus') }}" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus Riwayat?', 'Apakah Anda yakin ingin menghapus riwayat diagnosa ini? Tindakan ini tidak dapat dibatalkan.');" class="inline">
                @csrf
                <input type="hidden" name="id" value="{{ $d->id }}">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 shadow-sm transition-colors hover:bg-red-100/70 whitespace-nowrap">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <p class="hidden print:block text-sm text-slate-500 mb-4">
        Detail Riwayat Diagnosa #{{ $d->id }} — {{ format_date_id($d->tanggal_diagnosa) }}
    </p>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-8 print:border-0 print:shadow-none print:p-0">
        <div class="rounded-2xl bg-red-50/50 p-5 sm:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                @include('partials.printer-diagnosa-icon')
                <div class="text-center sm:text-left space-y-2">
                    <p class="text-[13px] font-bold uppercase tracking-wider text-slate-500">Diagnosa Kerusakan</p>
                    @if ($penyakit)
                        <h2 class="text-2xl font-extrabold text-red-650 leading-tight tracking-tight sm:text-3xl">
                            {{ $penyakit->nama_penyakit }}
                        </h2>
                        <div class="mt-2">
                            <span class="inline-flex items-center rounded-full bg-red-100/70 px-3.5 py-1 text-xs font-bold text-red-700">
                                Tingkat Kerusakan : {{ $tingkat }}
                            </span>
                        </div>
                    @else
                        <h2 class="text-2xl font-bold text-slate-700">Tidak ada kerusakan terdeteksi</h2>
                    @endif
                </div>
            </div>
        </div>

        @if ($penyakit)
            <div class="space-y-3">
                <h3 class="text-base font-bold text-slate-900">Penyebab</h3>
                @if (count($penyebabLines))
                    <ul class="list-decimal pl-5 text-[15px] space-y-2.5 text-slate-700 leading-relaxed">
                        @foreach ($penyebabLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-[15px] leading-relaxed text-slate-700">{{ $penyakit->deskripsi }}</p>
                @endif
            </div>

            <div class="space-y-3">
                <h3 class="text-base font-bold text-slate-900">Solusi</h3>
                @if (count($solusiLines))
                    <ol class="list-decimal pl-5 text-[15px] space-y-2.5 text-slate-700 leading-relaxed">
                        @foreach ($solusiLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="text-[15px] leading-relaxed text-slate-700">{{ $penyakit->solusi }}</p>
                @endif
            </div>

            @if (count($pencegahanLines) || ($penyakit->pencegahan ?? null))
                <div class="space-y-3">
                    <h3 class="text-base font-bold text-slate-900">Pencegahan</h3>
                    @if (count($pencegahanLines))
                        <ol class="list-decimal pl-5 text-[15px] space-y-2.5 text-slate-700 leading-relaxed">
                            @foreach ($pencegahanLines as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-[15px] leading-relaxed text-slate-700">{{ $penyakit->pencegahan }}</p>
                    @endif
                </div>
            @endif
        @endif

        <div class="rounded-xl bg-slate-50/90 p-5 sm:p-6">
            <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">Gejala yang terdeteksi</h4>
            <div class="flex flex-wrap gap-2">
                @foreach ($kodes as $k)
                    <span class="inline-flex items-center rounded-lg bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ $namaGejala[$k] ?? $k }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('head')
<style>
    @media print {
        .riwayat-detail-page,
        .riwayat-detail-page * {
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .riwayat-detail-page table,
        .riwayat-detail-page td,
        .riwayat-detail-page th {
            border: none !important;
        }
    }
</style>
@endpush

@endsection
