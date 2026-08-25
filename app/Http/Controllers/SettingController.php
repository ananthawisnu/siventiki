<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'org_name' => Setting::get('org_name', 'KORPS SUKARELA PALANG MERAH INDONESIA'),
            'unit_name' => Setting::get('unit_name', 'UNIT INSTITUT BISNIS DAN TEKNOLOGI INDONESIA (INSTIKI)'),
            'org_address' => Setting::get('org_address', 'Jl. Tukad Pakerisan No.97, Panjer, Denpasar Selatan, Bali 80225'),
            'org_contact' => Setting::get('org_contact', 'Telp: (0361) 256995 | Email: ksrpmi@instiki.ac.id'),
            'logo_left' => Setting::get('logo_left'),
            'logo_right' => Setting::get('logo_right'),
            'leader_name' => Setting::get('leader_name', 'I Komang Wisnu Anantha'),
            'leader_nim' => Setting::get('leader_nim', '2401020002'),
            'officer_name' => Setting::get('officer_name', 'Ni Putu Ayu Saraswati'),
            'officer_nim' => Setting::get('officer_nim', '2401020015'),
        ];

        return view('settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'org_name' => ['required', 'string', 'max:255'],
            'unit_name' => ['required', 'string', 'max:255'],
            'org_address' => ['required', 'string', 'max:500'],
            'org_contact' => ['nullable', 'string', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'leader_nim' => ['nullable', 'string', 'max:50'],
            'officer_name' => ['nullable', 'string', 'max:255'],
            'officer_nim' => ['nullable', 'string', 'max:50'],
            'logo_left' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:512'],
            'logo_right' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:512'],
        ], [
            'logo_left.max' => 'Ukuran Logo Kiri maksimal 500 KB.',
            'logo_right.max' => 'Ukuran Logo Kanan maksimal 500 KB.',
        ]);

        Setting::set('org_name', $validated['org_name']);
        Setting::set('unit_name', $validated['unit_name']);
        Setting::set('org_address', $validated['org_address']);
        Setting::set('org_contact', $validated['org_contact'] ?? '');
        Setting::set('leader_name', $validated['leader_name'] ?? '');
        Setting::set('leader_nim', $validated['leader_nim'] ?? '');
        Setting::set('officer_name', $validated['officer_name'] ?? '');
        Setting::set('officer_nim', $validated['officer_nim'] ?? '');

        if ($request->hasFile('logo_left')) {
            $file = $request->file('logo_left');
            $base64 = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
            Setting::set('logo_left', $base64);
        }

        if ($request->hasFile('logo_right')) {
            $file = $request->file('logo_right');
            $base64 = 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
            Setting::set('logo_right', $base64);
        }

        return back()->with('success', 'Pengaturan instansi dan logo kop surat berhasil diperbarui.');
    }
}
