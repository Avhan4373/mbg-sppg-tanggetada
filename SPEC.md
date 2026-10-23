# SPEC.md — Sistem Prediksi Stok Bahan Makanan MBG (Metode Holt)

## Project Overview
- **Aplikasi**: Sistem Prediksi Persediaan Stok Bahan Makanan Pada Program Makanan Bergizi Gratis (MBG)
- **Studi Kasus**: SPPG Kecamatan Tanggetada
- **Metode**: Double Exponential Smoothing (Holt) — 2 Parameter (Alpha, Beta)
- **Stack**: Node.js / Express, SQLite (Prisma ORM), Vite + React, Tailwind CSS (Dark Theme), Lucide Icons, Recharts.

---

## Architecture & Data Model

### Database Schema (Prisma / SQLite)
```prisma
model User {
  id        Int      @id @default(autoincrement())
  username  String   @unique
  password  String
  nama      String
  role      String   // 'ADMIN' | 'KEPALA_SPPG'
  createdAt DateTime @default(now())
}

model BahanMakanan {
  id           Int      @id @default(autoincrement())
  kodeBahan    String   @unique
  namaBahan    String
  kategori     String   // 'Karbohidrat', 'Protein', 'Sayuran', 'Bumbu', 'Lainnya'
  satuan       String   // 'kg', 'liter', 'rak', 'porsi'
  stokMinimal  Float
  stokSaatIni  Float    @default(0)
  hargaSatuan  Float    @default(0)
  createdAt    DateTime @default(now())

  bahanMasuk      BahanMasuk[]
  penggunaanStok  PenggunaanStok[]
  riwayatPrediksi Prediksi[]
}

model BahanMasuk {
  id           Int          @id @default(autoincrement())
  tglMasuk     DateTime
  bahanId      Int
  bahan        BahanMakanan @relation(fields: [bahanId], references: [id])
  jumlah       Float
  supplier     String?
  totalBiaya   Float        @default(0)
  createdAt    DateTime     @default(now())
}

model PenggunaanStok {
  id               Int          @id @default(autoincrement())
  tglPenggunaan    DateTime
  bahanId          Int
  bahan            BahanMakanan @relation(fields: [bahanId], references: [id])
  jumlah           Float
  keteranganMenu   String?      // Contoh: 'Menu Nasi Ayam Sayur SPPG Tanggetada'
  createdAt        DateTime     @default(now())
}

model Prediksi {
  id            Int          @id @default(autoincrement())
  bahanId       Int
  bahan         BahanMakanan @relation(fields: [bahanId], references: [id])
  alpha         Float
  beta          Float
  periodeAwal   String       // '2025-01'
  periodeAkhir  String       // '2025-12'
  hasilForecast Float
  mape          Float
  mpe           Float
  detailJson    String       // Array JSON
  createdAt     DateTime     @default(now())
}
```

---

## Rumus Holt (Double Exponential Smoothing)
1. Level: Lt = Alpha * Yt + (1 - Alpha) * (Lt-1 + Tt-1)
2. Trend: Tt = Beta * (Lt - Lt-1) + (1 - Beta) * Tt-1
3. Forecast: Ft+p = Lt + (p * Tt)
4. MAPE: Mean Absolute Percentage Error
