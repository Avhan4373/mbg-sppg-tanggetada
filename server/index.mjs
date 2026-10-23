import express from 'express';
import cors from 'cors';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { db, syncStok } from './db.mjs';
import { holt, optimize } from './holt.mjs';

const app = express();
app.use(cors());
app.use(express.json());

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// ---------- Bahan ----------
app.get('/api/bahan', (_req, res) => {
  res.json(db.prepare('SELECT * FROM bahan ORDER BY kategori, nama').all());
});
app.post('/api/bahan', (req, res) => {
  const { kode, nama, kategori, satuan, stok_minimal, harga_satuan } = req.body;
  const r = db.prepare('INSERT INTO bahan (kode,nama,kategori,satuan,stok_minimal,harga_satuan) VALUES (?,?,?,?,?,?)')
    .run(kode, nama, kategori, satuan, stok_minimal, harga_satuan);
  res.json({ id: r.lastInsertRowid });
});
app.put('/api/bahan/:id', (req, res) => {
  const { nama, kategori, satuan, stok_minimal, harga_satuan } = req.body;
  db.prepare('UPDATE bahan SET nama=?, kategori=?, satuan=?, stok_minimal=?, harga_satuan=? WHERE id=?')
    .run(nama, kategori, satuan, stok_minimal, harga_satuan, req.params.id);
  res.json({ ok: true });
});
app.delete('/api/bahan/:id', (req, res) => {
  db.prepare('DELETE FROM transaksi WHERE bahan_id=?').run(req.params.id);
  db.prepare('DELETE FROM bahan WHERE id=?').run(req.params.id);
  res.json({ ok: true });
});

// ---------- Transaksi ----------
app.get('/api/transaksi', (req, res) => {
  const { bahan_id, tipe } = req.query;
  let q = `SELECT t.*, b.nama AS nama_bahan, b.satuan FROM transaksi t JOIN bahan b ON b.id=t.bahan_id WHERE 1=1`;
  const args = [];
  if (bahan_id) { q += ' AND t.bahan_id=?'; args.push(bahan_id); }
  if (tipe) { q += ' AND t.tipe=?'; args.push(tipe); }
  q += ' ORDER BY t.tanggal DESC LIMIT 200';
  res.json(db.prepare(q).all(...args));
});
app.post('/api/transaksi', (req, res) => {
  const { tipe, tanggal, bahan_id, jumlah, keterangan, harga_total } = req.body;
  db.prepare('INSERT INTO transaksi (tipe,tanggal,bahan_id,jumlah,keterangan,harga_total) VALUES (?,?,?,?,?,?)')
    .run(tipe, tanggal, bahan_id, jumlah, keterangan || null, harga_total || 0);
  syncStok();
  res.json({ ok: true });
});

// ---------- Data historis agregat mingguan (untuk Holt) ----------
function historis(bahanId) {
  return db.prepare(`SELECT tanggal AS periode, SUM(jumlah) AS yt
    FROM transaksi WHERE tipe='PAKAI' AND bahan_id=?
    GROUP BY tanggal ORDER BY tanggal`).all(bahanId);
}

// ---------- Prediksi Holt ----------
app.get('/api/historis/:bahanId', (req, res) => {
  res.json(historis(+req.params.bahanId));
});
app.post('/api/prediksi', (req, res) => {
  const { bahanId, alpha, beta } = req.body;
  const h = historis(bahanId);
  if (h.length < 2) return res.status(400).json({ error: 'Data historis minimal 2 periode' });
  const r = holt(h.map(x => x.yt), alpha, beta);
  const periodeTerakhir = h[h.length - 1].periode;
  const next = new Date(periodeTerakhir); next.setDate(next.getDate() + 7);
  const detail = h.map((x, i) => ({
    t: i + 1, periode: x.periode, yt: x.yt,
    lt: i === 0 ? r.L[0] : r.L[i],
    tt: i === 0 ? r.T[0] : r.T[i],
    ft: r.F[i],
    pe: r.F[i] == null ? null : ((x.yt - r.F[i]) / x.yt) * 100,
  }));
  const id = crypto.randomUUID();
  const forecastNext = Math.round(r.forecastNext * 10) / 10;
  db.prepare(`INSERT INTO prediksi (id,bahan_id,alpha,beta,mape,mpe,forecast_next,periode,detail_json)
    VALUES (?,?,?,?,?,?,?,?,?)`)
    .run(id, bahanId, alpha, beta, +r.mape.toFixed(3), +r.mpe.toFixed(3), forecastNext,
      next.toISOString().slice(0, 10), JSON.stringify(detail));
  res.json({ id, mape: r.mape, mpe: r.mpe, forecastNext, detail });
});
app.get('/api/prediksi/optimize/:bahanId', (req, res) => {
  const h = historis(+req.params.bahanId);
  if (h.length < 2) return res.status(400).json({ error: 'Data historis minimal 2 periode' });
  res.json(optimize(h.map(x => x.yt)));
});
app.get('/api/prediksi', (req, res) => {
  const q = `SELECT p.*, b.nama AS nama_bahan, b.satuan FROM prediksi p JOIN bahan b ON b.id=p.bahan_id
    ${req.query.bahanId ? 'WHERE p.bahan_id=?' : ''} ORDER BY p.created_at DESC LIMIT 50`;
  const args = req.query.bahanId ? [req.query.bahanId] : [];
  res.json(db.prepare(q).all(...args).map(p => ({ ...p, detail: JSON.parse(p.detail_json) })));
});

// ---------- Dashboard ----------
app.get('/api/dashboard', (_req, res) => {
  const totalBahan = db.prepare('SELECT COUNT(*) n FROM bahan').get().n;
  const alert = db.prepare('SELECT * FROM bahan WHERE stok_saat_ini < stok_minimal ORDER BY stok_saat_ini/stok_minimal').all();
  const pakai7hari = db.prepare(`SELECT b.nama, b.satuan, SUM(t.jumlah) jml FROM transaksi t JOIN bahan b ON b.id=t.bahan_id
    WHERE t.tipe='PAKAI' AND t.tanggal >= date('now','-7 day') GROUP BY t.bahan_id ORDER BY jml DESC`).all();
  const pengadaanBulanIni = db.prepare(`SELECT COALESCE(SUM(harga_total),0) total FROM transaksi
    WHERE tipe='MASUK' AND strftime('%Y-%m',tanggal)=strftime('%Y-%m','now')`).get().total;
  const prediksiTerakhir = db.prepare(`SELECT p.id, p.forecast_next, p.mape, p.periode, b.nama, b.satuan
    FROM prediksi p JOIN bahan b ON b.id=p.bahan_id ORDER BY p.created_at DESC LIMIT 5`).all();
  res.json({ totalBahan, alert, pakai7hari, pengadaanBulanIni, prediksiTerakhir });
});

// ---------- Static frontend ----------
const dist = path.join(__dirname, '..', 'web', 'dist');
app.use(express.static(dist));
app.get(/^\/(?!api).*/, (_req, res) => res.sendFile(path.join(dist, 'index.html')));

const PORT = process.env.PORT || 5177;
app.listen(PORT, () => console.log(`SPPG MBG API on http://localhost:${PORT}`));
