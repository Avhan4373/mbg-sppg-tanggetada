# SIPRES MBG — SPPG Kecamatan Tanggetada

Sistem prediksi stok bahan makanan Program Makanan Bergizi Gratis (MBG) untuk
SPPG Kecamatan Tanggetada. Perhitungan memakai **Double Exponential Smoothing
(Holt)** — level + trend tanpa komponen musiman.

Halaman prediksi adalah **halaman utama** (`/`) dan dapat dipakai tanpa login.
Area pengelolaan data berada di `/admin` dan memerlukan login.

## Fitur

- Prediksi kebutuhan bahan makanan mingguan dengan Holt (α dan β dipilih otomatis).
- Pemilihan parameter lewat *grid search* 9.801 kombinasi + validasi *holdout*.
- Metrik akurasi: MAE, RMSE, MAPE, dan kategori skala Lewis.
- Safety stock, reorder point, dan saran pembelian.
- Halaman hasil berisi grafik aktual vs prediksi, penjelasan 10 langkah
  perhitungan dengan angka nyata, dan tabel iterasi lengkap.
- Halaman panduan metode di `/panduan`.
- Pengelolaan bahan, kategori, satuan, supplier, transaksi stok, menu,
  rencana produksi, riwayat prediksi, dan pengguna.
- Tema warna dapat diganti langsung dari header (hijau, biru, oranye, ungu),
  tersimpan di `localStorage`.
- Tanpa Filament dan tanpa build step (CSS/JS disajikan dari `public/`).

## Kebutuhan

- PHP 8.2+ dengan ekstensi umum Laravel
- Composer
- SQLite (default) — tidak perlu server database

## Instalasi

```bash
composer install
cp .env.example .env        # jika .env belum ada
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Buka <http://127.0.0.1:8000>.

Database default memakai SQLite di `database/database.sqlite`. Jika file belum
ada:

```bash
touch database/database.sqlite
```

## Akun demo

| Peran | Email | Password |
|---|---|---|
| Admin SPPG | `admin@mbg.test` | `password` |
| Ahli Gizi | `gizi@mbg.test` | `password` |
| Kepala SPPG | `kepala@mbg.test` | `password` |

Hanya peran `admin` yang dapat membuka menu **Pengguna**.

## Alur pemakaian

1. Buka `/` lalu pilih bahan makanan.
2. Tentukan rentang tanggal data (minimal 12 minggu) dan horizon prediksi.
3. Pilih service level (90%, 95%, atau 99%).
4. Klik **Hitung Prediksi**.
5. Halaman hasil menampilkan ringkasan, grafik, penjelasan tiap langkah, dan
   tabel perhitungan penuh. Halaman ini bisa dicetak.

Data prediksi diambil dari transaksi berjenis **Pemakaian** (`out`) pada menu
**Transaksi Stok**. Transaksi lain (masuk, terbuang, penyesuaian) tidak dipakai
sebagai deret permintaan tetapi tetap memengaruhi stok saat ini.

## Perhitungan singkat

```
Y_t = total pemakaian minggu ke-t

L_1 = Y_1
T_1 = Y_2 − Y_1

F_t = L_{t−1} + T_{t−1}
L_t = α·Y_t + (1 − α)·F_t
T_t = β·(L_t − L_{t−1}) + (1 − β)·T_{t−1}
e_t = Y_t − F_t

F_{n+h} = L_n + h·T_n

SS  = Z · σ_e · √LT
ROP = (rata-rata F_{n+h} × LT) + SS
Saran beli = Σ F_{n+h} + SS − stok saat ini − stok dalam perjalanan
```

Penjelasan lengkap ada di `/panduan` dan pada setiap halaman hasil prediksi.

## Mengganti tema

Semua warna terpusat sebagai CSS variable di `public/css/app.css`.

- Preset tersedia pada blok `[data-theme="hijau"]`, `biru`, `oranye`, `ungu`.
- Untuk membuat tema baru, tambahkan blok `[data-theme="nama"]` dan daftarkan
  opsinya di `resources/views/partials/header.blade.php`.
- Variabel utama: `--brand`, `--brand-2`, `--brand-soft`, `--accent`, `--header`,
  `--bg`, `--surface`, `--text`, `--border`.

Tidak ada kompilasi aset; cukup muat ulang halaman.

## Struktur penting

```
app/Services/HoltForecastService.php   # inti perhitungan Holt + metrik
app/Services/ForecastRunner.php        # agregasi mingguan + simpan hasil
app/Http/Controllers/PredictionController.php
app/Support/NumberFormat.php           # format angka/tanggal Indonesia
resources/views/predictions/           # halaman utama + hasil
resources/views/pages/guide.blade.php  # panduan metode
resources/views/admin/                 # CRUD area pengelolaan
public/css/app.css                     # seluruh tema dan styling
public/js/chart.umd.min.js             # Chart.js lokal
routes/web.php                         # seluruh route
```

## Pengujian

```bash
php artisan test --compact
```

Cakupan: alur prediksi publik, validasi minimal periode, panduan, autentikasi,
hak akses admin, dan render seluruh halaman CRUD.

## Catatan

- Zona waktu aplikasi: `Asia/Makassar`; locale: `id`.
- Holt tidak memodelkan musiman. Bila pola musiman kuat dan data mencukupi,
  pertimbangkan Holt-Winters sebagai pengembangan lanjutan.
- Hasil prediksi adalah alat bantu keputusan, bukan pengganti pertimbangan ahli
  gizi dan kepala SPPG.
