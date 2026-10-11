# Sistem Inventaris KSR PMI - Unit INSTIKI
## Detail Fitur Lengkap A – Z

---

### 🔐 A. Autentikasi & Manajemen Akses (RBAC)

- **Login JWT** dengan HttpOnly cookie (sesi 8 jam), form username + password.
- **3 Role dengan lingkup berbeda**:
  - **Super Admin** — akses penuh (KSR + Poliklinik + semua menu admin)
  - **Admin KSR** — hanya data Gudang KSR
  - **Admin Poliklinik** — hanya data Poliklinik
- **Manajemen Pengguna** (Super Admin): tambah/hapus akun, pilih role, warehouse otomatis mengikuti role.
- **Scope enforcement backend**: setiap query difilter otomatis berdasarkan `user.warehouse` sehingga admin tidak bisa mengintip data gudang lain.

---

### 📊 B. Dashboard Interaktif

- **4 Metric Cards**: Total unit stok, Aset tetap, Stok kritis, Akan kedaluwarsa (+ jumlah yang sudah lewat).
- **Jendela Kedaluwarsa** dengan tabs: **Lewat / ≤30 / ≤60 / ≤90 hari** — otomatis mengurutkan batch obat berdasarkan sisa hari.
- **Reorder Point** — daftar BHP yang stoknya ≤ batas minimum.
- **Status Aset Tetap** — count Tersedia / Digunakan / Rusak.
- **Aktivitas Terbaru** — 8 pergerakan stok terkini (inbound/outbound/opname) dengan nama barang.
- **Grafik Tren 6 Bulan** — BarChart konsumsi bulanan Obat vs BHP untuk prediksi restock.
- **Tombol Segarkan** untuk refresh data real-time.

---

### 📦 C. Modul Data Master (Katalog Barang)

- **Registrasi Obat & BHP** dengan validasi:
  - Obat: batch & expired diisi saat inbound (bukan di master data)
  - BHP: **wajib** isi batas minimum stok
- **Stok dinamis**: dihitung real-time dari sum(batch.quantity) — tidak disimpan di dokumen item, jadi selalu akurat.
- **Search bar** untuk cari nama/kategori.
- **Chip visual** kategori (Obat = amber, BHP = sage).
- **Highlight merah** ketika BHP di bawah min_stock.

---

### 📈 D. Kartu Stok (Stock Card)

- Modal per item menampilkan **histori pergerakan** dengan **saldo berjalan** (running balance).
- Kolom: Tanggal, Jenis (inbound/outbound/opname), Batch, Expired, Masuk, Keluar, Selisih (opname), Saldo, Catatan.
- **Ekspor XLSX/PDF** kartu stok per item (dengan kop berlogo).

---

### ⚙️ E. Pergerakan Stok

**Inbound (Barang Masuk):**
- Wajib pilih item, jumlah, tanggal, nomor faktur, sumber.
- Obat → **wajib batch_no + expiry**.
- BHP → batch/expiry opsional.
- Setiap inbound membuat 1 record di `batches` collection.

**Outbound (Barang Keluar) — FEFO Otomatis:**
- Sistem mengurutkan batches by `expiry ASC` (nulls last) → `received_at ASC`.
- Alokasi greedy: batch terlama/expiry-terdekat dipakai dulu.
- Menyimpan `allocations: [{batch_id, batch_no, expiry, quantity}]` di dokumen movement (audit trail).
- Validasi total stok cukup sebelum eksekusi.

**Opname (Penyesuaian Fisik):**
- Modal daftar batch aktif, admin isi jumlah fisik hasil hitung.
- Sistem mencatat `delta = physical - system` di movement dengan kind=`opname`.
- Update batch.quantity otomatis.

**Tabs filter**: Semua / Inbound / Outbound / Opname.

---

### 🩺 F. Manajemen Aset Tetap

- **Auto-generate kode** format `ASET-{KSR|POL}-{SLUG_NAMA}-{seq:03d}` (mis. `ASET-KSR-TANDULIP-001`).
- Field: Nama, Gudang, Tahun perolehan, Sumber dana, Lokasi penempatan, Deskripsi.
- **4 Aksi Status**:
  - **Pinjam** — isi nama peminjam, estimasi kembali, keperluan → status `Digunakan`
  - **Kembalikan** → status `Tersedia`
  - **Rusak** — isi catatan kerusakan → status `Rusak` (tidak dihitung sebagai siap pakai)
  - **Perbaiki** → status `Tersedia`
- **Filter tabs**: Semua / Tersedia / Digunakan / Rusak.
- Setiap aksi tercatat di `asset_logs` (audit trail).

---

### 🕓 G. Timeline Aset (Riwayat)

- Tombol **Riwayat** pada setiap baris aset → modal timeline vertikal.
- Gabungan events dari `asset_logs` + `loans` (peminjaman lintas divisi).
- Setiap event: ikon berwarna (Pinjam/Kembali/Rusak/Perbaiki/Peminjaman lintas divisi), timestamp, peminjam, tujuan, keperluan, actor.
- Diurutkan terbaru di atas.

---

### 🔄 H. Mutasi Antar Gudang

- Transfer BHP/Obat antara **KSR ↔ Poliklinik**.
- **State machine**: `In Transit` → `Received` (atau `Cancelled`).
- Saat dibuat: alokasi FEFO di gudang asal (batches ter-decrement).
- Saat diterima gudang tujuan: sistem otomatis membuat **sibling item** di gudang tujuan (jika belum ada) + batch baru dengan expiry & batch_no yang sama.
- Hanya gudang tujuan (atau Super Admin) yang bisa klik **Terima**.

---

### 🤝 I. Peminjaman Aset Lintas Divisi

- Menu khusus: aset milik KSR bisa dipinjam ke Poliklinik (atau sebaliknya).
- Field: Aset, Divisi tujuan, Nama peminjam/PIC, Est. kembali, Keperluan.
- Otomatis mengubah status aset ke `Digunakan` di source warehouse.
- Tombol **Kembalikan** mengembalikan status aset ke `Tersedia` dan menandai loan `Selesai`.
- Semua event masuk ke Timeline Aset.

---

### 📋 J. Laporan (LPJ / Reporting)

**3 jenis laporan:**
- **Kartu Stok / Pergerakan** — semua movement (inbound/outbound/opname)
- **Aset Aktif** — daftar aset dengan kondisi terkini
- **Rekap Stock Opname** — selisih fisik vs sistem

**Filter:**
- Gudang (Super Admin: pilih KSR/Poliklinik/Semua)
- Rentang tanggal (Dari – Sampai)
- Status (untuk laporan aset)

**Preview** langsung di UI sebelum diunduh.

**Ekspor:**
- **XLSX** (openpyxl) dengan header maroon, kolom auto-width, kop berlogo.
- **PDF** (reportlab Platypus, landscape A4) dengan **kop berlogo** (Table 3 kolom: logo kiri | title tengah | logo kanan) + garis pembatas maroon.

---

### 🎨 K. Pengaturan Logo (Kop Instansi)

- Menu Super Admin: upload **2 logo** (kiri & kanan) format PNG/JPG (max 500 KB).
- Preview langsung sebelum simpan.
- Label singkat editable (default: KSR, PMI).
- Logo tampil di:
  - Login page (pojok kanan atas)
  - Header semua halaman
  - **Kop PDF laporan** (validated via PIL)
  - **Kop XLSX laporan**

---

### 🎯 L. UI/UX Highlights

- **Warna**: Maroon (`#7f1d2d`) + Putih, dengan aksen amber untuk warning dan sage untuk success.
- **Font**: IBM Plex Sans (body) + IBM Plex Mono (labels, eyebrows, kode).
- **Responsif**: sidebar drawer di mobile, grid metrics adaptive.
- **Data-testid** pada semua elemen interaktif untuk stabilitas testing.
- **Toast notifications** untuk feedback aksi (success = maroon, error = merah).
- **Zero-state** dengan pesan panduan di setiap halaman kosong.
- **Loading & disabled states** pada semua tombol async.

---

### 🗂️ M. Struktur Data (MongoDB)

| Collection | Fungsi |
|---|---|
| `users` | Akun + role + warehouse |
| `items` | Master data Obat & BHP |
| `batches` | Setiap batch inbound (FEFO source) |
| `movements` | Log inbound/outbound/opname + allocations |
| `assets` | Aset tetap + status terkini |
| `asset_logs` | Audit trail aksi aset |
| `loans` | Peminjaman lintas divisi |
| `transfers` | Mutasi antar gudang + state machine |
| `settings` | Konfigurasi logo (base64) |

---

### 🔧 N. Endpoint API Kunci

```
POST   /api/auth/login              Login + set cookie
GET    /api/auth/me                 Info user aktif
GET    /api/dashboard               Metrics + expiring windows
GET    /api/dashboard/trend         Konsumsi bulanan 6 bulan
GET    /api/items                   List items + stok dinamis
GET    /api/items/{id}/card         Kartu stok saldo berjalan
POST   /api/movements               Inbound / Outbound (FEFO)
POST   /api/opname                  Adjust batch quantity
POST   /api/assets/{id}/action      Borrow/Return/Damage/Repair
GET    /api/assets/{id}/logs        Timeline aset (logs + loans)
POST   /api/transfers               Buat mutasi (In Transit)
POST   /api/transfers/{id}/receive  Terima di destination
POST   /api/loans                   Peminjaman lintas divisi
GET    /api/reports/{kind}          Preview data laporan
GET    /api/reports/{kind}/export   Unduh XLSX/PDF dengan kop
PUT    /api/settings/logos          Upload logo (Super Admin)
```

---

### 🛡️ O. Keamanan & Audit

- Password di-hash dengan **bcrypt**.
- Cookie **HttpOnly + SameSite=lax**.
- Setiap movement/asset-action mencatat **actor** (nama Super Admin/Admin).
- Validasi scope di setiap endpoint (403 jika lintas gudang tanpa hak).
- Validasi domain: Obat wajib batch+expiry, BHP wajib min_stock, transfer tidak boleh source=destination, dll.

---

## 🔑 Login Akses

- username: `superadminksr`
- Password: `password123`

## 🌐 Environment