import { DatabaseSync } from 'node:sqlite';
import { randomUUID } from 'node:crypto';

export const db = new DatabaseSync(process.env.DB_PATH || './data.db');

db.exec(`
CREATE TABLE IF NOT EXISTS bahan (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  kode TEXT UNIQUE NOT NULL,
  nama TEXT NOT NULL,
  kategori TEXT NOT NULL,
  satuan TEXT NOT NULL,
  stok_minimal REAL DEFAULT 0,
  stok_saat_ini REAL DEFAULT 0,
  harga_satuan REAL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS transaksi (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tipe TEXT NOT NULL CHECK (tipe IN ('MASUK','PAKAI')),
  tanggal TEXT NOT NULL,
  bahan_id INTEGER NOT NULL REFERENCES bahan(id),
  jumlah REAL NOT NULL,
  keterangan TEXT,
  harga_total REAL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS prediksi (
  id TEXT PRIMARY KEY,
  bahan_id INTEGER NOT NULL REFERENCES bahan(id),
  alpha REAL NOT NULL,
  beta REAL NOT NULL,
  mape REAL NOT NULL,
  mpe REAL NOT NULL,
  forecast_next REAL NOT NULL,
  periode TEXT NOT NULL,
  detail_json TEXT NOT NULL,
  created_at TEXT DEFAULT (datetime('now'))
);
`);

export function syncStok() {
  db.exec(`UPDATE bahan SET stok_saat_ini = COALESCE((
    SELECT SUM(CASE WHEN tipe='MASUK' THEN jumlah ELSE -jumlah END)
    FROM transaksi WHERE transaksi.bahan_id = bahan.id), 0)`);
}

// ---- Seed: bahan MBG + data penggunaan mingguan 30 minggu (SPPG Tanggetada) ----
function seed() {
  const c = db.prepare('SELECT COUNT(*) n FROM bahan').get().n;
  if (c > 0) return;
  const bahans = [
    ['BR-01', 'Beras', 'Karbohidrat', 'kg', 200, 14000],
    ['TL-02', 'Telur', 'Protein', 'kg', 80, 28000],
    ['AY-03', 'Ayam Segar', 'Protein', 'kg', 60, 36000],
    ['DS-04', 'Daging Sapi', 'Protein', 'kg', 30, 120000],
    ['BY-05', 'Sayur Bayam', 'Sayuran', 'kg', 25, 6000],
    ['WR-06', 'Wortel', 'Sayuran', 'kg', 25, 8000],
    ['TM-07', 'Tempe', 'Protein', 'kg', 40, 9000],
    ['MG-08', 'Minyak Goreng', 'Bumbu', 'liter', 30, 17000],
    ['SR-09', 'Susu UHT', 'Protein', 'liter', 100, 15000],
    ['BW-10', 'Bawang & Bumbu', 'Bumbu', 'kg', 20, 25000],
  ];
  const insB = db.prepare('INSERT INTO bahan (kode,nama,kategori,satuan,stok_minimal,harga_satuan) VALUES (?,?,?,?,?,?)');
  const insT = db.prepare('INSERT INTO transaksi (tipe,tanggal,bahan_id,jumlah,keterangan,harga_total) VALUES (?,?,?,?,?,?)');
  const mulai = new Date('2026-03-30'); // minggu mulai
  bahans.forEach((b, i) => {
    const { lastInsertRowid: id } = insB.run(...b);
    // data penggunaan mingguan 30 minggu: dasar + tren naik + noise deterministik
    let dasar = [280, 110, 85, 42, 38, 36, 55, 24, 150, 16][i];
    for (let m = 0; m < 30; m++) {
      const noise = Math.sin(m * (i + 1) * 0.7) * dasar * 0.10;
      const tren = dasar * 0.012 * m; // tren naik ~1.2%/minggu
      const jumlah = Math.max(1, Math.round((dasar + tren + noise) * 10) / 10);
      const tgl = new Date(mulai.getTime() + m * 7 * 864e5).toISOString().slice(0, 10);
      insT.run('PAKAI', tgl, id, jumlah, `Penggunaan minggu ke-${m + 1} - Menu MBG SPPG Tanggetada`, 0);
      if (m % 4 === 0) { // restock tiap 4 minggu
        const masuk = Math.round(dasar * 5.5);
        insT.run('MASUK', tgl, id, masuk, 'Pengadaan supplier', masuk * b[5]);
      }
    }
  });
  syncStok();
}
seed();
