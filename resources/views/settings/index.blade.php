@extends('layouts.app')

@section('title', 'Pengaturan Logo & Kop')
@section('header_title', 'Pengaturan Kop Instansi & Logo Resmi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    leftPreview: '{{ $settings['logo_left'] }}',
    rightPreview: '{{ $settings['logo_right'] }}',
    previewLeft(event) {
        const file = event.target.files[0];
        if (file) {
            this.leftPreview = URL.createObjectURL(file);
        }
    },
    previewRight(event) {
        const file = event.target.files[0];
        if (file) {
            this.rightPreview = URL.createObjectURL(file);
        }
    }
}">

    <!-- Top Header Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Pengaturan Kop Surat & Logo Instansi</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1">Konfigurasi kop surat 3-kolom dan tanda tangan resmi pada seluruh laporan PDF dan kartu stok</p>
    </div>

    <!-- Letterhead Live Preview Box -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pratinjau Langsung Kop Surat (Live Preview)</span>
            <span class="text-[11px] px-2 py-0.5 rounded bg-rose-50 text-[#7f1d2d] font-semibold">Format Landscape A4</span>
        </div>

        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200" style="border-bottom: 3px double #7f1d2d;">
            <table class="w-full">
                <tr>
                    <!-- Logo Left Preview -->
                    <td class="w-20 text-left align-middle pr-3">
                        <template x-if="leftPreview">
                            <img :src="leftPreview" alt="Logo Kiri" class="h-16 w-auto object-contain">
                        </template>
                        <template x-if="!leftPreview">
                            <div class="w-14 h-14 rounded-xl bg-white border border-rose-200 flex items-center justify-center p-1">
                                <svg viewBox="0 0 24 24" class="w-10 h-10 fill-[#dc2626]"><path d="M9 4h6v5h5v6h-5v5H9v-5H4V9h5V4z"/></svg>
                            </div>
                        </template>
                    </td>

                    <!-- Text Center Preview -->
                    <td class="text-center align-middle px-3">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide leading-tight">
                            {{ $settings['org_name'] }}
                        </h3>
                        <h4 class="text-xs font-extrabold text-[#7f1d2d] uppercase tracking-wide leading-tight mt-0.5">
                            {{ $settings['unit_name'] }}
                        </h4>
                        <p class="text-[11px] text-slate-600 mt-1">{{ $settings['org_address'] }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $settings['org_contact'] }}</p>
                    </td>

                    <!-- Logo Right Preview -->
                    <td class="w-20 text-right align-middle pl-3">
                        <template x-if="rightPreview">
                            <img :src="rightPreview" alt="Logo Kanan" class="h-16 w-auto object-contain ml-auto">
                        </template>
                        <template x-if="!rightPreview">
                            <div class="w-14 h-14 rounded-xl bg-[#7f1d2d] text-white flex flex-col items-center justify-center ml-auto">
                                <span class="font-black text-xs">INSTIKI</span>
                            </div>
                        </template>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Form Configuration -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Logo Upload Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 rounded-2xl bg-rose-50/50 border border-rose-100">
                <!-- Logo Kiri -->
                <div>
                    <label for="logo_left" class="block text-xs font-bold text-slate-800 uppercase mb-1">
                        Logo Kiri (Logo PMI)
                    </label>
                    <input type="file" id="logo_left" name="logo_left" accept="image/*" @change="previewLeft($event)" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#7f1d2d] file:text-white hover:file:bg-[#991b1b]">
                    <p class="text-[10px] text-slate-500 mt-1">Format PNG/JPG/SVG, maks 500 KB.</p>
                </div>

                <!-- Logo Kanan -->
                <div>
                    <label for="logo_right" class="block text-xs font-bold text-slate-800 uppercase mb-1">
                        Logo Kanan (Logo INSTIKI)
                    </label>
                    <input type="file" id="logo_right" name="logo_right" accept="image/*" @change="previewRight($event)" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#7f1d2d] file:text-white hover:file:bg-[#991b1b]">
                    <p class="text-[10px] text-slate-500 mt-1">Format PNG/JPG/SVG, maks 500 KB.</p>
                </div>
            </div>

            <!-- Organization Details -->
            <div class="space-y-4">
                <div>
                    <label for="set_org_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Organisasi Induk <span class="text-rose-600">*</span></label>
                    <input type="text" id="set_org_name" name="org_name" value="{{ old('org_name', $settings['org_name']) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div>
                    <label for="set_unit_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Unit / Kampus <span class="text-rose-600">*</span></label>
                    <input type="text" id="set_unit_name" name="unit_name" value="{{ old('unit_name', $settings['unit_name']) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div>
                    <label for="set_org_address" class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Lengkap <span class="text-rose-600">*</span></label>
                    <input type="text" id="set_org_address" name="org_address" value="{{ old('org_address', $settings['org_address']) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div>
                    <label for="set_org_contact" class="block text-xs font-bold text-slate-700 uppercase mb-1">Kontak / Telepon / Email</label>
                    <input type="text" id="set_org_contact" name="org_contact" value="{{ old('org_contact', $settings['org_contact']) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                </div>
            </div>

            <!-- Signature Metadata -->
            <div class="pt-6 border-t border-slate-200">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Tanda Tangan Pengesahan Laporan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="set_leader_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Ketua KSR PMI</label>
                        <input type="text" id="set_leader_name" name="leader_name" value="{{ old('leader_name', $settings['leader_name']) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                    <div>
                        <label for="set_leader_nim" class="block text-xs font-bold text-slate-700 mb-1">NIM Ketua</label>
                        <input type="text" id="set_leader_nim" name="leader_nim" value="{{ old('leader_nim', $settings['leader_nim']) }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-sm shadow-md transition-all">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
