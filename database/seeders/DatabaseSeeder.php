<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        $superAdmin = User::where('role', 'super_admin')
            ->orWhere('nim', '2401020002')
            ->orWhere('nim', 'superadminksr')
            ->first();

        if ($superAdmin) {
            $superAdmin->update([
                'nim' => 'superadminksr',
                'name' => 'Super Admin KSR INSTIKI',
                'email' => 'admin.ksr@instiki.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'warehouse' => 'all',
                'is_active' => true,
            ]);
        } else {
            User::create([
                'nim' => 'superadminksr',
                'name' => 'Super Admin KSR INSTIKI',
                'email' => 'admin.ksr@instiki.ac.id',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'warehouse' => 'all',
                'is_active' => true,
            ]);
        }

        // 2. Organization & Letterhead Base Settings
        Setting::set('org_name', 'KORPS SUKARELA PALANG MERAH INDONESIA');
        Setting::set('unit_name', 'UNIT INSTITUT BISNIS DAN TEKNOLOGI INDONESIA (INSTIKI)');
        Setting::set('org_address', 'Jl. Tukad Pakerisan No.97, Panjer, Denpasar Selatan, Kota Denpasar, Bali 80225');
        Setting::set('org_contact', 'Telp: (0361) 256995 | Email: ksrpmi@instiki.ac.id | Web: instiki.ac.id');
        Setting::set('leader_name', 'I Komang Wisnu Anantha');
        Setting::set('leader_nim', '2401020002');
        Setting::set('officer_name', 'Ni Putu Ayu Saraswati');
        Setting::set('officer_nim', '2401020015');
    }
}
