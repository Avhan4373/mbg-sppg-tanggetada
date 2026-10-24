# Spesifikasi Sistem

## Sistem Prediksi Persediaan Stok Bahan Makanan pada Program Makanan Bergizi Gratis (MBG) Menggunakan Metode Double Exponential Smoothing (Holt)

**Studi kasus:** SPPG Kecamatan Tanggetada  
**Versi:** 0.1.0 (Draft)  
**Tanggal:** 23 Oktober 2026  
**Status:** Menunggu konfirmasi stack dan ketersediaan data

---

## 1. Ringkasan

Sistem ini digunakan untuk memprediksi kebutuhan bahan makanan pada Satuan Pelayanan Pemenuhan Gizi (SPPG) Kecamatan Tanggetada, Program Makanan Bergizi Gratis (MBG). Prediksi dihitung dari data historis pemakaian bahan makanan menggunakan metode **Double Exponential Smoothing (Holt)**, yang memodelkan level dan tren data.

Hasil prediksi digunakan untuk:

1. memperkirakan kebutuhan bahan makanan pada periode berikutnya;
2. menghitung safety stock dan reorder point;
3. memberikan rekomendasi jumlah pembelian;
4. mengurangi risiko kekurangan maupun kelebihan stok.

Sistem ini mengadaptasi alur dan fitur dari jurnal “Sistem Informasi Prediksi Stok Barang Menggunakan Metode Holt Winters pada CV Budi Jaya Mandiri” (Mariz, Sobri, & Putra, 2017), tetapi **mengganti metode Holt-Winters musiman menjadi Holt (level + trend)** sesuai judul penelitian baru.

---

## 2. Latar Belakang

Persediaan bahan makanan di dapur SPPG memiliki karakteristik:

- jumlah pemakaian bergantung pada jumlah porsi yang diproduksi;
- menu dapat berulang dalam siklus tertentu;
- bahan mudah rusak dan memiliki masa simpan terbatas;
- keterlambatan pasokan dapat mengganggu produksi makanan;
- kelebihan stok menimbulkan pemborosan biaya dan risiko bahan kedaluwarsa.

Pencatatan stok dan pemakaian yang baik memungkinkan sistem menghitung pola kebutuhan secara objektif. Dengan metode Holt, sistem dapat menangkap arah tren pemakaian dan menghasilkan prediksi kuantitatif sebagai dasar perencanaan pembelian.

---

## 3. Tujuan

### 3.1 Tujuan umum

Membangun sistem informasi berbasis web untuk memprediksi persediaan bahan makanan pada SPPG Kecamatan Tanggetada.

### 3.2 Tujuan khusus

1. Mencatat data bahan makanan, stok masuk, stok keluar, dan pemakaian aktual.
2. Menghitung prediksi kebutuhan bahan makanan menggunakan metode Holt.
3. Menentukan parameter α dan β terbaik berdasarkan data historis.
4. Mengukur akurasi prediksi menggunakan MAPE, MAE, dan RMSE.
5. Menghitung safety stock, reorder point, dan rekomendasi pembelian.
6. Menyajikan hasil prediksi dalam tabel dan grafik.
7. Menyediakan laporan yang dapat diekspor.

---

## 4. Ruang Lingkup

### 4.1 Termasuk dalam ruang lingkup

- Satu lokasi SPPG, yaitu Kecamatan Tanggetada.
- Banyak jenis bahan makanan dalam satu database.
- Pencatatan transaksi stok masuk, stok keluar, penyesuaian, dan bahan terbuang.
- Data menu dan rencana produksi porsi.
- Prediksi kebutuhan per bahan makanan.
- Agregasi data mingguan.
- Horizon prediksi 1–4 minggu.
- Evaluasi akurasi model.
- Rekomendasi pembelian.
- Manajemen pengguna dan hak akses.
- Laporan dalam bentuk tabel, grafik, PDF, dan Excel.

### 4.2 Di luar ruang lingkup

- Integrasi otomatis dengan sistem pengadaan atau e-katalog.
- Pembayaran dan akuntansi keuangan.
- Prediksi harga pasar.
- Optimasi menu atau kandungan gizi otomatis.
- Multi-SPPG dalam satu instance.
- Integrasi timbangan digital atau IoT.
- Aplikasi mobile native.
- Prediksi musiman lanjutan (Holt-Winters) pada versi awal.

---

## 5. Pengguna dan Hak Akses

| Peran | Tanggung jawab | Hak akses utama |
|---|---|---|
| Admin SPPG | Mengelola data operasional dan menjalankan prediksi | Master data, transaksi stok, menu, produksi, prediksi, laporan |
| Ahli Gizi / Perencana Menu | Menyusun menu dan rencana produksi | Melihat stok, mengelola menu, mengelola rencana produksi, melihat prediksi |
| Kepala SPPG | Memantau kondisi persediaan dan mengambil keputusan | Dashboard, melihat prediksi, menyetujui rekomendasi pembelian, laporan |

Aturan:

- Setiap pengguna wajib login.
- Hak akses diperiksa di server, bukan hanya disembunyikan di antarmuka.
- Perubahan data penting dicatat dalam audit log.

---

## 6. Definisi dan Istilah

| Istilah | Definisi |
|---|---|
| Bahan makanan | Item yang digunakan untuk produksi makanan, misalnya beras, telur, ayam, sayur, minyak |
| Pemakaian aktual | Jumlah bahan yang benar-benar digunakan untuk produksi |
| Stok saat ini | Stok awal + stok masuk − stok keluar − bahan terbuang ± penyesuaian |
| Level (L) | Nilai dasar pemulusan pada periode ke-t |
| Trend (T) | Estimasi perubahan level antarperiode |
| Alpha (α) | Parameter pemulusan level, 0 < α < 1 |
| Beta (β) | Parameter pemulusan trend, 0 < β < 1 |
| Horizon | Jumlah periode ke depan yang diprediksi |
| Safety stock | Stok cadangan untuk mengantisipasi ketidakpastian kebutuhan |
| Reorder point | Titik stok minimum yang memicu pemesanan ulang |
| MAPE | Mean Absolute Percentage Error |
| MAE | Mean Absolute Error |
| RMSE | Root Mean Squared Error |

---

## 7. Metode Prediksi

### 7.1 Ketentuan data

1. Data utama adalah **pemakaian aktual** bahan makanan, bukan jumlah pembelian.
2. Data diagregasi per minggu. Minggu dimulai hari Senin dan diakhiri hari Minggu.
3. Minimal data untuk menjalankan prediksi adalah **12 periode mingguan**.
4. Data yang disarankan adalah **24–52 periode mingguan**.
5. Periode tanpa produksi dicatat sebagai pemakaian 0 jika memang tidak ada produksi.
6. Periode yang datanya tidak tercatat ditandai sebagai data hilang dan tidak boleh diperlakukan sebagai 0 tanpa konfirmasi.
7. Outlier dapat ditandai, tetapi tidak dihapus otomatis tanpa jejak audit.
8. Semua jumlah disimpan dalam satuan dasar. Konversi satuan dilakukan saat input dan tampilan.

### 7.2 Rumus Holt

Level:

```text
L_t = α × Y_t + (1 − α) × (L_(t−1) + T_(t−1))
```

Trend:

```text
T_t = β × (L_t − L_(t−1)) + (1 − β) × T_(t−1)
```

Prediksi:

```text
F_(t+h) = L_t + h × T_t
```

Keterangan:

- `Y_t` = pemakaian aktual periode t
- `L_t` = level periode t
- `T_t` = trend periode t
- `F_(t+h)` = prediksi h periode ke depan
- `α` = parameter level
- `β` = parameter trend

### 7.3 Inisialisasi

Untuk periode pertama:

```text
L_1 = Y_1
T_1 = Y_2 − Y_1
```

Jika jumlah data mencukupi, slope awal dapat dihitung dari regresi linear periode awal untuk mengurangi sensitivitas terhadap satu nilai awal. Metode inisialisasi yang dipakai harus konsisten dan dicatat pada hasil prediksi.

### 7.4 Pencarian parameter

1. Rentang α dan β: 0,01 sampai 0,99.
2. Langkah pencarian: 0,01.
3. Data dibagi menjadi:
   - data latih: 80% periode paling awal;
   - data uji: 20% periode paling akhir, minimal 4 periode.
4. Setiap kombinasi α dan β diuji pada data latih, lalu dihitung error pada data uji.
5. Kombinasi terbaik dipilih berdasarkan error terkecil.
6. Setelah parameter terbaik diperoleh, model dilatih ulang menggunakan seluruh data untuk menghasilkan prediksi masa depan.
7. Parameter yang dipakai, nilai error, dan tanggal proses disimpan pada riwayat prediksi.

### 7.5 Evaluasi akurasi

```text
Error_t = Y_t − F_t
MAE     = rata-rata |Error_t|
RMSE    = akar dari rata-rata (Error_t)^2
MAPE    = rata-rata (|Error_t| / Y_t) × 100%
```

Aturan:

- MAPE hanya dihitung untuk periode dengan `Y_t > 0`.
- Jika data mengandung banyak nilai nol, RMSE dan MAE menjadi metrik utama.
- Nilai evaluasi ditampilkan bersama jumlah periode yang dievaluasi.
- Sistem tidak boleh mengklaim “akurasi 80%” hanya dari satu nilai error tanpa dasar perhitungan yang benar.

### 7.6 Rekomendasi persediaan

Safety stock:

```text
Safety stock = z × standar deviasi residual × akar(lead time dalam periode)
```

Nilai `z` default untuk service level 95% adalah 1,65. Jika residual belum tersedia, gunakan cadangan default 20% dari prediksi kebutuhan dan tandai sebagai estimasi awal.

Reorder point:

```text
ROP = prediksi kebutuhan selama lead time + safety stock
```

Rekomendasi pembelian:

```text
Saran pembelian = maksimum(
  0,
  prediksi kebutuhan horizon
  + safety stock
  − stok saat ini
  − stok dalam perjalanan
)
```

Aturan tambahan:

- Hasil prediksi negatif dibulatkan menjadi 0.
- Jumlah pembelian dibulatkan ke atas sesuai satuan pembelian.
- Bahan dengan masa simpan pendek harus mempertimbangkan batas maksimum stok.
- Rekomendasi pembelian bersifat saran, bukan pesanan otomatis.

### 7.7 Penanganan kondisi khusus

| Kondisi | Perlakuan |
|---|---|
| Data kurang dari 12 periode | Prediksi ditolak dengan pesan yang jelas |
| Data aktual nol | Tetap dipakai; MAPE tidak dihitung untuk periode tersebut |
| Prediksi negatif | Diubah menjadi 0 dan diberi catatan |
| Satuan berbeda | Dikonversi ke satuan dasar sebelum perhitungan |
| Data hilang | Ditandai; tidak otomatis dianggap nol |
| Lonjakan ekstrem | Ditandai sebagai outlier; keputusan tetap oleh pengguna |
| Perubahan menu besar | Dicatat sebagai catatan pada periode terkait |

---

## 8. Kebutuhan Fungsional

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-01 | Login, logout, dan pembatasan akses berdasarkan peran | Wajib |
| FR-02 | Dashboard ringkasan stok, bahan kritis, dan prediksi terbaru | Wajib |
| FR-03 | CRUD bahan makanan | Wajib |
| FR-04 | CRUD kategori, satuan, dan supplier | Wajib |
| FR-05 | Pencatatan stok masuk, stok keluar, penyesuaian, dan bahan terbuang | Wajib |
| FR-06 | Melihat stok saat ini dan kartu stok per bahan | Wajib |
| FR-07 | CRUD menu beserta komposisi bahan per porsi | Wajib |
| FR-08 | Pencatatan rencana produksi: tanggal, menu, dan jumlah porsi | Wajib |
| FR-09 | Riwayat pemakaian bahan dengan filter tanggal dan bahan | Wajib |
| FR-10 | Form prediksi: pilih bahan, rentang data, horizon, dan mode parameter | Wajib |
| FR-11 | Perhitungan Holt otomatis dan pencarian α/β terbaik | Wajib |
| FR-12 | Menampilkan tabel perhitungan L, T, F, error, dan periode prediksi | Wajib |
| FR-13 | Grafik aktual, fitted, dan forecast | Wajib |
| FR-14 | Menampilkan MAPE, MAE, dan RMSE | Wajib |
| FR-15 | Menghitung safety stock, ROP, dan saran pembelian | Wajib |
| FR-16 | Menyimpan riwayat proses prediksi | Wajib |
| FR-17 | Ekspor laporan PDF dan Excel | Wajib |
| FR-18 | Audit log untuk perubahan data penting | Sebaiknya |
| FR-19 | Pengaturan service level, data minimum, dan horizon default | Sebaiknya |
| FR-20 | Notifikasi stok menipis atau kritis | Sebaiknya |
| FR-21 | Data demo atau seed untuk pengujian | Sebaiknya |
| FR-22 | Perbandingan beberapa bahan dalam satu dashboard | opsional |

---

## 9. Alur Utama Prediksi

1. Pengguna membuka menu **Prediksi**.
2. Pengguna memilih bahan makanan.
3. Pengguna menentukan rentang data historis.
4. Pengguna menentukan horizon prediksi, misalnya 4 minggu.
5. Sistem memvalidasi jumlah dan kelengkapan data.
6. Sistem membagi data latih dan data uji.
7. Sistem mencari kombinasi α dan β terbaik.
8. Sistem menghitung level, trend, fitted value, dan forecast.
9. Sistem menghitung MAPE, MAE, dan RMSE.
10. Sistem menghitung safety stock, ROP, dan saran pembelian.
11. Sistem menyimpan hasil sebagai satu `forecast_run`.
12. Pengguna melihat tabel, grafik, dan rekomendasi.
13. Pengguna dapat mengekspor laporan atau membandingkan dengan hasil sebelumnya.

---

## 10. Model Data

### 10.1 Daftar tabel

| Tabel | Fungsi |
|---|---|
| `users` | Akun dan peran pengguna |
| `categories` | Kategori bahan makanan |
| `units` | Satuan dasar dan konversi |
| `suppliers` | Data pemasok |
| `ingredients` | Master bahan makanan |
| `stock_transactions` | Semua transaksi stok |
| `menus` | Master menu |
| `menu_ingredients` | Komposisi bahan per menu |
| `production_plans` | Rencana produksi dan jumlah porsi |
| `forecast_runs` | Header proses prediksi dan parameter |
| `forecast_values` | Detail nilai L, T, F, dan error per periode |
| `settings` | Konfigurasi sistem |
| `audit_logs` | Jejak perubahan data |

### 10.2 Field utama

#### `ingredients`

- `id`
- `code` (unik)
- `name`
- `category_id`
- `unit_id`
- `supplier_id` (opsional)
- `minimum_stock`
- `lead_time_days`
- `shelf_life_days`
- `purchase_price`
- `is_active`
- `created_at`, `updated_at`

#### `stock_transactions`

- `id`
- `ingredient_id`
- `type`: `in`, `out`, `adjustment`, `waste`
- `quantity`
- `transaction_date`
- `batch_number` (opsional)
- `expiry_date` (opsional)
- `note`
- `created_by`
- `created_at`

#### `menus`

- `id`
- `name`
- `description`
- `is_active`

#### `menu_ingredients`

- `menu_id`
- `ingredient_id`
- `quantity_per_portion`
- `unit_id`

#### `production_plans`

- `id`
- `production_date`
- `menu_id`
- `portions`
- `note`
- `created_by`

#### `forecast_runs`

- `id`
- `ingredient_id`
- `method` (`holt`)
- `period_type` (`weekly`)
- `start_date`
- `end_date`
- `horizon`
- `alpha`
- `beta`
- `mape`
- `mae`
- `rmse`
- `service_level`
- `status`
- `created_by`
- `created_at`

#### `forecast_values`

- `id`
- `forecast_run_id`
- `period_date`
- `actual`
- `level`
- `trend`
- `forecast`
- `error`
- `is_future`

### 10.3 Aturan data

- Stok saat ini dihitung dari transaksi dan dapat di-cache pada tabel ringkasan.
- Satu bahan hanya memiliki satu satuan dasar.
- Konversi satuan disimpan eksplisit, bukan diasumsikan.
- Penghapusan master data yang sudah memiliki transaksi dilakukan dengan menonaktifkan data, bukan menghapus permanen.
- Semua nominal uang menggunakan Rupiah dan tipe desimal yang sesuai.
- Semua waktu menggunakan zona waktu `Asia/Makassar` (WITA) secara default.

---

## 11. Rancangan Antarmuka

### 11.1 Halaman

1. Login.
2. Dashboard.
3. Bahan Makanan.
4. Kategori dan Satuan.
5. Supplier.
6. Transaksi Stok.
7. Kartu Stok.
8. Menu.
9. Rencana Produksi.
10. Riwayat Pemakaian.
11. Prediksi.
12. Hasil Prediksi.
13. Evaluasi Akurasi.
14. Laporan.
15. Pengguna dan Hak Akses.
16. Pengaturan.

### 11.2 Dashboard

Menampilkan:

- total bahan aktif;
- jumlah bahan berstatus kritis;
- total nilai persediaan;
- prediksi kebutuhan terbaru;
- grafik pemakaian 8 minggu terakhir;
- daftar rekomendasi pembelian teratas.

### 11.3 Halaman hasil prediksi

Menampilkan:

- identitas bahan dan periode;
- parameter α dan β terpilih;
- tabel periode aktual, level, trend, forecast, dan error;
- grafik garis aktual vs fitted vs forecast;
- kartu MAPE, MAE, dan RMSE;
- safety stock, ROP, dan saran pembelian;
- tombol ekspor PDF/Excel;
- catatan metode dan peringatan kualitas data.

### 11.4 Status stok

| Status | Kondisi |
|---|---|
| Aman | Stok di atas reorder point |
| Menipis | Stok sama dengan atau di bawah reorder point |
| Kritis | Stok sama dengan atau di bawah minimum stock |
| Habis | Stok sama dengan 0 |

---

## 12. Kebutuhan Nonfungsional

| Aspek | Ketentuan |
|---|---|
| Platform | Aplikasi web responsif |
| Bahasa | Bahasa Indonesia |
| Perhitungan | Deterministik, hasil dapat direproduksi |
| Presisi | Simpan minimal 3 angka desimal, tampilkan 2 angka desimal |
| Performa | Prediksi satu bahan maksimal 2 detik untuk 52 periode |
| Keamanan | Password hash, CSRF, validasi server, otorisasi per peran |
| Audit | Perubahan data penting tercatat |
| Backup | Backup database harian |
| Kompatibilitas | Chrome, Edge, dan Firefox versi terbaru |
| Akses | Desktop dan tablet |
| Zona waktu | `Asia/Makassar` |
| Mata uang | IDR |

---

## 13. Arsitektur dan Stack

### 13.1 Stack terpilih

- **Backend:** Laravel 12
- **Database:** SQLite (mudah dipindah ke MySQL/PostgreSQL)
- **Frontend:** Blade + CSS kustom (tanpa framework UI, tanpa build step)
- **Grafik:** Chart.js (aset lokal di `public/js/`)
- **Testing:** PHPUnit

Panel admin **tidak** memakai Filament. Seluruh tampilan memakai Blade
dengan CSS terpusat agar tema mudah diganti cukup lewat CSS variable.

### 13.2 Struktur komponen

- `HoltForecastService` — perhitungan level, trend, forecast, dan evaluasi.
- `ForecastRunner` — agregasi pemakaian mingguan dan penyimpanan hasil.
- `PredictionController` — formulir publik dan halaman penjelasan hasil.
- Controller `Admin\*` — CRUD master data, transaksi, dan riwayat prediksi.
- `NumberFormat` — format angka, mata uang, dan tanggal Indonesia.
- Halaman prediksi publik di `/` — dapat diakses tanpa login dan menjadi
  halaman utama situs.
- Halaman panduan metode di `/panduan`.

Perhitungan metode Holt berada di kelas service tersendiri dan diuji dengan
unit/feature test, tidak ditulis langsung di controller atau halaman tampilan.

### 13.3 Catatan

Spesifikasi fungsional dan metode tetap berlaku bila stack teknis disesuaikan.

---

## 14. Kriteria Penerimaan

1. Pengguna dapat login dan hanya melihat menu sesuai perannya.
2. Admin dapat menambah bahan, transaksi stok, menu, dan rencana produksi.
3. Sistem menolak prediksi jika data kurang dari 12 periode.
4. Sistem menghasilkan nilai L, T, dan F yang sama dengan perhitungan manual/spreadsheet pada toleransi 0,01.
5. Sistem memilih α dan β berdasarkan data uji dan menampilkan nilainya.
6. Sistem menampilkan MAPE, MAE, dan RMSE beserta jumlah periode evaluasi.
7. Forecast tidak pernah bernilai negatif.
8. Sistem menghitung safety stock, ROP, dan saran pembelian.
9. Hasil prediksi tersimpan dan dapat dibuka kembali.
10. Grafik menampilkan data aktual, fitted, dan forecast.
11. Laporan dapat diekspor ke PDF dan Excel.
12. Data master yang sudah dipakai tidak dapat dihapus permanen.
13. Perhitungan dapat diuji ulang dengan data dan parameter yang sama.
14. Tidak ada error kritis pada pengujian alur utama.

---

## 15. Asumsi dan Risiko

### 15.1 Asumsi

- Data pemakaian historis tersedia minimal 12 minggu.
- Satu SPPG memiliki satu dapur produksi.
- Satuan bahan dapat dikonversi ke satuan dasar.
- Menu dan jumlah porsi dicatat setiap hari produksi.
- Pengguna memiliki akses browser modern.

### 15.2 Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Pencatatan pemakaian tidak rutin | Prediksi bias | Validasi data hilang dan pengingat input |
| Pola menu musiman | Holt tidak menangkap musiman | Evaluasi residual; pertimbangkan Holt-Winters pada versi lanjutan |
| Satuan tidak konsisten | Perhitungan salah | Satuan dasar dan konversi eksplisit |
| Banyak minggu dengan nilai nol | MAPE tidak valid | Gunakan MAE/RMSE sebagai metrik utama |
| Lead time supplier berubah | ROP kurang tepat | Perbarui lead time dan gunakan safety stock |
| Bahan mudah rusak | Kelebihan stok | Batas maksimum stok dan peringatan kedaluwarsa |
| Data historis pendek | Model tidak stabil | Minimal 12 periode dan tampilkan peringatan |

---

## 16. Rencana Tahapan

1. Finalisasi spesifikasi dan konfirmasi stack.
2. Pembuatan struktur proyek dan database.
3. Implementasi master data dan transaksi stok.
4. Implementasi menu dan rencana produksi.
5. Implementasi service Holt dan unit test.
6. Implementasi halaman prediksi dan hasil.
7. Implementasi evaluasi akurasi dan rekomendasi pembelian.
8. Implementasi laporan dan ekspor.
9. Pengujian dengan data dummy.
10. Pengujian dengan data SPPG Tanggetada.
11. Perbaikan dan dokumentasi penggunaan.

---

## 17. Pertanyaan Terbuka

1. ~~Stack final: Laravel + Filament, atau stack lain?~~ **Terjawab:** Laravel 12 + Blade + CSS kustom + SQLite, tanpa Filament.
2. Apakah data pemakaian bahan sudah tersedia? Jika belum, apakah boleh memakai data dummy?
3. Periode prediksi yang diinginkan: mingguan, harian, atau bulanan?
4. Berapa jumlah porsi dan siklus menu yang biasa dijalankan?
5. Apakah sistem hanya untuk satu SPPG atau perlu multi-SPPG?
6. Apakah perlu fitur persetujuan pembelian oleh Kepala SPPG?
7. Apakah perlu prediksi musiman (Holt-Winters) sejak versi pertama?

---

## 18. Referensi

- Mariz, E. P., Sobri, M., & Putra, A. (2017). Sistem Informasi Prediksi Stok Barang Menggunakan Metode Holt Winters pada CV Budi Jaya Mandiri. *Jurnal Ilmiah Fak. Ilkom*, 1(1), 1–11.
- Makridakis, S., Wheelwright, S. C., & Hyndman, R. J. (1998). *Forecasting: Methods and Applications*.
- Hyndman, R. J., & Athanasopoulos, G. (2021). *Forecasting: Principles and Practice* (3rd ed.).
- Chopra, S., & Meindl, P. (2019). *Supply Chain Management: Strategy, Planning, and Operation*.

---

## 19. Catatan Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 0.1.0 | 23 Oktober 2026 | Draft awal berdasarkan analisis jurnal dan kebutuhan sistem MBG |
