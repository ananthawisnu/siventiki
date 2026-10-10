# 🏥 SISTEM INFORMASI INVENTARIS LOGISTIK MEDIS & ASET TETAP
## KORPS SUKARELA PALANG MERAH INDONESIA (KSR PMI) UNIT INSTIKI

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12%20%2F%2013-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-%3E%3D%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/TailwindCSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Database-MySQL%20%2F%20SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Testing-Pest%20PHP-7852FF?style=for-the-badge&logo=pest&logoColor=white" alt="Pest">
</p>

---

## 📌 Ringkasan Sistem

**Sistem Informasi Inventaris KSR PMI Unit INSTIKI** adalah aplikasi berbasis web yang dikembangkan khusus untuk mengelola logistik pertolongan pertama, obat-obatan medis, Bahan Medis Habis Pakai (BHP), serta aset tetap peralatan darurat kampus secara akurat, transparan, dan terstandarisasi.

Sistem ini menerapkan algoritma **FEFO (*First Expired, First Out*)** untuk menjamin keamanan distribusi obat dan BHP, pelacakan riwayat aset tetap (*lifecycle tracking*), mutasi antar gudang, peminjaman peralatan medis lintas divisi kampus, serta pembuatan Laporan Pertanggungjawaban (LPJ) resmi ber-KOP instansi.

---

## 🏛️ Lingkup Multi-Gudang & Role Pengguna

Sistem dirancang dengan arsitektur **Warehouse Scoping** dan **Role-Based Access Control (RBAC)**:

| Role Pengguna | Lingkup Gudang | Wewenang & Hak Akses |
|---|---|---|
| **Super Admin** | **Semua Gudang** (`all`) | Akses penuh seluruh gudang, manajemen pengguna (NIM-based), konfigurasi logo & kop surat resmi, monitoring global. |
| **Admin Gudang Obat** | **Gudang Obat** (`ksr`) | Operasional logistik medis, obat-obatan, BHP markas KSR, penerimaan barang (*inbound*), pengeluaran FEFO (*outbound*), opname, dan pengajuan mutasi. |
| **Admin Gudang Inventaris** | **Gudang Inventaris** (`poliklinik`) | Manajemen aset tetap, tandu, tensimeter, tabung oksigen, penerimaan mutasi, dan peminjaman peralatan medis lintas ormawa/unit kampus. |

---

## ✨ Fitur-Fitur Utama

### 1. 💊 Master Data & Katalog Logistik Medis
* **Klasifikasi Kategori Terstandar**:
  * **Obat Medis**: Wajib nomor batch & tanggal kedaluwarsa (*expiry date*), diproses dengan algoritma FEFO.
  * **BHP Medis**: Bahan medis habis pakai (kasa steril, plester, spuit) dengan batas minimum stok (*reorder point*).
  * **BHP Non Medis**: Kebutuhan operasional (hand sanitizer, masker, plastik klip obat).
* **Perhitungan Saldo Dinamis**: Stok dihitung secara *real-time* dari akumulasi batch aktif (`sum(quantity)`), menjamin data selalu konsisten.
* **Kartu Stok (*Stock Card Ledger*)**: Riwayat pergerakan saldo berjalan (*running balance*) per item secara kronologis lengkap dengan ekspor.

### 2. ⚡ Pergerakan Stok & Algoritma FEFO (First Expired, First Out)
* **Penerimaan Barang (*Inbound*)**: Pencatatan barang masuk dari donasi/faktur, pembuatan slot batch baru, dan penambahan saldo otomatis.
* **Pengeluaran Otomatis FEFO (*Outbound*)**: Sistem secara cerdas mengalokasikan stok dari batch yang memiliki tanggal kedaluwarsa paling dekat terlebih dahulu. Setiap alokasi batch disimpan dalam riwayat audit trail (*JSON allocations*).
* **Stock Opname Fisik**: Penyesuaian stok berkala antara kondisi fisik dan sistem. Otomatis menghitung selisih (*variance delta*), memperbarui kuantitas batch, dan mencatat log audit.

### 3. 🔄 Mutasi Logistik Antar Gudang (*Inter-Warehouse Transfer*)
* Siklus mutasi persediaan antara **Gudang Obat ↔ Gudang Inventaris**.
* Menerapkan *State Machine*: **`In Transit`** &rarr; **`Received`** (atau **`Cancelled`**).
* Pengurangan stok asal berbasis FEFO saat pengiriman, serta pembuatan *sibling item* & batch identik otomatis di gudang tujuan saat diterima.

### 4. 🩺 Manajemen Aset Tetap & Siklus Hidup (*Lifecycle*)
* **Penomoran Otomatis**: Format kode unik otomatis seperti `ASET-KSR-TANDULIP-001`.
* **Pelacakan Status Real-time**: `Tersedia` *(Ready)*, `Digunakan` *(In Use)*, dan `Rusak` *(Maintenance)*.
* **Timeline Riwayat Aset**: Riwayat vertikal gabungan aktivitas servis, peminjaman, dan pergantian status.

### 5. 🤝 Peminjaman Aset Lintas Divisi Kampus
* Peminjaman peralatan darurat medis ke ormawa, BEM, UKM, atau panitia kegiatan kampus.
* Pendataan PIC, nomor WhatsApp, keperluan, dan estimasi tanggal kembali.
* Otomatisasi pengalihan status aset ke `Digunakan` dan kembali ke `Tersedia` setelah proses verifikasi pengembalian.

### 6. 📊 Dashboard Interaktif & Analitik
* **Metric Cards**: Total unit stok, aset siap pakai, stok kritis, dan jendela obat kedaluwarsa.
* **Jendela Kedaluwarsa Dinamis**: Tab klasifikasi kedaluwarsa: *Lewat, &le;30 hari, &le;60 hari, &le;90 hari*.
* **Grafik Tren Konsumsi 6 Bulan**: Visualisasi tren pengeluaran Obat vs BHP menggunakan Chart.js.
* **Peringatan Batas Minimum (*Reorder Point Alert*)**: Menampilkan daftar barang di bawah ambang batas minimal stok.

### 7. 📑 Laporan Pertanggungjawaban (LPJ) & Ekspor
* **Pilihan Laporan**:
  1. *Laporan Pergerakan Stok & Mutasi (Inbound / Outbound / Opname)*
  2. *Laporan Inventaris Aset Tetap & Kondisi*
  3. *Laporan Rekapitulasi Stock Opname Fisik*
* **Cetak Lembar Resmi (Kop Surat)**: Format siap cetak/PDF berstandar PMI INSTIKI lengkap dengan logo instansi dan kolom tanda tangan pimpinan/pengurus.
* **Ekspor CSV / Excel**: Dilengkapi UTF-8 BOM untuk integrasi ke Microsoft Excel dan Google Sheets.

### 8. ⚙️ Manajemen Pengguna & KOP Surat (Super Admin)
* Pengaturan data akun berbasis NIM, status aktif/non-aktif, dan penggantian kata sandi.
* Kustomisasi logo resmi (Logo KSR PMI dan Logo INSTIKI), identitas lembaga, alamat, kontak, dan penandatangan dokumen resmi.

---

## 🛠️ Arsitektur & Teknologi

* **Framework Backend**: [Laravel 12 / 13](https://laravel.com) (PHP 8.3+)
* **Database**: MySQL / MariaDB (Dukungan SQLite untuk unit testing)
* **Frontend UI**: Blade Templates, [Tailwind CSS v4](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev)
* **Grafik & Visual**: [Chart.js](https://www.chartjs.org/)
* **Interaktivitas & Alert**: [SweetAlert2](https://sweetalert2.github.io/)
* **Build Tooling**: [Vite 8](https://vitejs.dev)
* **Test Suite**: [Pest PHP](https://pestphp.com)

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Prasyarat Sistem
Pastikan lingkungan pengembangan Anda telah terpasang:
* **PHP** &ge; 8.3 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`)
* **Composer** &ge; 2.x
* **Node.js** &ge; 18.x & **NPM**
* **MySQL** / **MariaDB** (atau menggunakan Laragon / XAMPP)

---

### 2. Langkah-Langkah Instalasi

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/ananthawisnu/siventiki.git
   cd siventiki
   ```

2. **Install Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Install Dependensi Frontend**:
   ```bash
   npm install
   ```

4. **Konfigurasi Environment (`.env`)**:
   Salin berkas `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```
   Sesuaikan konfigurasi database Anda pada `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventaris_ksr_instiki
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate Kunci Aplikasi**:
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seeder**:
   Buat database `inventaris_ksr_instiki` di MySQL/Laragon terlebih dahulu, lalu jalankan:
   ```bash
   php artisan migrate --seed
   ```

7. **Kompilasi Asset Frontend**:
   * Mode Pengembangan:
     ```bash
     npm run dev
     ```
   * Mode Produksi:
     ```bash
     npm run build
     ```

8. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Akses aplikasi pada peramban Anda melalui: **`http://127.0.0.1:8000`** atau domain lokal Laragon Anda.

---

## 🔑 Kredensial Login Default

Setelah menjalankan `php artisan db:seed`, akun berikut siap digunakan:

| Role | Username (NIM) | Password Default | Lingkup Akses |
|---|---|---|---|
| **Super Admin** | `2401020002` | `16marett` | Semua Gudang (KSR & Poliklinik) |

> 💡 *Admin Gudang Obat (`admin_ksr`) dan Admin Gudang Inventaris (`admin_poliklinik`) dapat ditambahkan secara fleksibel melalui menu **Manajemen Pengguna** oleh Super Admin.*

---

## 🧪 Menjalankan Pengujian (Testing)

Aplikasi telah dilengkapi dengan rangkaian pengujian terotomatisasi menggunakan **Pest PHP** untuk memvalidasi algoritma FEFO, autentikasi, mutasi gudang, dan siklus hidup aset:

```bash
# Menjalankan seluruh pengujian
php artisan test

# Menjalankan pengujian dengan Pest secara spesifik
vendor/bin/pest tests/Feature/InventorySystemTest.php
```

---

## 📂 Struktur Direktori Utama

```
inventarisksrpmiunitinstiki/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Controller aplikasi (Item, Movement, Asset, Loan, Transfer, Report, dll.)
│   │   └── Middleware/          # RoleMiddleware & verifikasi akses gudang
│   ├── Models/                  # Eloquent Models (Item, Batch, Movement, Asset, AssetLog, Loan, Transfer, User, Setting)
│   └── Services/
│       └── InventoryService.php # Business logic inti: FEFO Engine, Inbound, Outbound, Opname, Transfer
├── database/
│   ├── migrations/              # Skema tabel database inventaris & logistik
│   └── seeders/                 # DatabaseSeeder (Akun default & konfigurasi awal lembaga)
├── resources/
│   ├── css/                     # Styling Tailwind CSS
│   ├── js/                      # Script JS frontend
│   └── views/                   # Tampilan Blade (Dashboard, Items, Movements, Assets, Transfers, Loans, Reports, Settings)
├── routes/
│   └── web.php                  # Seluruh rute aplikasi (Guest, Auth, Super Admin)
├── tests/
│   └── Feature/                 # Feature tests algoritma FEFO, Stock Opname, Mutasi, Aset
├── MANUAL_BOOK.md               # Buku panduan penggunaan lengkap untuk operasional pengguna
└── README.md                    # Dokumentasi utama proyek
```

---

## 📖 Buku Panduan Pengguna

Untuk panduan operasional langkah demi langkah bagi pengguna akhir dan pengurus KSR, silakan baca [MANUAL_BOOK.md](file:///c:/laragon/www/inventarisksrpmiunitinstiki/MANUAL_BOOK.md).

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan operasional **Korps Sukarela Palang Merah Indonesia Unit Institut Bisnis dan Teknologi Indonesia (KSR PMI Unit INSTIKI)** dan dilisensikan di bawah lisensi [MIT](LICENSE).
