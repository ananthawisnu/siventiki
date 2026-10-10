<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>@yield('title', 'Sistem Inventaris') - KSR PMI Unit INSTIKI</title>

    <!-- Tailwind & App CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js for Interactive Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Alpine.js for lightweight reactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-800 selection:bg-rose-500 selection:text-white" x-data="{ sidebarOpen: false, userMenuOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden" 
         @click="sidebarOpen = false" 
         style="display: none;"></div>

    <div class="flex h-full min-h-screen w-full overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:static inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-[#7f1d2d] via-[#881337] to-[#4c0519] text-white flex flex-col transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none no-print">
            
            <!-- Brand / Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-rose-900/50 bg-black/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-white p-1.5 shadow-md flex items-center justify-center transition-transform group-hover:scale-105">
                        <!-- PMI Red Cross SVG -->
                        <svg viewBox="0 0 24 24" class="w-7 h-7 fill-red-600">
                            <path d="M9 4h6v5h5v6h-5v5H9v-5H4V9h5V4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-base tracking-wide text-white flex items-center gap-1.5">
                            <span>KSR PMI</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-rose-500/30 text-rose-200 border border-rose-400/30 font-mono">INSTIKI</span>
                        </div>
                        <p class="text-[11px] text-rose-200/70 font-medium">Sistem Inventaris Medis</p>
                    </div>
                </a>
                <button class="lg:hidden p-1.5 text-rose-200 hover:text-white rounded-lg hover:bg-rose-900/50" @click="sidebarOpen = false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Active User Warehouse Scope Badge -->
            <div class="px-6 py-3 bg-black/20 border-b border-rose-900/40">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-rose-300 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ auth()->user()->isSuperAdmin() ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse"></span>
                        Lingkup Gudang:
                    </span>
                    <span class="font-semibold text-white px-2 py-0.5 rounded-full bg-white/10 text-[11px]">
                        {{ auth()->user()->warehouse_label }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 text-sm">
                
                <div class="px-3 py-1 text-[11px] font-semibold tracking-wider text-rose-300/60 uppercase">Menu Utama</div>

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                <!-- Master Data (Obat & BHP) -->
                <a href="{{ route('items.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('items.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Katalog Obat & BHP</span>
                </a>

                <!-- Pergerakan Stok (Inbound, Outbound, Opname) -->
                <a href="{{ route('movements.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('movements.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                    <span>Pergerakan Stok (FEFO)</span>
                </a>

                <div class="pt-4 px-3 py-1 text-[11px] font-semibold tracking-wider text-rose-300/60 uppercase">Aset & Logistik</div>

                <!-- Manajemen Aset Tetap -->
                <a href="{{ route('assets.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('assets.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Aset Tetap & Riwayat</span>
                </a>

                <!-- Mutasi Antar Gudang -->
                <a href="{{ route('transfers.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('transfers.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Mutasi Antar Gudang</span>
                </a>

                <!-- Peminjaman Lintas Divisi -->
                <a href="{{ route('loans.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('loans.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Peminjaman Lintas Divisi</span>
                </a>

                <!-- Laporan (LPJ) -->
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan & LPJ</span>
                </a>

                @if(auth()->user()->isSuperAdmin())
                <div class="pt-4 px-3 py-1 text-[11px] font-semibold tracking-wider text-rose-300/60 uppercase">Super Admin</div>

                <!-- Manajemen Pengguna -->
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Manajemen Pengguna</span>
                </a>

                <!-- Pengaturan Kop & Logo -->
                <a href="{{ route('settings.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-white text-rose-950 shadow-md font-semibold' : 'text-rose-100/90 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Pengaturan Logo & Kop</span>
                </a>
                @endif
            </nav>

            <!-- User Footer -->
            <div class="p-4 border-t border-rose-900/50 bg-black/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-rose-700 text-white flex items-center justify-center font-bold text-sm border border-rose-500 shadow-inner">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="truncate max-w-[130px]">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-rose-300 font-mono">{{ auth()->user()->nim }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-rose-300 hover:text-white hover:bg-rose-800/60 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- TOP NAVBAR -->
            <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 lg:px-8 shrink-0 no-print">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100" @click="sidebarOpen = true">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    
                    <h1 class="text-lg font-bold text-slate-800 hidden sm:block">
                        @yield('header_title', 'Sistem Informasi Inventaris KSR PMI Unit INSTIKI')
                    </h1>
                </div>

                <!-- Right Toolbar -->
                <div class="flex items-center gap-3">
                    
                    <!-- Super Admin Warehouse Switcher -->
                    @if(auth()->user()->isSuperAdmin())
                    <div class="hidden sm:flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-500 font-medium pl-2 pr-1">Filter Gudang:</span>
                        <a href="{{ request()->fullUrlWithQuery(['warehouse' => 'all']) }}" 
                           class="px-2.5 py-1 rounded-lg font-medium transition-colors {{ request('warehouse', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-700 hover:text-slate-900' }}">Semua</a>
                        <a href="{{ request()->fullUrlWithQuery(['warehouse' => 'ksr']) }}" 
                           class="px-2.5 py-1 rounded-lg font-medium transition-colors {{ request('warehouse') === 'ksr' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-700 hover:text-slate-900' }}">Gudang Obat</a>
                        <a href="{{ request()->fullUrlWithQuery(['warehouse' => 'poliklinik']) }}" 
                           class="px-2.5 py-1 rounded-lg font-medium transition-colors {{ request('warehouse') === 'poliklinik' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-700 hover:text-slate-900' }}">Gudang Inventaris</a>
                    </div>
                    @endif

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 p-1.5 pl-2.5 pr-2 rounded-xl hover:bg-slate-100 border border-slate-200 transition-colors">
                            <div class="text-right hidden md:block">
                                <p class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-rose-800 font-semibold">{{ auth()->user()->role_label }}</p>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-[#7f1d2d] text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 divide-y divide-slate-100"
                             style="display: none;">
                            <div class="px-4 py-2.5">
                                <p class="text-xs text-slate-500">Login sebagai:</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs font-mono text-rose-800 font-medium">NIM: {{ auth()->user()->nim }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Pengaturan Profil & Sandi
                                </a>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-rose-700 hover:bg-rose-50 font-medium">
                                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Keluar dari Sistem (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- FLASH MESSAGES via SweetAlert2 Toast -->
            <div style="display:none;">
                @if(session('success'))
                <span id="flash-success">{{ session('success') }}</span>
                @endif
                @if(session('info'))
                <span id="flash-info">{{ session('info') }}</span>
                @endif
                @if($errors->any())
                <span id="flash-errors">{{ implode('||', $errors->all()) }}</span>
                @endif
            </div>

            <!-- PAGE CONTENT BODY -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                @yield('content')
            </main>

            <!-- FOOTER -->
            <footer class="h-10 bg-white border-t border-slate-200/80 px-6 flex items-center justify-between text-xs text-slate-500 no-print shrink-0">
                <span>&copy; {{ date('Y') }} Korps Sukarela Palang Merah Indonesia - Unit INSTIKI</span>
                <span class="font-mono text-[11px] text-slate-400">v1.0.0 • FEFO Logistics & Asset Mgmt</span>
            </footer>

        </div>
    </div>

    @stack('scripts')

    <script>
    // ── SweetAlert2 Global Config ──────────────────────────────────────────────
    const SwalToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    // ── Flash Messages ─────────────────────────────────────────────────────────
    const flashSuccess = document.getElementById('flash-success');
    const flashInfo    = document.getElementById('flash-info');
    const flashErrors  = document.getElementById('flash-errors');

    if (flashSuccess) {
        SwalToast.fire({
            icon: 'success',
            title: flashSuccess.textContent.trim(),
            background: '#f0fdf4',
            color: '#14532d',
            iconColor: '#16a34a',
        });
    }

    if (flashInfo) {
        SwalToast.fire({
            icon: 'info',
            title: flashInfo.textContent.trim(),
            background: '#eff6ff',
            color: '#1e3a5f',
            iconColor: '#2563eb',
        });
    }

    if (flashErrors) {
        const msgs = flashErrors.textContent.trim().split('||');
        const listHtml = '<ul style="text-align:left;margin-top:6px;padding-left:18px;font-size:13px">' +
            msgs.map(m => `<li>${m}</li>`).join('') + '</ul>';
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            html: listHtml,
            confirmButtonColor: '#7f1d2d',
            confirmButtonText: 'Mengerti',
            background: '#fff1f2',
            color: '#7f1d1d',
        });
    }

    // ── Konfirmasi Delete / Aksi Berbahaya ─────────────────────────────────────
    // Tambahkan atribut data-swal-confirm="Pesan konfirmasi" pada form untuk mengaktifkan.
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-swal-confirm]').forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const msg   = this.dataset.swalConfirm  || 'Apakah Anda yakin?';
                const title = this.dataset.swalTitle    || 'Konfirmasi Aksi';
                const icon  = this.dataset.swalIcon     || 'warning';
                const confirmText = this.dataset.swalOk || 'Ya, Lanjutkan';
                const result = await Swal.fire({
                    title,
                    text: msg,
                    icon,
                    showCancelButton: true,
                    confirmButtonColor: '#7f1d2d',
                    cancelButtonColor:  '#64748b',
                    confirmButtonText:  confirmText,
                    cancelButtonText:   'Batal',
                    reverseButtons: true,
                });
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
