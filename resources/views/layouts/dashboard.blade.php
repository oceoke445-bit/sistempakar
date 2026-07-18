<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistem Pakar'))</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        navy: { DEFAULT: '#152238', 950: '#0a1628' },
                        sidebar: '#152238',
                        brand: { 600: '#2563eb', 700: '#1d4ed8' },
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
    @stack('head')
</head>
<body class="overflow-hidden bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="flex h-[100dvh] w-full flex-col overflow-hidden lg:h-screen">
        <div id="nav-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-[1px] transition-opacity lg:hidden" aria-hidden="true"></div>

        @include('partials.nav', ['role' => session('auth.role')])

        <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:ml-72">
            <header class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-slate-200/80 bg-white/95 px-4 shadow-sm backdrop-blur-md lg:hidden print:hidden">
                <button type="button" id="nav-open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold uppercase tracking-[0.18em] text-blue-600/90">Sistem Pakar</p>
                    <p class="truncate text-sm font-bold text-slate-900">@yield('title')</p>
                </div>
            </header>

            <main class="relative main-scroll min-h-0 min-w-0 flex-1 overflow-y-auto bg-slate-100/80 px-4 py-4 sm:px-5 sm:py-6 md:px-8 md:py-8 lg:px-10 lg:py-10 print:ml-0 print:w-full print:overflow-visible print:bg-white print:p-6">
                @if (
                    !Request::is('admin/penyakit') && 
                    !Request::is('admin/gejala') && 
                    !Request::is('admin/printer') &&
                    !Request::is('admin/relasi') &&
                    !Request::is('admin/pengguna') &&
                    !Request::is('user/dashboard') &&
                    !Request::is('user/diagnosa') &&
                    !Request::is('user/diagnosa/*') &&
                    !Request::is('user/hasil-diagnosa*') &&
                    !Request::is('user/riwayat*') &&
                    !Request::is('profile*')
                )
                <div class="mb-5 flex justify-end sm:absolute sm:right-5 sm:top-6 md:right-8 md:top-8 lg:right-10 lg:top-10 sm:mb-0 print:hidden z-10">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200/90 bg-white px-4 py-2 text-xs font-medium text-slate-600 shadow-sm sm:text-sm">
                        <i class="bi bi-calendar3 text-slate-400"></i>
                        <span id="toolbar-datetime-text"></span>
                    </span>
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var sidebar = document.getElementById('app-sidebar');
            var overlay = document.getElementById('nav-overlay');
            var btn = document.getElementById('nav-open');
            if (!sidebar || !overlay || !btn) return;

            function isDesktop() {
                return window.matchMedia('(min-width: 1024px)').matches;
            }

            function setOpen(open) {
                if (isDesktop()) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    return;
                }
                if (open) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    btn.setAttribute('aria-expanded', 'true');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    btn.setAttribute('aria-expanded', 'false');
                }
            }

            btn.addEventListener('click', function () {
                var open = sidebar.classList.contains('-translate-x-full');
                setOpen(open);
            });
            overlay.addEventListener('click', function () {
                setOpen(false);
            });
            sidebar.querySelectorAll('a, button[type="submit"]').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (!isDesktop()) setOpen(false);
                });
            });
            window.addEventListener('resize', function () {
                if (isDesktop()) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !isDesktop()) setOpen(false);
            });
            if (isDesktop()) sidebar.classList.remove('-translate-x-full');
        })();
        (function () {
            var el = document.getElementById('toolbar-datetime-text');
            if (!el) return;

            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            function fmt() {
                var d = new Date();
                var parts = new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Asia/Jakarta',
                    day: 'numeric',
                    month: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                }).formatToParts(d);

                var map = {};
                parts.forEach(function (p) {
                    if (p.type !== 'literal') map[p.type] = p.value;
                });

                var dd = parseInt(map.day, 10);
                var mm = months[parseInt(map.month, 10) - 1];
                var yy = map.year;
                var hh = map.hour;
                var mi = map.minute;
                var ss = map.second;

                el.textContent = dd + ' ' + mm + ' ' + yy + ' | ' + hh + ':' + mi + ':' + ss + ' WIB';
            }

            fmt();
            setInterval(fmt, 1000);
        })();

        // Smooth global auto-dismiss alert script
        document.addEventListener('DOMContentLoaded', function() {
            var alerts = document.querySelectorAll(
                'div.rounded-xl.bg-emerald-50, div.rounded-xl.bg-red-50, div.rounded-xl.bg-sky-50, div.rounded-xl.bg-amber-50, ' +
                'div.rounded-xl.bg-emerald-100, div.rounded-xl.bg-red-100, div.rounded-xl.bg-sky-100, div.rounded-xl.bg-amber-100'
            );

            alerts.forEach(function(alert) {
                // Ignore modal alerts or elements inside hidden templates
                if (alert.closest('#logoutModal') || alert.closest('#deleteModal') || alert.closest('#saveModal') || alert.closest('#updateModal')) return;

                // Ensure parent is relative and set smooth transitions
                alert.classList.add('relative', 'pr-10', 'transition-all', 'duration-500', 'ease-out');
                alert.style.opacity = '1';
                alert.style.transform = 'translateY(0)';
                
                // Create close button
                var closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none';
                closeBtn.innerHTML = '<i class="bi bi-x-lg text-[13px]"></i>';
                
                closeBtn.addEventListener('click', function() {
                    dismissAlert(alert);
                });
                
                alert.appendChild(closeBtn);

                // Auto dismiss after 4 seconds
                setTimeout(function() {
                    dismissAlert(alert);
                }, 4000);
            });

            function dismissAlert(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(function() {
                    alert.style.height = '0';
                    alert.style.paddingTop = '0';
                    alert.style.paddingBottom = '0';
                    alert.style.marginTop = '0';
                    alert.style.marginBottom = '0';
                    alert.style.borderWidth = '0';
                    alert.style.overflow = 'hidden';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 500);
            }
        });
    </script>
    <!-- Premium Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity" onclick="closeLogoutModal()"></div>
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl transition-all sm:p-8">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 border border-amber-100">
                <i class="bi bi-box-arrow-right text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 leading-6">Keluar dari Akun?</h3>
            <p class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Apakah Anda yakin ingin keluar? Sesi Anda akan berakhir dan Anda harus masuk kembali untuk mengakses sistem.
            </p>
            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" onclick="closeLogoutModal()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm sm:w-auto">
                    Batal
                </button>
                <button type="button" id="confirmLogoutBtn" class="w-full rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition-colors shadow-sm sm:w-auto active:scale-95">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>

    <!-- Premium Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl transition-all sm:p-8">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-500 border border-red-100">
                <i class="bi bi-trash3-fill text-2xl"></i>
            </div>
            <h3 id="deleteModalTitle" class="text-lg font-bold text-slate-900 leading-6">Hapus Data?</h3>
            <p id="deleteModalDescription" class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" onclick="closeDeleteModal()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm sm:w-auto">
                    Batal
                </button>
                <button type="button" id="confirmDeleteBtn" class="w-full rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition-colors shadow-sm sm:w-auto active:scale-95">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Premium Save Confirmation Modal -->
    <div id="saveModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity" onclick="closeSaveModal()"></div>
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl transition-all sm:p-8">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 border border-emerald-100">
                <i class="bi bi-plus-circle text-2xl"></i>
            </div>
            <h3 id="saveModalTitle" class="text-lg font-bold text-slate-900 leading-6">Tambah Data?</h3>
            <p id="saveModalDescription" class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Apakah Anda yakin ingin menambahkan data baru ini?
            </p>
            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" onclick="closeSaveModal()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm sm:w-auto">
                    Batal
                </button>
                <button type="button" id="confirmSaveBtn" class="w-full rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition-colors shadow-sm sm:w-auto active:scale-95">
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- Premium Update Confirmation Modal -->
    <div id="updateModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity" onclick="closeUpdateModal()"></div>
        <div class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-xl transition-all sm:p-8">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500 border border-blue-100">
                <i class="bi bi-check2-circle text-3xl"></i>
            </div>
            <h3 id="updateModalTitle" class="text-lg font-bold text-slate-900 leading-6">Simpan Perubahan?</h3>
            <p id="updateModalDescription" class="mt-2.5 text-sm leading-relaxed text-slate-500">
                Apakah Anda yakin ingin memperbarui data ini?
            </p>
            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" onclick="closeUpdateModal()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors shadow-sm sm:w-auto">
                    Batal
                </button>
                <button type="button" id="confirmUpdateBtn" class="w-full rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700 transition-colors shadow-sm sm:w-auto active:scale-95">
                    Ya, Perbarui
                </button>
            </div>
        </div>
    </div>

    <script>
        function resolveForm(formRef) {
            if (!formRef) return null;
            if (typeof formRef === 'string') {
                return document.getElementById(formRef);
            }
            if (formRef instanceof HTMLFormElement) {
                return formRef;
            }
            if (formRef.closest) {
                return formRef.closest('form');
            }
            return null;
        }

        function submitFormAfterConfirm(form) {
            if (!form) return;
            form.removeAttribute('onsubmit');
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }

        // Modal helpers
        window.openLogoutModal = function() {
            var modal = document.getElementById('logoutModal');
            if (modal) modal.classList.remove('hidden');
        }
        window.closeLogoutModal = function() {
            var modal = document.getElementById('logoutModal');
            if (modal) modal.classList.add('hidden');
        }

        var confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
        if (confirmLogoutBtn) {
            confirmLogoutBtn.addEventListener('click', function() {
                var form = document.getElementById('logoutForm');
                if (form) form.submit();
            });
        }

        var deleteFormToSubmit = null;
        window.confirmDelete = function(formIdOrElement, customTitle, customDesc) {
            deleteFormToSubmit = resolveForm(formIdOrElement);
            var titleEl = document.getElementById('deleteModalTitle');
            var descEl = document.getElementById('deleteModalDescription');
            if (titleEl) titleEl.textContent = customTitle || 'Hapus Data?';
            if (descEl) descEl.textContent = customDesc || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
            
            var modal = document.getElementById('deleteModal');
            if (modal) modal.classList.remove('hidden');
        }
        window.closeDeleteModal = function() {
            var modal = document.getElementById('deleteModal');
            if (modal) modal.classList.add('hidden');
            deleteFormToSubmit = null;
        }

        var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                var form = deleteFormToSubmit;
                closeDeleteModal();
                submitFormAfterConfirm(form);
            });
        }

        var saveFormToSubmit = null;
        window.confirmSave = function(formIdOrElement, customTitle, customDesc) {
            saveFormToSubmit = resolveForm(formIdOrElement);
            var titleEl = document.getElementById('saveModalTitle');
            var descEl = document.getElementById('saveModalDescription');
            if (titleEl) titleEl.textContent = customTitle || 'Tambah Data?';
            if (descEl) descEl.textContent = customDesc || 'Apakah Anda yakin ingin menambahkan data baru ini?';
            
            var modal = document.getElementById('saveModal');
            if (modal) modal.classList.remove('hidden');
        }
        window.closeSaveModal = function() {
            var modal = document.getElementById('saveModal');
            if (modal) modal.classList.add('hidden');
            saveFormToSubmit = null;
        }

        var confirmSaveBtn = document.getElementById('confirmSaveBtn');
        if (confirmSaveBtn) {
            confirmSaveBtn.addEventListener('click', function() {
                var form = saveFormToSubmit;
                closeSaveModal();
                submitFormAfterConfirm(form);
            });
        }

        var updateFormToSubmit = null;
        window.confirmUpdate = function(formIdOrElement, customTitle, customDesc) {
            updateFormToSubmit = resolveForm(formIdOrElement);
            var titleEl = document.getElementById('updateModalTitle');
            var descEl = document.getElementById('updateModalDescription');
            if (titleEl) titleEl.textContent = customTitle || 'Simpan Perubahan?';
            if (descEl) descEl.textContent = customDesc || 'Apakah Anda yakin ingin memperbarui data ini?';

            var modal = document.getElementById('updateModal');
            if (modal) modal.classList.remove('hidden');
        }
        window.closeUpdateModal = function() {
            var modal = document.getElementById('updateModal');
            if (modal) modal.classList.add('hidden');
            updateFormToSubmit = null;
        }

        var confirmUpdateBtn = document.getElementById('confirmUpdateBtn');
        if (confirmUpdateBtn) {
            confirmUpdateBtn.addEventListener('click', function() {
                var form = updateFormToSubmit;
                closeUpdateModal();
                submitFormAfterConfirm(form);
            });
        }

        document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = btn.getAttribute('data-target');
                var input = targetId ? document.getElementById(targetId) : null;
                if (!input) return;
                var icon = btn.querySelector('i');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                if (icon) {
                    icon.classList.toggle('bi-eye', show);
                    icon.classList.toggle('bi-eye-slash', !show);
                }
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
