@extends('layouts.dashboard')
@section('title', 'Profil Saya')
@section('content')

@php
    $fotoUrl = user_foto_profil_url($user);
@endphp

<div class="mx-auto max-w-6xl space-y-6">

    @include('partials.user-page-header', ['title' => 'Profil Saya', 'firstName' => $firstName])

    @if (request('success') === 'password')
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Password berhasil diubah. Silakan <strong>logout</strong> lalu login kembali dengan password baru.
        </div>
    @elseif (request('success') === 'photo')
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Foto profil berhasil diperbarui.
        </div>
    @elseif (request('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            Profil berhasil diperbarui.
        </div>
    @endif
    @if ($error)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $error }}</div>
    @endif

    @if (! $editMode)
        <div class="grid gap-6 lg:grid-cols-[minmax(240px,320px)_1fr] lg:items-start">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col items-center text-center">
                    <div id="profilePhotoPreview" class="relative h-36 w-36 overflow-hidden rounded-full bg-blue-100 ring-4 ring-blue-50">
                        @if ($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="Foto profil" class="h-full w-full object-cover" id="profilePhotoImg">
                        @else
                            <span class="flex h-full w-full items-center justify-center text-blue-500" id="profilePhotoPlaceholder">
                                <i class="bi bi-person-fill text-7xl"></i>
                            </span>
                        @endif
                    </div>
                    <button type="button" onclick="openPhotoModal()"
                            class="mt-6 w-full max-w-[200px] rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                        Ubah Foto
                    </button>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <dl class="divide-y divide-slate-200">
                    @foreach ([
                        ['Nama', $user->nama_lengkap ?: '—'],
                        ['Username', $username],
                        ['Email', $user->email],
                        ['No. WA', format_no_wa_display($user->no_wa ?? null)],
                        ['Tanggal Daftar', format_date_id_day($user->created_at ?? null)],
                    ] as [$label, $value])
                        <div class="flex flex-wrap gap-x-2 px-6 py-4 text-sm sm:px-8 sm:py-4">
                            <dt class="min-w-[7.5rem] font-semibold text-slate-800">{{ $label }}</dt>
                            <dd class="text-slate-400">:</dd>
                            <dd class="min-w-0 flex-1 font-medium text-slate-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="/profile?edit=1"
               class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                Edit Profil
            </a>
        </div>

        {{-- Modal pilih sumber foto --}}
        <div id="photoModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]" onclick="closePhotoModal()"></div>
            <div class="relative w-full max-w-sm rounded-2xl border border-slate-100 bg-white p-6 shadow-xl sm:p-8">
                <h3 class="text-center text-lg font-bold text-slate-900">Ubah Foto Profil</h3>
                <p class="mt-2 text-center text-sm text-slate-500">Pilih sumber foto yang ingin digunakan</p>

                <div class="mt-6 space-y-3">
                    <button type="button" onclick="pickPhoto('camera')"
                            class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                        <i class="bi bi-camera-fill text-xl text-blue-600"></i>
                        Ambil dari Kamera
                    </button>
                    <button type="button" onclick="pickPhoto('gallery')"
                            class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-800 shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                        <i class="bi bi-images text-xl text-blue-600"></i>
                        Pilih dari Galeri / File
                    </button>
                </div>

                <button type="button" onclick="closePhotoModal()"
                        class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
            </div>
        </div>

        {{-- Modal kamera langsung (HP / laptop / tablet) --}}
        <div id="cameraModal" class="fixed inset-0 z-[9999] hidden items-end justify-center sm:items-center sm:p-4 md:p-6">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px]" onclick="closeCameraModal()"></div>
            <div class="relative flex max-h-[100dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl border border-slate-100 bg-white shadow-xl sm:max-h-[min(92dvh,720px)] sm:rounded-2xl">
                <div class="shrink-0 px-4 pb-2 pt-4 sm:px-6 sm:pt-5">
                    <h3 class="text-center text-base font-bold text-slate-900 sm:text-lg">Ambil dari Kamera</h3>
                    <p id="cameraStatus" class="mt-1 text-center text-xs text-slate-500 sm:mt-2 sm:text-sm">Menyiapkan kamera…</p>
                </div>

                <div class="flex min-h-0 flex-1 flex-col px-4 sm:px-6">
                    <div class="relative mx-auto w-full max-w-[min(100%,52dvh)] overflow-hidden rounded-xl bg-slate-900 aspect-square sm:max-w-[min(100%,58dvh)] sm:rounded-2xl md:max-w-[min(100%,420px)]">
                        <video id="cameraVideo" class="absolute inset-0 h-full w-full object-cover scale-x-[-1]" playsinline autoplay muted></video>
                        <canvas id="cameraCanvas" class="hidden"></canvas>
                    </div>
                    <p id="cameraError" class="mt-2 hidden text-center text-xs font-medium text-red-600 sm:mt-3 sm:text-sm"></p>
                </div>

                <div class="shrink-0 space-y-2 border-t border-slate-100 bg-white px-4 pb-[max(1rem,env(safe-area-inset-bottom))] pt-3 sm:space-y-3 sm:px-6 sm:pb-5 sm:pt-4">
                    <button type="button" id="cameraCaptureBtn" onclick="captureCameraPhoto()" disabled
                            class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:bg-slate-300 sm:py-3.5">
                        <i class="bi bi-camera-fill mr-1.5"></i> Ambil Foto
                    </button>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3">
                        <button type="button" id="cameraSwitchBtn" onclick="switchCameraFacing()" disabled
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 sm:py-3">
                            <i class="bi bi-arrow-repeat mr-1.5"></i> Ganti Kamera
                        </button>
                        <button type="button" onclick="closeCameraModal()"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 sm:py-3">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal preview sebelum upload --}}
        <div id="photoPreviewModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px]" onclick="cancelPhotoPreview()"></div>
            <div class="relative w-full max-w-sm rounded-2xl border border-slate-100 bg-white p-6 shadow-xl sm:p-8">
                <h3 class="text-center text-lg font-bold text-slate-900">Preview Foto</h3>
                <p class="mt-2 text-center text-sm text-slate-500">Pastikan foto sudah sesuai sebelum disimpan</p>

                <div class="mx-auto mt-6 flex justify-center">
                    <div class="h-40 w-40 overflow-hidden rounded-full bg-blue-100 ring-4 ring-blue-50">
                        <img id="photoPreviewImg" src="" alt="Preview foto" class="hidden h-full w-full object-cover">
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <button type="button" onclick="submitPhotoPreview()"
                            class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="cancelPhotoPreview()"
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-100">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <form id="photoUploadForm" method="post" action="{{ route('profile.foto') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" name="foto" id="photoFileInput" accept="image/jpeg,image/png,image/webp,image/jpg" class="hidden">
        </form>

        <div id="photoUploading" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-900/30 p-4">
            <div class="rounded-xl bg-white px-6 py-4 text-sm font-semibold text-slate-700 shadow-lg">
                Mengunggah foto…
            </div>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between gap-4">
            <a href="/profile" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700">
                <i class="bi bi-arrow-left"></i> Kembali ke Profil
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-bold text-slate-900">Edit Profil</h2>
            <p class="mt-1 text-sm text-slate-500">Perbarui informasi akun Anda.</p>

            <form method="post" action="/profile"
                  onsubmit="event.preventDefault(); confirmUpdate(this, 'Simpan Perubahan?', 'Apakah Anda yakin ingin menyimpan perubahan profil?');"
                  class="mt-6 max-w-xl space-y-5">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                           value="{{ old('nama_lengkap', $user->nama_lengkap) }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Email</label>
                    <input type="email" name="email" required
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                           value="{{ old('email', $user->email) }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">No. WhatsApp</label>
                    <input type="tel" name="no_wa" inputmode="numeric" autocomplete="tel"
                           placeholder="081234567890 atau 6281234567890"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                           value="{{ old('no_wa', $user->no_wa ?? '') }}">
                    <p class="mt-1.5 text-xs text-slate-500">Opsional. Harus diawali <strong>0</strong>, <strong>62</strong>, atau <strong>8</strong> (contoh: 0812…, 628…, 812…).</p>
                </div>

                <hr class="border-slate-200">

                <p class="text-sm font-semibold text-slate-700">Ubah Password</p>
                <p class="text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Password Saat Ini</label>
                    @include('partials.password-input', [
                        'name' => 'current_password',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'current-password',
                    ])
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Password Baru</label>
                    @include('partials.password-input', [
                        'name' => 'password',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password',
                    ])
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Konfirmasi Password Baru</label>
                    @include('partials.password-input', [
                        'name' => 'password_confirmation',
                        'placeholder' => '••••••••',
                        'autocomplete' => 'new-password',
                    ])
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                    <a href="/profile"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    @endif
</div>

@if (! $editMode)
@push('scripts')
<script>
(function () {
    var modal = document.getElementById('photoModal');
    var cameraModal = document.getElementById('cameraModal');
    var previewModal = document.getElementById('photoPreviewModal');
    var previewImg = document.getElementById('photoPreviewImg');
    var input = document.getElementById('photoFileInput');
    var form = document.getElementById('photoUploadForm');
    var uploading = document.getElementById('photoUploading');
    var video = document.getElementById('cameraVideo');
    var canvas = document.getElementById('cameraCanvas');
    var cameraStatus = document.getElementById('cameraStatus');
    var cameraError = document.getElementById('cameraError');
    var captureBtn = document.getElementById('cameraCaptureBtn');
    var switchBtn = document.getElementById('cameraSwitchBtn');
    var previewObjectUrl = null;
    var mediaStream = null;
    var facingMode = 'user';

    function showModal(el) {
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function hideModal(el) {
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    function revokePreviewUrl() {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function setCameraError(msg) {
        if (!cameraError) return;
        if (msg) {
            cameraError.textContent = msg;
            cameraError.classList.remove('hidden');
        } else {
            cameraError.textContent = '';
            cameraError.classList.add('hidden');
        }
    }

    function stopCameraStream() {
        if (mediaStream) {
            mediaStream.getTracks().forEach(function (track) {
                track.stop();
            });
            mediaStream = null;
        }
        if (video) {
            video.srcObject = null;
        }
        if (captureBtn) captureBtn.disabled = true;
        if (switchBtn) switchBtn.disabled = true;
    }

    function assignFileToInput(file) {
        if (!input || !file) return false;
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            return input.files && input.files.length > 0;
        } catch (e) {
            return false;
        }
    }

    function showFilePreview(file) {
        if (!file) return;
        revokePreviewUrl();
        previewObjectUrl = URL.createObjectURL(file);
        if (previewImg) {
            previewImg.src = previewObjectUrl;
            previewImg.classList.remove('hidden');
        }
        showModal(previewModal);
    }

    async function startCameraStream() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera. Gunakan Chrome/Edge/Safari terbaru, atau pilih Galeri.');
        }

        stopCameraStream();
        setCameraError('');
        if (cameraStatus) cameraStatus.textContent = 'Meminta izin kamera…';
        if (captureBtn) captureBtn.disabled = true;
        if (switchBtn) switchBtn.disabled = true;

        var constraints = {
            audio: false,
            video: {
                facingMode: { ideal: facingMode },
                width: { ideal: 1280 },
                height: { ideal: 1280 },
            },
        };

        try {
            mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (err) {
            // Fallback: any camera without facingMode (some laptops)
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: false, video: true });
        }

        if (video) {
            video.srcObject = mediaStream;
            video.setAttribute('playsinline', 'true');
            if (facingMode === 'user') {
                video.classList.add('scale-x-[-1]');
            } else {
                video.classList.remove('scale-x-[-1]');
            }
            try {
                await video.play();
            } catch (e) {
                // autoplay may already be running
            }
        }

        if (cameraStatus) cameraStatus.textContent = 'Arahkan kamera, lalu tekan Ambil Foto';
        if (captureBtn) captureBtn.disabled = false;
        if (switchBtn) switchBtn.disabled = false;
    }

    window.openPhotoModal = function () {
        showModal(modal);
    };

    window.closePhotoModal = function () {
        hideModal(modal);
    };

    window.closeCameraModal = function () {
        stopCameraStream();
        setCameraError('');
        if (cameraStatus) cameraStatus.textContent = 'Menyiapkan kamera…';
        hideModal(cameraModal);
    };

    window.openCameraCapture = async function () {
        closePhotoModal();
        showModal(cameraModal);
        setCameraError('');
        try {
            await startCameraStream();
        } catch (err) {
            var msg = 'Tidak bisa membuka kamera.';
            if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
                msg = 'Izin kamera ditolak. Izinkan kamera di browser, lalu coba lagi.';
            } else if (err && (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError')) {
                msg = 'Kamera tidak ditemukan di perangkat ini.';
            } else if (err && err.message) {
                msg = err.message;
            }
            if (cameraStatus) cameraStatus.textContent = 'Kamera tidak tersedia';
            setCameraError(msg);
            if (captureBtn) captureBtn.disabled = true;
            if (switchBtn) switchBtn.disabled = true;
        }
    };

    window.switchCameraFacing = async function () {
        facingMode = facingMode === 'user' ? 'environment' : 'user';
        try {
            await startCameraStream();
        } catch (err) {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            setCameraError('Gagal mengganti kamera. Coba lagi.');
        }
    };

    window.captureCameraPhoto = function () {
        if (!video || !canvas || !mediaStream) return;
        var w = video.videoWidth || 640;
        var h = video.videoHeight || 640;
        if (!w || !h) {
            setCameraError('Kamera belum siap. Tunggu sebentar lalu coba lagi.');
            return;
        }

        canvas.width = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        // Mirror selfie when using front camera
        if (facingMode === 'user') {
            ctx.translate(w, 0);
            ctx.scale(-1, 1);
        }
        ctx.drawImage(video, 0, 0, w, h);

        canvas.toBlob(function (blob) {
            if (!blob) {
                setCameraError('Gagal mengambil foto. Coba lagi.');
                return;
            }
            var file = new File([blob], 'profil-kamera.jpg', { type: 'image/jpeg' });
            if (!assignFileToInput(file)) {
                setCameraError('Browser tidak mendukung menyimpan foto dari kamera. Coba Galeri / File.');
                return;
            }
            stopCameraStream();
            hideModal(cameraModal);
            showFilePreview(file);
        }, 'image/jpeg', 0.92);
    };

    window.pickPhoto = function (source) {
        if (source === 'camera') {
            window.openCameraCapture();
            return;
        }
        if (!input) return;
        closePhotoModal();
        input.removeAttribute('capture');
        input.value = '';
        input.click();
    };

    window.cancelPhotoPreview = function () {
        revokePreviewUrl();
        if (previewImg) {
            previewImg.src = '';
            previewImg.classList.add('hidden');
        }
        if (input) {
            input.value = '';
        }
        hideModal(previewModal);
    };

    window.submitPhotoPreview = function () {
        if (!input || !input.files || !input.files.length || !form) return;
        hideModal(previewModal);
        if (uploading) {
            showModal(uploading);
        }
        form.submit();
    };

    if (input) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) return;
            showFilePreview(input.files[0]);
        });
    }

    // Stop camera if page is hidden / unloaded
    window.addEventListener('pagehide', stopCameraStream);
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            // keep stream only while modal open; if modal closed, already stopped
        }
    });
})();
</script>
@endpush
@endif

@endsection
