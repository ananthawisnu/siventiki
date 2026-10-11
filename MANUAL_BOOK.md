# 📖 BUKU PANDUAN PENGGUNA (MANUAL BOOK) FINAL
## SISTEM INFORMASI INVENTARIS LOGISTIK MEDIS & ASET TETAP
### KORPS SUKARELA PALANG MERAH INDONESIA (KSR PMI) UNIT INSTIKI

---

## DAFTAR ISI
1. [Tentang Sistem & Lingkup Gudang](#1-tentang-sistem--lingkup-gudang)
2. [Hak Akses & Role Pengguna](#2-hak-akses--role-pengguna)
3. [Klasifikasi Kategori Barang & Aset](#3-klasifikasi-kategori-barang--aset)
4. [Alur Manajemen Katalog Obat & BHP](#4-alur-manajemen-katalog-obat--bhp)
5. [Alur Pergerakan Stok & Algoritma FEFO](#5-alur-pergerakan-stok--algoritma-fefo)
6. [Mutasi Logistik Antar Gudang](#6-mutasi-logistik-antar-gudang)
7. [Manajemen Aset Tetap & Siklus Hidup](#7-manajemen-aset-tetap--siklus-hidup)
8. [Peminjaman Lintas Divisi / Eksternal](#8-peminjaman-lintas-divisi--eksternal)
9. [Laporan Pertanggungjawaban (LPJ) & Ekspor](#9-laporan-pertanggungjawaban-lpj--ekspor)
10. [Pengaturan Super Admin & Pengguna](#10-pengaturan-super-admin--pengguna)

---

## 1. Tentang Sistem & Lingkup Gudang

Sistem Inventaris KSR PMI Unit INSTIKI dirancang khusus untuk mengelola logistik pertolongan pertama, obat-obatan, bahan medis habis pakai (BHP), serta aset tetap peralatan darurat secara akurat, transparan, dan terstandarisasi.

### Lingkup Gudang yang Dikelola:
* **Gudang Obat**: Didedikasikan untuk penyimpanan obat-obatan medis, multivitamin, dan bahan medis habis pakai (BHP) utama markas KSR.
* **Gudang Inventaris**: Didedikasikan untuk penyimpanan peralatan medis portabel, sarana prasarana, tandu, tabung oksigen, spalk, dan logistik inventaris poliklinik/kampus.

---

## 2. Hak Akses & Role Pengguna

Login ke dalam sistem menggunakan **Username / NIM** dan Password terdaftar.

| Role | Username Default | Password Default | Cakupan Akses | Fitur Khusus |
|---|---|---|---|---|
| **Super Admin** | `superadminksr` *(dapat diubah)* | `password123` | Akses Penuh ke **Semua Gudang** (Gudang Obat + Gudang Inventaris) | Manajemen Akun, Konfigurasi Kop Surat & Tanda Tangan, Filter Gudang Global, Ubah Username/NIM Akun |
| **Admin Gudang Obat** | Didaftarkan via NIM | Didaftarkan oleh Super Admin | Terbatas pada data & pergerakan **Gudang Obat** | Katalog Obat, Inbound/Outbound/Opname Gudang Obat, Pengiriman Mutasi |
| **Admin Gudang Inventaris** | Didaftarkan via NIM | Didaftarkan oleh Super Admin | Terbatas pada data & pergerakan **Gudang Inventaris** | Registrasi Aset, Peminjaman, Penerimaan Mutasi, Pemeliharaan Aset |

> 📌 **Catatan Khusus Super Admin**:
> Khusus akun **Super Admin**, username dapat diganti kapan saja secara fleksibel (tidak terkunci pada format NIM angka) melalui menu **Profil Pengguna** atau **Manajemen Pengguna**. Sedangkan untuk akun Admin Gudang, username tetap dikunci menggunakan NIM mahasiswa untuk standarisasi identitas anggota.

---

## 3. Klasifikasi Kategori Barang & Aset

### A. Kategori Barang Habis Pakai (Menu: *Katalog Obat & BHP*)
1. **Obat Medis (Badge Kuning)**
   * *Contoh*: Paracetamol, Antasida, Betadine Sol., Salep Burnazin, Amoxicillin.
   * *Aturan Khusus*: **Wajib** mencantumkan **Nomor Batch** dan **Tanggal Kedaluwarsa (Expiry Date)** saat barang masuk (*Inbound*).
   * *Pengeluaran*: Diatur otomatis oleh algoritma **FEFO** (*First Expired, First Out*).
2. **BHP Medis (Badge Hijau)**
   * *Contoh*: Kasa steril, Plester gulung, Sarung tangan latex, Spuit, Alkohol swab.
   * *Aturan Khusus*: Wajib menentukan **Batas Minimum Stok** untuk sistem peringatan (*critical stock alert*). Batch & Expiry bersifat opsional.
3. **BHP Non Medis (Badge Biru)**
   * *Contoh*: Masker non-medis, Hand sanitizer, Plastik klip obat, Sendok takar, Label resep.
   * *Aturan Khusus*: Habis pakai operasional umum tanpa tanggal kedaluwarsa ketat.

### B. Kategori Aset Tetap (Menu: *Aset Tetap & Riwayat*)
* *Contoh*: Tandu Lipat (Stretcher), Tabung Oksigen & Regulator, Tensimeter Digital, Kotak P3K Lapangan, Megaphone, Tenda Posko.
* *Karakteristik*: Memiliki **Kode Registrasi Unik**, tahun perolehan, sumber pendanaan (DIPA/Donasi), dan pelacakan riwayat kondisi (Tersedia, Digunakan, Rusak/Perbaikan).

---

## 4. Alur Manajemen Katalog Obat & BHP

1. Buka menu **Katalog Obat & BHP**.
2. Klik tombol **"+ Tambah Barang Baru"**.
3. Isi formulir:
   * **Nama Barang**: Nama resmi beserta sediaan (cth: *Paracetamol 500mg Kaplet*).
   * **Kategori**: Pilih *Obat Medis*, *BHP Medis*, atau *BHP Non Medis*.
   * **Gudang**: Tentukan lokasi penyimpanan (*Gudang Obat* atau *Gudang Inventaris*).
   * **Satuan**: Sediaan terkecil (*tablet, botol, roll, box, pcs*).
   * **Batas Min. Stok**: Jumlah minimum sebelum sistem memicu alert merah.
4. Klik **"Simpan Barang"** — Notifikasi manis **SweetAlert2** akan mengonfirmasi pendaftaran sukses.
5. Klik **"Kartu Stok"** pada baris barang untuk melihat riwayat saldo berjalan (*running balance ledger*) secara kronologis.

---

## 5. Alur Pergerakan Stok & Algoritma FEFO

Menu **Pergerakan Stok (FEFO)** mengelola siklus keluar-masuk barang secara matematis:

### A. Barang Masuk (Inbound / Penerimaan)
1. Klik tombol **"+ Penerimaan Stok (Inbound)"**.
2. Pilih barang dari gudang Anda.
3. Masukkan jumlah, tanggal terima, nomor faktur/supplier, serta **Nomor Batch & Expiry Date** (wajib jika kategori Obat).
4. Klik **"Simpan Penerimaan"** — Sistem akan membuat slot batch baru dan menambah saldo barang.

### B. Pengeluaran Stok (Outbound / Pelayanan Medis)
1. Klik tombol **"- Pengeluaran Stok (Outbound)"**.
2. Pilih obat/BHP yang akan dikeluarkan dan masukkan jumlah yang dibutuhkan.
3. **Algoritma FEFO**: Sistem secara otomatis mengalokasikan stok dari batch yang memiliki tanggal kedaluwarsa paling awal.
4. Isi tujuan penggunaan (cth: *Pelayanan Posko Donor Darah Dies Natalis*) dan nama penerima.
5. Klik **"Proses Pengeluaran"**.

### C. Stock Opname (Penyesuaian Fisik)
1. Digunakan saat audit fisik berkala.
2. Pilih batch yang diaudit, masukkan jumlah real fisik.
3. Sistem menghitung selisih (*variance*) dan merekam log audit secara otomatis.

---

## 6. Mutasi Logistik Antar Gudang

Memindahkan stok obat/BHP antara **Gudang Obat ↔ Gudang Inventaris**:

1. **Pembuatan Mutasi (Gudang Asal)**:
   * Buka menu **Mutasi Antar Gudang** → Klik **"Buat Mutasi Baru"**.
   * Pilih barang asal dan gudang tujuan, masukkan jumlah mutasi.
   * Status tercatat sebagai **`In Transit` (Dalam Pengiriman)**, stok asal terpotong melalui FEFO.
2. **Penerimaan Mutasi (Gudang Tujuan)**:
   * Petugas gudang tujuan membuka menu **Mutasi Antar Gudang**.
   * Pada baris mutasi `In Transit`, klik tombol hijau **"Terima Barang"**.
   * SweetAlert2 konfirmasi akan muncul → Klik **"Ya, Terima Barang"**.
   * Sistem otomatis membuat batch identik di gudang tujuan dan status berubah menjadi **`Diterima (Received)`**.

---

## 7. Manajemen Aset Tetap & Siklus Hidup

Menu **Aset Tetap & Riwayat**:
* **Registrasi Aset**: Menghasilkan kode unik otomatis: `ASET-KSR-TENSIMETER-001` atau `ASET-POL-TANDU-002`.
* **Ubah Status**:
  * *Tersedia (Ready)* → Siap digunakan/dipinjamkan.
  * *Digunakan (In Use)* → Sedang bertugas di lapangan.
  * *Rusak / Perbaikan (Maintenance)* → Catat rincian kerusakan dan biaya perbaikan.
* **Histori Terintegrasi**: Setiap pergantian status dan riwayat servis tercatat permanen.

---

## 8. Peminjaman Lintas Divisi / Eksternal

Menu **Peminjaman Lintas Divisi**:
* **Peminjaman Baru**: Pilih aset yang berstatus *Tersedia*, masukkan divisi peminjam (cth: *BEM, Ormawa, UKM Korps, Panitia PKKMB*), nama PIC, kontak WhatsApp, estimasi tanggal kembali, dan keperluan.
* Status aset otomatis berubah menjadi **`Digunakan`**.
* **Pengembalian**: Saat barang kembali, klik tombol **"Kembalikan"** dengan konfirmasi SweetAlert2. Status aset seketika pulih menjadi **`Tersedia`**.

---

## 9. Laporan Pertanggungjawaban (LPJ) & Ekspor

Menu **Laporan & LPJ**:
1. Pilih Jenis Laporan:
   * *1. Kartu Stok & Pergerakan (In/Out/Opname)*
   * *2. Inventaris Aset Tetap & Kondisi*
   * *3. Rekapitulasi Stock Opname Fisik*
2. Filter rentang tanggal dan gudang.
3. Fitur Ekspor:
   * **🖨️ Cetak / Simpan PDF (Kop Resmi)**: Menggunakan layout standar PMI INSTIKI, lengkap dengan nomor surat, logo, dan kolom tanda tangan Pengurus / Komandan KSR.
   * **📊 Ekspor CSV / Excel**: Untuk keperluan olah data administratif spreadsheets.

---

## 10. Pengaturan Super Admin & Pengguna

* **Manajemen Pengguna**: Tambah anggota baru, ubah role (Admin Gudang Obat / Admin Gudang Inventaris / Super Admin), reset password, ubah username khusus akun Super Admin, atau nonaktifkan akun anggota yang telah demisioner.
* **Profil Super Admin**: Akses menu Profil untuk memperbarui nama, email, password, serta username Super Admin (`superadminksr` atau kustom).
* **Pengaturan Logo & Kop**: Unggah logo resmi KSR PMI, logo kampus INSTIKI, atur nama instansi, alamat markas, dan nama/NIM penandatangan laporan resmi.

---

*Buku Panduan ini disusun untuk standarisasi operasional Korps Sukarela Palang Merah Indonesia Unit Institut Bisnis dan Teknologi Indonesia (INSTIKI).*
