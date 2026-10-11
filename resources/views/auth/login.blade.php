<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Login - Sistem Inventaris KSR PMI Unit INSTIKI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="h-full font-sans antialiased text-slate-100 flex items-center justify-center p-4 bg-radial from-[#7f1d2d] via-[#4c0519] to-[#0f172a] selection:bg-rose-500 selection:text-white relative overflow-hidden"
      x-data="{ 
          nim: '{{ old('nim', '') }}', 
          password: '',
          showPass: false
      }">

    <!-- Background Decorative Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Card Container -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl border border-white/20 p-8 text-slate-800">
            
            <!-- Top Branding & Logos -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <!-- Logo INSTIKI (Kiri) -->
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-lg border border-slate-100 flex items-center justify-center p-2 transform hover:scale-105 transition-transform">
                        <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo INSTIKI" class="w-full h-full object-contain">
                    </div>

                    <!-- Divider -->
                    <div class="w-px h-8 bg-slate-200"></div>

                    <!-- Logo KSR PMI (Kanan) -->
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-lg border border-rose-100 flex items-center justify-center p-2 transform hover:scale-105 transition-transform">
                        <img src="{{ asset('images/logo-ksr.png') }}" alt="Logo KSR PMI" class="w-full h-full object-contain">
                    </div>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100/80 text-[#7f1d2d] text-xs font-semibold uppercase tracking-wider mb-2">
                    <span>KSR PMI Unit INSTIKI</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Sistem Inventaris Medis</h1>
                <p class="text-xs text-slate-500 mt-1">Silakan masuk menggunakan Username atau NIM akun Anda</p>
            </div>

            <!-- Error Notification -->
            @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="space-y-0.5">
                    @foreach($errors->all() as $error)
                    <p class="font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium">{{ session('info') }}</p>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <!-- NIM Input (Username) -->
                <div>
                    <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username / NIM
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" 
                               id="nim" 
                               name="nim" 
                               x-model="nim"
                               required 
                               autofocus
                               placeholder="Contoh: superadminksr atau 2401020002"
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border {{ $errors->has('nim') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300' }} rounded-2xl text-slate-900 text-sm font-mono focus:ring-2 focus:ring-rose-700 focus:border-rose-700 outline-none transition-all placeholder:font-sans placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi (Password)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" 
                               id="password" 
                               name="password" 
                               x-model="password"
                               required 
                               placeholder="Masukkan kata sandi..."
                               class="w-full pl-11 pr-11 py-3 bg-slate-50 border {{ $errors->has('password') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300' }} rounded-2xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-700 focus:border-rose-700 outline-none transition-all placeholder:text-slate-400">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700">
                            <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#7f1d2d] focus:ring-rose-700 border-slate-300">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#7f1d2d] to-[#991b1b] hover:from-[#991b1b] hover:to-[#be123c] text-white font-bold text-sm shadow-lg shadow-rose-950/20 hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                    <span>Masuk ke Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

        </div>

        <p class="text-center text-xs text-rose-200/70 mt-6">
            &copy; {{ date('Y') }} Korps Sukarela Palang Merah Indonesia - Unit INSTIKI
        </p>

    </div>

    <!-- Popup Alerts Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('login_error'))
            Swal.fire({
                icon: 'error',
                title: {!! json_encode(session('login_error.title')) !!},
                text: {!! json_encode(session('login_error.message')) !!},
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#7f1d2d',
                background: '#ffffff',
                backdrop: 'rgba(15, 23, 42, 0.75)',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-rose-100 p-6',
                    title: 'text-xl font-bold text-slate-800',
                    htmlContainer: 'text-sm text-slate-600',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold text-sm shadow-md transition-all'
                }
            });
        @elseif($errors->any())
            Swal.fire({
                icon: 'warning',
                title: 'Data Masuk Belum Sesuai',
                html: '<ul class="text-left text-sm space-y-1.5 list-disc list-inside mt-2 text-slate-700">' + 
                      {!! json_encode($errors->all()) !!}.map(err => '<li>' + err + '</li>').join('') + 
                      '</ul>',
                confirmButtonText: 'Periksa Kembali',
                confirmButtonColor: '#7f1d2d',
                background: '#ffffff',
                backdrop: 'rgba(15, 23, 42, 0.75)',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-amber-100 p-6',
                    title: 'text-xl font-bold text-slate-800',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold text-sm shadow-md transition-all'
                }
            });
        @endif

        @if(session('info'))
            const InfoToast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                iconColor: '#2563eb'
            });
            InfoToast.fire({
                icon: 'info',
                title: {!! json_encode(session('info')) !!}
            });
        @endif

        @if(session('success'))
            const SuccessToast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                iconColor: '#16a34a'
            });
            SuccessToast.fire({
                icon: 'success',
                title: {!! json_encode(session('success')) !!}
            });
        @endif
    });
    </script>

</body>
</html>
