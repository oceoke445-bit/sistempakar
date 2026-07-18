@extends('layouts.dashboard')
@section('title', 'Data Pengguna')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#152238] sm:text-3xl">Data Pengguna</h1>
            <p class="mt-1 text-[15px] text-slate-600">Akun admin dan pengguna aplikasi.</p>
        </div>
        <div>
            <button type="button" onclick="togglePenggunaForm()" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition-all active:scale-95">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna Baru
            </button>
        </div>
    </div>

    @if (request('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">Berhasil.</div>
    @endif
    @if (request('notice'))
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">{{ request('notice') }}</div>
    @endif
    @if (request('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900">{{ request('error') }}</div>
    @endif

    <details id="tambah-pengguna-form" class="hidden group rounded-2xl border border-slate-200/90 bg-white shadow-[0_4px_24px_rgba(15,23,42,0.06)]">
        <summary class="cursor-pointer list-none rounded-2xl px-5 py-4 font-bold text-[#152238] marker:content-none sm:px-6 sm:py-5 [&::-webkit-details-marker]:hidden">
            <span class="inline-flex items-center gap-2"><i class="bi bi-person-plus text-brand-600"></i> Form tambah pengguna</span>
            <span class="float-right text-sm font-normal text-slate-500 group-open:hidden">Buka form</span>
            <span class="float-right hidden text-sm font-normal text-slate-500 group-open:inline">Tutup</span>
        </summary>
        <div class="border-t border-slate-100 px-5 pb-6 pt-2 sm:px-6">
            <form method="post" action="{{ url('/admin/pengguna') }}" class="mt-2 grid gap-4 md:grid-cols-2" onsubmit="event.preventDefault(); confirmSave(this, 'Tambah Pengguna?', 'Apakah Anda yakin ingin menambahkan pengguna baru ini?');">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nama</label>
                    <input name="nama_lengkap" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Email</label>
                    <input type="email" name="email" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Password</label>
                    @include('partials.password-input', [
                        'name' => 'password',
                        'placeholder' => 'Password baru',
                        'autocomplete' => 'new-password',
                        'required' => true,
                        'inputClass' => 'bg-slate-50 py-2.5',
                    ])
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Role</label>
                    <select name="role" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20">
                        <option value="user">user</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700">Simpan</button>
                </div>
            </form>
        </div>
    </details>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $u->nama_lengkap }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold capitalize text-slate-700">{{ $u->role }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <form method="post" action="{{ url('/admin/pengguna/update') }}" class="mb-2 flex flex-wrap gap-2" onsubmit="event.preventDefault(); confirmUpdate(this, 'Perbarui Pengguna?', 'Apakah Anda yakin ingin menyimpan perubahan data pengguna ini?');">
                                @csrf
                                <input type="hidden" name="id" value="{{ $u->id }}">
                                <input name="nama_lengkap" value="{{ $u->nama_lengkap }}" class="w-32 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                <input name="email" value="{{ $u->email }}" class="w-40 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                <select name="role" class="rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                    <option value="user" @selected($u->role==='user')>user</option>
                                    <option value="admin" @selected($u->role==='admin')>admin</option>
                                </select>
                                @include('partials.password-input', [
                                    'name' => 'password',
                                    'id' => 'pwd-update-' . $u->id,
                                    'placeholder' => 'Pwd baru',
                                    'autocomplete' => 'new-password',
                                    'size' => 'compact',
                                    'class' => 'w-32',
                                    'inputClass' => 'bg-white',
                                ])
                                <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1 text-xs font-semibold text-white">Simpan</button>
                            </form>
                            <form method="post" action="{{ url('/admin/pengguna/hapus') }}" class="inline" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus Pengguna?', 'Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                <input type="hidden" name="id" value="{{ $u->id }}">
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @include('partials.pagination', ['paginator' => $users])
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePenggunaForm() {
        const details = document.getElementById('tambah-pengguna-form');
        if (!details) return;
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            details.open = true;
            details.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            details.open = !details.open;
            if (!details.open) {
                details.classList.add('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const details = document.getElementById('tambah-pengguna-form');
        if (details) {
            details.addEventListener('toggle', function () {
                if (!this.open) {
                    this.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
