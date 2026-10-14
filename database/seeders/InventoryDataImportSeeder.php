<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Movement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryDataImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Imports inventory items and assets based on physical inventory document.
     */
    public function run(): void
    {
        $actor = User::where('role', 'super_admin')->first() ?? User::first();
        $actorId = $actor?->id;
        $actorName = $actor?->name ?? 'Super Admin KSR INSTIKI';

        DB::beginTransaction();

        try {
            // ──────────────────────────────────────────────────────────────────────────
            // 1. IMPORT BHP MEDIS & NON MEDIS (Katalog Obat & BHP + Batches + Movements)
            // ──────────────────────────────────────────────────────────────────────────
            $bhpItems = [
                // BHP Medis
                [
                    'code' => 'BHP-POL-KASAGUL-001',
                    'name' => 'Kasa Gulung',
                    'category' => 'bhp',
                    'warehouse' => 'poliklinik',
                    'unit' => 'gulung',
                    'min_stock' => 5,
                    'description' => 'Kasa gulung perban medis steril / P3K. Sesuai Dokumen Inventaris No. 3',
                    'qty' => 1,
                    'batch_no' => 'BATCH-KASA-2026',
                    'expiry_date' => '2028-12-31',
                ],
                [
                    'code' => 'BHP-POL-MITELA-001',
                    'name' => 'Mitela (Pembalut Segitiga)',
                    'category' => 'bhp',
                    'warehouse' => 'poliklinik',
                    'unit' => 'buah',
                    'min_stock' => 5,
                    'description' => 'Kain mitela segitiga untuk pertolongan pertama dan balut bidai. Sesuai Dokumen Inventaris No. 52',
                    'qty' => 19,
                    'batch_no' => 'BATCH-MITELA-2026',
                    'expiry_date' => '2029-12-31',
                ],
                [
                    'code' => 'BHP-POL-GLUKODR-001',
                    'name' => 'GlukoDR (Strip Cek Gula Darah)',
                    'category' => 'bhp',
                    'warehouse' => 'poliklinik',
                    'unit' => 'kotak',
                    'min_stock' => 2,
                    'description' => 'Strip tes pemeriksaan gula darah GlukoDR. Sesuai Dokumen Inventaris No. 65',
                    'qty' => 1,
                    'batch_no' => 'BATCH-GLUKO-2026',
                    'expiry_date' => '2027-06-30',
                ],
                [
                    'code' => 'BHP-POL-BDONAT-001',
                    'name' => 'Bantalan Donat (Balut Luka)',
                    'category' => 'bhp',
                    'warehouse' => 'poliklinik',
                    'unit' => 'buah',
                    'min_stock' => 5,
                    'description' => 'Bantalan donat untuk fiksasi dan perawatan luka benda menancap / fraktur. Sesuai Dokumen Inventaris No. 38',
                    'qty' => 10,
                    'batch_no' => 'BATCH-DONAT-2026',
                    'expiry_date' => '2029-12-31',
                ],
                [
                    'code' => 'BHP-POL-HANDSCO-001',
                    'name' => 'Handscoon (Sarung Tangan Medis)',
                    'category' => 'bhp',
                    'warehouse' => 'poliklinik',
                    'unit' => 'box',
                    'min_stock' => 2,
                    'description' => 'Sarung tangan pemeriksaan medis latex steril/non-steril poliklinik.',
                    'qty' => 2,
                    'batch_no' => 'BATCH-HAND-2026',
                    'expiry_date' => '2028-12-31',
                ],

                // BHP Non Medis (ATK / Logistik Habis Pakai)
                [
                    'code' => 'BHPNM-POL-SENDOKPL-001',
                    'name' => 'Sendok Plastik',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'buah',
                    'min_stock' => 10,
                    'description' => 'Sendok makan plastik untuk logistik/kegiatan. Sesuai Dokumen Inventaris No. 4',
                    'qty' => 13,
                    'batch_no' => 'BATCH-SENDOK-2026',
                    'expiry_date' => null,
                ],
                [
                    'code' => 'BHPNM-POL-MAPBIASA-001',
                    'name' => 'Map Biasa (Stopmap Folio)',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'buah',
                    'min_stock' => 5,
                    'description' => 'Map kertas folio untuk arsip administrasi. Sesuai Dokumen Inventaris No. 24',
                    'qty' => 3,
                    'batch_no' => 'BATCH-MAPB-2026',
                    'expiry_date' => null,
                ],
                [
                    'code' => 'BHPNM-POL-MAPBATIK-001',
                    'name' => 'Map Batik',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'buah',
                    'min_stock' => 5,
                    'description' => 'Map motif batik untuk persuratan resmi dan acara. Sesuai Dokumen Inventaris No. 25',
                    'qty' => 3,
                    'batch_no' => 'BATCH-MAPTK-2026',
                    'expiry_date' => null,
                ],
                [
                    'code' => 'BHPNM-POL-KERTASMN-001',
                    'name' => 'Kertas Manila',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'gulung',
                    'min_stock' => 2,
                    'description' => 'Kertas manila untuk materi pelatihan dan simulasi. Sesuai Dokumen Inventaris No. 48',
                    'qty' => 1,
                    'batch_no' => 'BATCH-MANILA-2026',
                    'expiry_date' => null,
                ],
                [
                    'code' => 'BHPNM-POL-PITAKNG-001',
                    'name' => 'Pita Kuning (Safety Line)',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'gulung',
                    'min_stock' => 1,
                    'description' => 'Pita barikade evakuasi / safety line kuning. Sesuai Dokumen Inventaris No. 58',
                    'qty' => 1,
                    'batch_no' => 'BATCH-PITAK-2026',
                    'expiry_date' => null,
                ],
                [
                    'code' => 'BHPNM-POL-KAWAT-001',
                    'name' => 'Kawat Pengikat Logistik',
                    'category' => 'bhp_non_medis',
                    'warehouse' => 'poliklinik',
                    'unit' => 'gulung',
                    'min_stock' => 1,
                    'description' => 'Kawat pengikat untuk perlengkapan dan logistik. Sesuai Dokumen Inventaris No. 67',
                    'qty' => 1,
                    'batch_no' => 'BATCH-KAWAT-2026',
                    'expiry_date' => null,
                ],
            ];

            foreach ($bhpItems as $itemData) {
                $item = Item::firstOrCreate(
                    ['code' => $itemData['code']],
                    [
                        'name' => $itemData['name'],
                        'category' => $itemData['category'],
                        'warehouse' => $itemData['warehouse'],
                        'unit' => $itemData['unit'],
                        'min_stock' => $itemData['min_stock'],
                        'description' => $itemData['description'],
                    ]
                );

                // Create initial batch if none exists
                $existingBatch = Batch::where('item_id', $item->id)->first();
                if (! $existingBatch) {
                    $batch = Batch::create([
                        'item_id' => $item->id,
                        'warehouse' => $item->warehouse,
                        'batch_no' => $itemData['batch_no'],
                        'expiry_date' => $itemData['expiry_date'] ? Carbon::parse($itemData['expiry_date']) : null,
                        'quantity' => $itemData['qty'],
                        'initial_quantity' => $itemData['qty'],
                        'received_at' => Carbon::now()->subDays(5),
                        'invoice_no' => 'INV-AWAL-'.now()->format('Ymd'),
                        'source' => 'Pencatatan Fisik Awal',
                    ]);

                    Movement::create([
                        'item_id' => $item->id,
                        'warehouse' => $item->warehouse,
                        'kind' => 'inbound',
                        'quantity' => $itemData['qty'],
                        'reference_no' => 'INV-AWAL-'.now()->format('Ymd'),
                        'source_destination' => 'Pencatatan Fisik Awal',
                        'actor_id' => $actorId,
                        'actor_name' => $actorName,
                        'notes' => 'Pencatatan saldo awal barang dari dokumen inventaris fisik.',
                        'allocations' => [
                            [
                                'batch_id' => $batch->id,
                                'batch_no' => $batch->batch_no,
                                'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                                'quantity' => $itemData['qty'],
                            ],
                        ],
                        'running_balance' => $itemData['qty'],
                    ]);
                }
            }

            // ──────────────────────────────────────────────────────────────────────────
            // 2. IMPORT ASET TETAP / INVENTARIS BARANG (Tersedia / Rusak)
            // ──────────────────────────────────────────────────────────────────────────
            $assetsList = [
                // Dokumen No. 2: Tandu (2 Buah, Kondisi Baik)
                [
                    'name' => 'Tandu Lipat Lapangan (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Tandu evakuasi korban lapangan (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 2',
                ],
                // Dokumen No. 6: Bendera Merah Putih
                [
                    'name' => 'Bendera Merah Putih',
                    'status' => 'tersedia',
                    'description' => 'Bendera Merah Putih resmi (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 6',
                ],
                // Dokumen No. 7: Teko listrik
                [
                    'name' => 'Teko Listrik (Electric Kettle)',
                    'status' => 'tersedia',
                    'description' => 'Teko pemanas air listrik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 7',
                ],
                // Dokumen No. 8: Sound
                [
                    'name' => 'Sound System Portable',
                    'status' => 'tersedia',
                    'description' => 'Sound audio pengeras suara portable (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 8',
                ],
                // Dokumen No. 9: Rompi (4 Buah)
                [
                    'name' => 'Rompi KSR PMI (4 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Rompi operasional KSR PMI (Jumlah: 4 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 9',
                ],
                // Dokumen No. 10: Ember Besar (2 Buah)
                [
                    'name' => 'Ember Besar (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Ember plastik kapasitas besar (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 10',
                ],
                // Dokumen No. 11: Mangkok (3 Buah)
                [
                    'name' => 'Mangkok (3 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Mangkok logistik konsumsi (Jumlah: 3 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 11',
                ],
                // Dokumen No. 12: Garpu (9 Buah)
                [
                    'name' => 'Garpu Makan (9 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Garpu makan stainless (Jumlah: 9 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 12',
                ],
                // Dokumen No. 13: Cobek
                [
                    'name' => 'Cobek dan Ulekan',
                    'status' => 'tersedia',
                    'description' => 'Cobek set perlengkapan dapur (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 13',
                ],
                // Dokumen No. 14: X Banner Norma (Rusak Ringan)
                [
                    'name' => 'X Banner Norma Organisasi',
                    'status' => 'rusak',
                    'description' => 'X Banner Norma KSR (1 Buah). Kondisi: Rusak Ringan. Sesuai Dokumen Inventaris No. 14',
                ],
                // Dokumen No. 15: Panci Besar (2 Buah)
                [
                    'name' => 'Panci Besar (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Panci masak besar logistik/dapur umum (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 15',
                ],
                // Dokumen No. 16: Box Obat (3 Buah)
                [
                    'name' => 'Box Obat P3K (3 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Kotak/box penyimpanan obat pertolongan pertama (Jumlah: 3 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 16',
                ],
                // Dokumen No. 20: Tempat Tidur Pasien
                [
                    'name' => 'Tempat Tidur Pasien (Hospital Bed)',
                    'status' => 'tersedia',
                    'description' => 'Bed pasien poliklinik beserta kasur (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 20',
                ],
                // Dokumen No. 21: AC
                [
                    'name' => 'AC (Air Conditioner) Ruangan',
                    'status' => 'tersedia',
                    'description' => 'Unit pendingin ruangan AC (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 21',
                ],
                // Dokumen No. 23: Jam Dinding
                [
                    'name' => 'Jam Dinding Ruangan',
                    'status' => 'tersedia',
                    'description' => 'Jam dinding analog ruangan (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 23',
                ],
                // Dokumen No. 26: Buku Pengunjung
                [
                    'name' => 'Buku Pengunjung Poliklinik',
                    'status' => 'tersedia',
                    'description' => 'Buku catatan kunjungan pasien (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 26',
                ],
                // Dokumen No. 27: Kursi (4 Buah)
                [
                    'name' => 'Kursi Ruangan (4 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Kursi duduk ruangan poliklinik (Jumlah: 4 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 27',
                ],
                // Dokumen No. 28: Buku Pinjaman
                [
                    'name' => 'Buku Peminjaman Alat',
                    'status' => 'tersedia',
                    'description' => 'Buku catatan peminjaman barang inventaris (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 28',
                ],
                // Dokumen No. 29: Taplak Meja
                [
                    'name' => 'Taplak Meja Ruangan',
                    'status' => 'tersedia',
                    'description' => 'Taplak meja ruangan poliklinik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 29',
                ],
                // Dokumen No. 30: Oximeter
                [
                    'name' => 'Oximeter (Fingertip Pulse Oximeter)',
                    'status' => 'tersedia',
                    'description' => 'Alat medis pengukur saturasi SpO2 dan detak jantung (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 30',
                ],
                // Dokumen No. 31: Piring (50 Buah)
                [
                    'name' => 'Piring Makan (50 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Piring makan logistik acara/kegiatan (Jumlah: 50 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 31',
                ],
                // Dokumen No. 32: Gelas (10 Buah)
                [
                    'name' => 'Gelas Minum (10 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Gelas minum konsumsi logistik (Jumlah: 10 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 32',
                ],
                // Dokumen No. 33: Palu Sidang
                [
                    'name' => 'Palu Sidang dan Tatakan',
                    'status' => 'tersedia',
                    'description' => 'Palu sidang musyawarah resmi (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 33',
                ],
                // Dokumen No. 34: Foto Wapres
                [
                    'name' => 'Bingkai Foto Wakil Presiden RI',
                    'status' => 'tersedia',
                    'description' => 'Foto resmi Wakil Presiden RI berbingkai (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 34',
                ],
                // Dokumen No. 35: Foto Pancasila
                [
                    'name' => 'Bingkai Lambang Garuda Pancasila',
                    'status' => 'tersedia',
                    'description' => 'Lambang resmi Garuda Pancasila berbingkai (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 35',
                ],
                // Dokumen No. 36: Baju Pelatih
                [
                    'name' => 'Baju Pelatih KSR',
                    'status' => 'tersedia',
                    'description' => 'Seragam baju pelatih/instruktur (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 36',
                ],
                // Dokumen No. 37: Staples (3 Buah)
                [
                    'name' => 'Staples / Hekter (3 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Alat jilid staples kantor (Jumlah: 3 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 37',
                ],
                // Dokumen No. 39: Piring Kue (2 Pcs)
                [
                    'name' => 'Piring Kue (2 Pcs)',
                    'status' => 'tersedia',
                    'description' => 'Piring saji kue/snack (Jumlah: 2 Pcs). Kondisi Baik. Sesuai Dokumen Inventaris No. 39',
                ],
                // Dokumen No. 40: Alat Bidai PP
                [
                    'name' => 'Alat Bidai Pertolongan Pertama (PP)',
                    'status' => 'tersedia',
                    'description' => 'Set bidai fiksasi patah tulang pertolongan pertama (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 40',
                ],
                // Dokumen No. 42: Baju PDL (3 Buah)
                [
                    'name' => 'Baju PDL KSR (3 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Pakaian Dinas Lapangan KSR PMI (Jumlah: 3 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 42',
                ],
                // Dokumen No. 43: Baju PDH (1 Buah)
                [
                    'name' => 'Baju PDH KSR',
                    'status' => 'tersedia',
                    'description' => 'Pakaian Dinas Harian KSR PMI (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 43',
                ],
                // Dokumen No. 44: Dispenser
                [
                    'name' => 'Dispenser Air Minum',
                    'status' => 'tersedia',
                    'description' => 'Dispenser galon air minum (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 44',
                ],
                // Dokumen No. 45: Galon (2 Buah)
                [
                    'name' => 'Galon Air Minum (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Galon air mineral (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 45',
                ],
                // Dokumen No. 46: Bendera PMI (2 Buah)
                [
                    'name' => 'Bendera Palang Merah Indonesia (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Bendera resmi lambang PMI (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 46',
                ],
                // Dokumen No. 47: Bidai (1 Set)
                [
                    'name' => 'Bidai Fraktur Tambahan',
                    'status' => 'tersedia',
                    'description' => 'Set bidai kayu fiksasi fraktur (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 47',
                ],
                // Dokumen No. 49: Baskom (3 Buah)
                [
                    'name' => 'Baskom Sanitasi (3 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Baskom cuci medis dan sanitasi (Jumlah: 3 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 49',
                ],
                // Dokumen No. 50: Karpet (2 Buah)
                [
                    'name' => 'Karpet Lantai (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Karpet lantai ruangan pertemuan (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 50',
                ],
                // Dokumen No. 51: Pemanggang Kecil
                [
                    'name' => 'Pemanggang Kecil',
                    'status' => 'tersedia',
                    'description' => 'Alat pemanggang kecil dapur logistik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 51',
                ],
                // Dokumen No. 53: X Banner KAP (Rusak Ringan)
                [
                    'name' => 'X Banner Komunikasi Antar Personal (KAP)',
                    'status' => 'rusak',
                    'description' => 'X Banner materi KAP (1 Buah). Kondisi: Rusak Ringan. Sesuai Dokumen Inventaris No. 53',
                ],
                // Dokumen No. 54: X Banner Harapan (Rusak Ringan)
                [
                    'name' => 'X Banner Harapan',
                    'status' => 'rusak',
                    'description' => 'X Banner kegiatan Harapan (1 Buah). Kondisi: Rusak Ringan. Sesuai Dokumen Inventaris No. 54',
                ],
                // Dokumen No. 55: Cap
                [
                    'name' => 'Cap / Stempel Resmi KSR PMI',
                    'status' => 'tersedia',
                    'description' => 'Stempel organisasi resmi KSR PMI Unit INSTIKI (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 55',
                ],
                // Dokumen No. 56: Alat Cek Tensi (Rusak Berat)
                [
                    'name' => 'Alat Cek Tensi (Tensimeter)',
                    'status' => 'rusak',
                    'description' => 'Alat pengukur tekanan darah (1 Buah). Kondisi: Rusak Berat (Perlu perbaikan/penggantian). Sesuai Dokumen Inventaris No. 56',
                ],
                // Dokumen No. 57: Alat Sedot Air Galon (Rusak Berat)
                [
                    'name' => 'Alat Sedot Air Galon (Pompa Galon)',
                    'status' => 'rusak',
                    'description' => 'Pompa galon elektrik/manual (1 Buah). Kondisi: Rusak Berat. Sesuai Dokumen Inventaris No. 57',
                ],
                // Dokumen No. 59: Meja (2 Buah: 1 Rusak Ringan, 1 Rusak Berat)
                [
                    'name' => 'Meja Kerja Ruangan (Unit 1)',
                    'status' => 'rusak',
                    'description' => 'Meja kerja ruangan poliklinik unit 1. Kondisi: Rusak Ringan. Sesuai Dokumen Inventaris No. 59',
                ],
                [
                    'name' => 'Meja Kerja Ruangan (Unit 2)',
                    'status' => 'rusak',
                    'description' => 'Meja kerja ruangan poliklinik unit 2. Kondisi: Rusak Berat (Perlu perbaikan). Sesuai Dokumen Inventaris No. 59',
                ],
                // Dokumen No. 60: Rak Obat
                [
                    'name' => 'Rak / Lemari Obat Ruangan',
                    'status' => 'tersedia',
                    'description' => 'Lemari rak kaca/kayu penyimpanan obat poliklinik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 60',
                ],
                // Dokumen No. 61: Penggaris Kayu
                [
                    'name' => 'Penggaris Kayu Panjang',
                    'status' => 'tersedia',
                    'description' => 'Penggaris kayu panjang ruangan (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 61',
                ],
                // Dokumen No. 62: Buku Inventaris
                [
                    'name' => 'Buku Inventaris Fisik',
                    'status' => 'tersedia',
                    'description' => 'Buku catatan arsip inventaris fisik barang (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 62',
                ],
                // Dokumen No. 63: Buku Perwakilan
                [
                    'name' => 'Buku Perwakilan Organisasi',
                    'status' => 'tersedia',
                    'description' => 'Buku pencatatan perwakilan dan delegasi KSR (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 63',
                ],
                // Dokumen No. 64: Termometer
                [
                    'name' => 'Termometer Klinis Pengukur Suhu',
                    'status' => 'tersedia',
                    'description' => 'Termometer badan klinis poliklinik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 64',
                ],
                // Dokumen No. 66: Talenan
                [
                    'name' => 'Talenan Pemotong Dapur',
                    'status' => 'tersedia',
                    'description' => 'Talenan dapur logistik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 66',
                ],
                // Dokumen No. 68: Kain Putih (2 Buah)
                [
                    'name' => 'Kain Putih Lapangan (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Kain putih keperluan medis/pertolongan pertama (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 68',
                ],
                // Dokumen No. 69: Pasu Najis
                [
                    'name' => 'Pasu Najis (Bedpan Pasien)',
                    'status' => 'tersedia',
                    'description' => 'Pispot / bedpan sanitasi pasien (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 69',
                ],
                // Dokumen No. 70: Piala (2 Buah)
                [
                    'name' => 'Piala Penghargaan (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Piala trofi penghargaan prestasi KSR PMI (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 70',
                ],
                // Dokumen No. 71: Lampu (2 Buah)
                [
                    'name' => 'Lampu Penerangan Darurat (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Lampu darurat/penerangan ruangan (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 71',
                ],
                // Dokumen No. 72: Alat Medis
                [
                    'name' => 'Set Peralatan Medis P3K',
                    'status' => 'tersedia',
                    'description' => 'Set peralatan medis pertolongan pertama (1 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 72',
                ],
                // Dokumen No. 73: Rak Medis
                [
                    'name' => 'Rak Peralatan Medis',
                    'status' => 'tersedia',
                    'description' => 'Rak susun tempat peralatan medis (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 73',
                ],
                // Dokumen No. 74: Bendera UKM KSR
                [
                    'name' => 'Bendera UKM KSR PMI Unit INSTIKI',
                    'status' => 'tersedia',
                    'description' => 'Bendera pataka resmi UKM KSR PMI Unit INSTIKI (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 74',
                ],
                // Dokumen No. 75: Kaki X Banner (6 Set)
                [
                    'name' => 'Kaki X Banner Stand (6 Set)',
                    'status' => 'tersedia',
                    'description' => 'Rangka kaki penyangga X-Banner (Jumlah: 6 Set). Kondisi Baik. Sesuai Dokumen Inventaris No. 75',
                ],
                // Dokumen No. 76: Papan Kecil
                [
                    'name' => 'Papan Pengumuman Kecil',
                    'status' => 'tersedia',
                    'description' => 'Papan pengumuman/tulis kecil (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 76',
                ],
                // Dokumen No. 77: Gorden (6 Buah)
                [
                    'name' => 'Gorden Sekat Ruangan (6 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Gorden jendela dan sekat tirai poliklinik (Jumlah: 6 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 77',
                ],
                // Dokumen No. 78: Pegangan Gorden (2 Buah)
                [
                    'name' => 'Pegangan / Rel Pipa Gorden (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Pipa rel gorden ruangan (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 78',
                ],
                // Dokumen No. 79: Pisau
                [
                    'name' => 'Pisau Dapur Serbaguna',
                    'status' => 'tersedia',
                    'description' => 'Pisau pemotong dapur logistik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 79',
                ],
                // Dokumen No. 80: Stetoskop (2 Buah)
                [
                    'name' => 'Stetoskop Medis (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Stetoskop diagnostik medis auskultasi (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 80',
                ],
                // Dokumen No. 81: Stop kontak (Cuk Roll)
                [
                    'name' => 'Stop Kontak Roll Listrik (Cuk Roll)',
                    'status' => 'tersedia',
                    'description' => 'Terminal kabel rol colokan listrik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 81',
                ],
                // Dokumen No. 82: Kipas
                [
                    'name' => 'Kipas Angin Ruangan',
                    'status' => 'tersedia',
                    'description' => 'Kipas angin ventilasi poliklinik (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 82',
                ],
                // Dokumen No. 83: Speaker Kecil
                [
                    'name' => 'Speaker Kecil Audio',
                    'status' => 'tersedia',
                    'description' => 'Speaker audio mini portable (1 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 83',
                ],
                // Dokumen No. 84: Ember Kecil (2 Buah)
                [
                    'name' => 'Ember Kecil Sanitasi (2 Buah)',
                    'status' => 'tersedia',
                    'description' => 'Ember plastik kecil (Jumlah: 2 Buah). Kondisi Baik. Sesuai Dokumen Inventaris No. 84',
                ],
            ];

            foreach ($assetsList as $assetData) {
                // Check if already created by name
                $existing = Asset::where('name', $assetData['name'])->first();
                if (! $existing) {
                    $code = Asset::generateCode($assetData['name'], 'poliklinik');

                    $asset = Asset::create([
                        'code' => $code,
                        'name' => $assetData['name'],
                        'warehouse' => 'poliklinik',
                        'acquisition_year' => 2024,
                        'funding_source' => 'Inventaris KSR INSTIKI',
                        'location' => 'Poliklinik / Ruang Inventaris',
                        'description' => $assetData['description'],
                        'status' => $assetData['status'],
                    ]);

                    AssetLog::create([
                        'asset_id' => $asset->id,
                        'action' => 'create',
                        'actor_id' => $actorId,
                        'actor_name' => $actorName,
                        'purpose' => 'Pencatatan data inventaris barang awal',
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
