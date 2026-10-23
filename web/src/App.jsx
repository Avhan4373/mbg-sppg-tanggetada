import React, { useEffect, useState, useCallback } from 'react';
import {
  LayoutDashboard, Package, ArrowLeftRight, TrendingUp, History,
  AlertTriangle, Plus, Trash2, Wand2, Save, UtensilsCrossed,
} from 'lucide-react';
import {
  ResponsiveContainer, LineChart, Line, XAxis, YAxis, Tooltip,
  Legend, CartesianGrid, BarChart, Bar,
} from 'recharts';

const api = (p, o) => fetch('/api' + p, o).then(r => r.json());
const fmt = n => n == null ? '-' : Number(n).toLocaleString('id-ID', { maximumFractionDigits: 1 });
const rupiah = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
const today = () => new Date().toISOString().slice(0, 10);

/* ---------- Layout ---------- */
const MENU = [
  { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { id: 'bahan', label: 'Bahan Makanan', icon: Package },
  { id: 'transaksi', label: 'Transaksi Stok', icon: ArrowLeftRight },
  { id: 'prediksi', label: 'Prediksi Holt', icon: TrendingUp },
  { id: 'riwayat', label: 'Riwayat Prediksi', icon: History },
];

export default function App() {
  const [page, setPage] = useState('dashboard');
  const [bahan, setBahan] = useState([]);
  const loadBahan = useCallback(() => api('/bahan').then(setBahan), []);
  useEffect(() => { loadBahan(); }, [loadBahan]);

  return (
    <div className="flex min-h-screen">
      <aside className="w-64 shrink-0 border-r border-slate-800 bg-slate-900/60 p-4 flex flex-col">
        <div className="mb-8 px-2">
          <div className="flex items-center gap-2 text-amber-400 font-bold text-lg">
            <UtensilsCrossed size={22} /> MBG Prediksi
          </div>
          <p className="mt-1 text-xs text-slate-400">SPPG Kecamatan Tanggetada<br />Program Makanan Bergizi Gratis</p>
        </div>
        <nav className="space-y-1">
          {MENU.map(m => (
            <button key={m.id} onClick={() => setPage(m.id)}
              className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition ${
                page === m.id ? 'bg-amber-500/15 text-amber-400' : 'text-slate-300 hover:bg-slate-800'}`}>
              <m.icon size={17} /> {m.label}
            </button>
          ))}
        </nav>
        <p className="mt-auto px-2 text-[11px] text-slate-500">Metode: Double Exponential Smoothing (Holt)</p>
      </aside>
      <main className="flex-1 p-8 overflow-auto">
        {page === 'dashboard' && <Dashboard go={setPage} />}
        {page === 'bahan' && <Bahan bahan={bahan} reload={loadBahan} />}
        {page === 'transaksi' && <Transaksi bahan={bahan} reload={loadBahan} />}
        {page === 'prediksi' && <Prediksi bahan={bahan} />}
        {page === 'riwayat' && <Riwayat bahan={bahan} />}
      </main>
    </div>
  );
}

const Card = ({ title, children, className = '' }) => (
  <div className={`rounded-xl border border-slate-800 bg-slate-900/60 p-5 ${className}`}>
    {title && <h3 className="mb-3 text-sm font-semibold text-slate-300">{title}</h3>}
    {children}
  </div>
);
const Th = ({ children }) => <th className="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">{children}</th>;
const Td = ({ children, className = '' }) => <td className={`px-3 py-2 text-sm ${className}`}>{children}</td>;

/* ---------- Dashboard ---------- */
function Dashboard({ go }) {
  const [d, setD] = useState(null);
  useEffect(() => { api('/dashboard').then(setD); }, []);
  if (!d) return <p className="text-slate-400">Memuat...</p>;
  const pakaiData = d.pakai7hari.map(x => ({ nama: x.nama.slice(0, 10), jumlah: x.jml }));
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Dashboard SPPG Tanggetada</h1>
      <div className="grid grid-cols-3 gap-4">
        <Card title="Total Jenis Bahan"><p className="text-3xl font-bold text-amber-400">{d.totalBahan}</p></Card>
        <Card title="Bahan di Bawah Stok Minimal">
          <p className={`text-3xl font-bold ${d.alert.length ? 'text-red-400' : 'text-emerald-400'}`}>{d.alert.length}</p>
        </Card>
        <Card title="Pengadaan Bulan Ini"><p className="text-3xl font-bold text-sky-400">{rupiah(d.pengadaanBulanIni)}</p></Card>
      </div>
      <div className="grid grid-cols-2 gap-4">
        <Card title="Peringatan Stok">
          {d.alert.length === 0 ? <p className="text-sm text-slate-400">Semua stok aman.</p> :
            <table className="w-full"><tbody>
              {d.alert.map(b => (
                <tr key={b.id} className="border-b border-slate-800 last:border-0">
                  <Td><AlertTriangle size={14} className="mr-2 inline text-red-400" />{b.nama}</Td>
                  <Td className="text-right text-red-400">{fmt(b.stok_saat_ini)} {b.satuan}</Td>
                  <Td className="text-right text-slate-400">min {fmt(b.stok_minimal)}</Td>
                </tr>))}
            </tbody></table>}
        </Card>
        <Card title="Pemakaian 7 Hari Terakhir">
          <ResponsiveContainer width="100%" height={180}>
            <BarChart data={pakaiData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" />
              <XAxis dataKey="nama" tick={{ fill: '#94a3b8', fontSize: 11 }} />
              <YAxis tick={{ fill: '#94a3b8', fontSize: 11 }} />
              <Tooltip contentStyle={{ background: '#0f172a', border: '1px solid #334155' }} />
              <Bar dataKey="jumlah" fill="#f59e0b" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </Card>
      </div>
      <Card title="Prediksi Terakhir">
        {d.prediksiTerakhir.length === 0
          ? <button onClick={() => go('prediksi')} className="text-sm text-amber-400 underline">Belum ada prediksi — buat sekarang</button>
          : <table className="w-full"><thead><tr><Th>Bahan</Th><Th>Periode</Th><Th>Forecast</Th><Th>MAPE</Th></tr></thead>
              <tbody>{d.prediksiTerakhir.map(p => (
                <tr key={p.id} className="border-b border-slate-800 last:border-0">
                  <Td>{p.nama}</Td><Td className="text-slate-400">{p.periode}</Td>
                  <Td className="font-semibold text-amber-400">{fmt(p.forecast_next)} {p.satuan}</Td>
                  <Td className="text-sky-400">{Number(p.mape).toFixed(2)}%</Td>
                </tr>))}</tbody></table>}
      </Card>
    </div>
  );
}

/* ---------- Bahan ---------- */
const KOSONG = { kode: '', nama: '', kategori: 'Karbohidrat', satuan: 'kg', stok_minimal: 0, harga_satuan: 0 };

function Bahan({ bahan, reload }) {
  const [form, setForm] = useState(KOSONG);
  const [editId, setEditId] = useState(null);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));
  const simpan = async () => {
    if (!form.nama || !form.kode) return alert('Kode & nama wajib diisi');
    const body = JSON.stringify({ ...form, stok_minimal: +form.stok_minimal, harga_satuan: +form.harga_satuan });
    if (editId) await api('/bahan/' + editId, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body });
    else await api('/bahan', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body });
    setForm(KOSONG); setEditId(null); reload();
  };
  const hapus = async id => { if (confirm('Hapus bahan beserta seluruh transaksinya?')) { await api('/bahan/' + id, { method: 'DELETE' }); reload(); } };
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Data Bahan Makanan MBG</h1>
      <Card title={editId ? 'Ubah Bahan' : 'Tambah Bahan'}>
        <div className="grid grid-cols-6 gap-3">
          <input placeholder="Kode" value={form.kode} disabled={!!editId} onChange={e => set('kode', e.target.value)} className={inp(!!editId)} />
          <input placeholder="Nama bahan" value={form.nama} onChange={e => set('nama', e.target.value)} className={inp()} />
          <select value={form.kategori} onChange={e => set('kategori', e.target.value)} className={inp()}>
            {['Karbohidrat', 'Protein', 'Sayuran', 'Bumbu', 'Lainnya'].map(k => <option key={k}>{k}</option>)}
          </select>
          <input placeholder="Satuan" value={form.satuan} onChange={e => set('satuan', e.target.value)} className={inp()} />
          <input type="number" placeholder="Stok minimal" value={form.stok_minimal} onChange={e => set('stok_minimal', e.target.value)} className={inp()} />
          <input type="number" placeholder="Harga/satuan" value={form.harga_satuan} onChange={e => set('harga_satuan', e.target.value)} className={inp()} />
        </div>
        <div className="mt-3 flex gap-2">
          <button onClick={simpan} className="flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-400">
            <Save size={15} /> {editId ? 'Simpan Perubahan' : 'Tambah'}
          </button>
          {editId && <button onClick={() => { setEditId(null); setForm(KOSONG); }} className="rounded-lg border border-slate-700 px-4 py-2 text-sm">Batal</button>}
        </div>
      </Card>
      <Card>
        <table className="w-full">
          <thead><tr><Th>Kode</Th><Th>Nama</Th><Th>Kategori</Th><Th>Satuan</Th><Th className="text-right">Stok</Th><Th className="text-right">Minimal</Th><Th className="text-right">Harga</Th><Th></Th></tr></thead>
          <tbody>{bahan.map(b => (
            <tr key={b.id} className="border-b border-slate-800 last:border-0 hover:bg-slate-800/40">
              <Td className="font-mono text-xs text-slate-400">{b.kode}</Td>
              <Td className="font-medium">{b.nama}</Td>
              <Td><span className="rounded-full bg-slate-800 px-2 py-0.5 text-xs text-slate-300">{b.kategori}</span></Td>
              <Td className="text-slate-400">{b.satuan}</Td>
              <Td className={`text-right font-semibold ${b.stok_saat_ini < b.stok_minimal ? 'text-red-400' : 'text-emerald-400'}`}>{fmt(b.stok_saat_ini)}</Td>
              <Td className="text-right text-slate-400">{fmt(b.stok_minimal)}</Td>
              <Td className="text-right text-slate-400">{rupiah(b.harga_satuan)}</Td>
              <Td className="text-right">
                <button onClick={() => { setForm(b); setEditId(b.id); }} className="mr-3 text-xs text-sky-400 hover:underline">Ubah</button>
                <button onClick={() => hapus(b.id)} className="text-xs text-red-400 hover:underline"><Trash2 size={13} className="inline" /></button>
              </Td>
            </tr>))}</tbody>
        </table>
      </Card>
    </div>
  );
}
const inp = dis => `rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm outline-none focus:border-amber-500 ${dis ? 'opacity-50' : ''}`;

/* ---------- Transaksi ---------- */
function Transaksi({ bahan, reload }) {
  const [list, setList] = useState([]);
  const [form, setForm] = useState({ tipe: 'PAKAI', tanggal: today(), bahan_id: '', jumlah: '', keterangan: '' });
  const load = useCallback(() => api('/transaksi').then(setList), []);
  useEffect(() => { load(); }, [load]);
  const set = (k, v) => setForm(f => ({ ...f, [k]: v }));
  const simpan = async () => {
    if (!form.bahan_id || !form.jumlah) return alert('Bahan & jumlah wajib diisi');
    const b = bahan.find(x => x.id === +form.bahan_id);
    await api('/transaksi', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ...form, bahan_id: +form.bahan_id, jumlah: +form.jumlah,
        harga_total: form.tipe === 'MASUK' ? +form.jumlah * b.harga_satuan : 0,
      }),
    });
    setForm({ ...form, jumlah: '', keterangan: '' }); load(); reload();
  };
  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Transaksi Stok</h1>
      <Card title="Input Transaksi">
        <div className="grid grid-cols-5 gap-3">
          <select value={form.tipe} onChange={e => set('tipe', e.target.value)} className={inp()}>
            <option value="PAKAI">Pakai (Penggunaan Menu MBG)</option>
            <option value="MASUK">Masuk (Pengadaan)</option>
          </select>
          <input type="date" value={form.tanggal} onChange={e => set('tanggal', e.target.value)} className={inp()} />
          <select value={form.bahan_id} onChange={e => set('bahan_id', e.target.value)} className={inp()}>
            <option value="">-- Pilih bahan --</option>
            {bahan.map(b => <option key={b.id} value={b.id}>{b.nama} ({b.satuan})</option>)}
          </select>
          <input type="number" placeholder="Jumlah" value={form.jumlah} onChange={e => set('jumlah', e.target.value)} className={inp()} />
          <input placeholder="Keterangan (opsional)" value={form.keterangan} onChange={e => set('keterangan', e.target.value)} className={inp()} />
        </div>
        <button onClick={simpan} className="mt-3 flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-400">
          <Plus size={15} /> Simpan Transaksi
        </button>
      </Card>
      <Card title="200 Transaksi Terakhir">
        <table className="w-full">
          <thead><tr><Th>Tanggal</Th><Th>Tipe</Th><Th>Bahan</Th><Th className="text-right">Jumlah</Th><Th>Keterangan</Th></tr></thead>
          <tbody>{list.map(t => (
            <tr key={t.id} className="border-b border-slate-800 last:border-0">
              <Td className="text-slate-400">{t.tanggal}</Td>
              <Td><span className={`rounded-full px-2 py-0.5 text-xs ${t.tipe === 'MASUK' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400'}`}>{t.tipe}</span></Td>
              <Td>{t.nama_bahan}</Td>
              <Td className="text-right font-medium">{fmt(t.jumlah)} {t.satuan}</Td>
              <Td className="text-xs text-slate-500">{t.keterangan}</Td>
            </tr>))}</tbody>
        </table>
      </Card>
    </div>
  );
}

/* ---------- Prediksi Holt ---------- */
function Prediksi({ bahan }) {
  const [bahanId, setBahanId] = useState('');
  const [alpha, setAlpha] = useState(0.3);
  const [beta, setBeta] = useState(0.2);
  const [hasil, setHasil] = useState(null);
  const [optim, setOptim] = useState(null);
  const [loading, setLoading] = useState(false);
  const b = bahan.find(x => x.id === +bahanId);

  const hitung = async () => {
    if (!bahanId) return alert('Pilih bahan makanan dulu');
    setLoading(true);
    try {
      const r = await api('/prediksi', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bahanId: +bahanId, alpha: +alpha, beta: +beta }),
      });
      if (r.error) alert(r.error); else setHasil(r);
    } finally { setLoading(false); }
  };
  const auto = async () => {
    if (!bahanId) return alert('Pilih bahan makanan dulu');
    const r = await api(`/prediksi/optimize/${bahanId}`);
    if (r.error) return alert(r.error);
    setOptim(r); setAlpha(r.alpha); setBeta(r.beta);
  };

  const chartData = hasil ? hasil.detail.map(d => ({
    periode: d.periode.slice(5), Aktual: d.yt, Prediksi: d.ft == null ? null : Math.round(d.ft * 10) / 10,
  })) : [];
  const kebutuhan = hasil ? Math.round(hasil.forecastNext) : 0;
  const beli = b ? Math.max(0, Math.round(kebutuhan - b.stok_saat_ini)) : 0;

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Prediksi Persediaan — Double Exponential Smoothing (Holt)</h1>
      <Card title="Parameter Peramalan">
        <div className="grid grid-cols-4 gap-4 items-end">
          <label className="block text-sm">
            <span className="mb-1 block text-slate-400">Bahan Makanan</span>
            <select value={bahanId} onChange={e => { setBahanId(e.target.value); setHasil(null); setOptim(null); }} className={inp()}>
              <option value="">-- Pilih bahan --</option>
              {bahan.map(x => <option key={x.id} value={x.id}>{x.nama}</option>)}
            </select>
          </label>
          <Slider label="Alpha (α)" v={alpha} set={setAlpha} hint={optim ? 'hasil optimasi' : ''} />
          <Slider label="Beta (β)" v={beta} set={setBeta} hint={optim ? 'hasil optimasi' : ''} />
          <div className="flex gap-2">
            <button onClick={hitung} disabled={loading}
              className="flex items-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-400 disabled:opacity-50">
              <TrendingUp size={15} /> {loading ? 'Menghitung...' : 'Hitung Prediksi'}
            </button>
            <button onClick={auto} title="Cari kombinasi alpha, beta dengan MAPE terkecil"
              className="flex items-center gap-2 rounded-lg border border-amber-500/50 px-3 py-2 text-sm text-amber-400 hover:bg-amber-500/10">
              <Wand2 size={15} /> Auto
            </button>
          </div>
        </div>
        {optim && <p className="mt-2 text-xs text-emerald-400">α &amp; β optimal: α={optim.alpha}, β={optim.beta} (MAPE {optim.mape.toFixed(2)}%). Klik "Hitung Prediksi".</p>}
      </Card>

      {hasil && (
        <>
          <div className="grid grid-cols-4 gap-4">
            <Card title={`Forecast ${hasil.detail[hasil.detail.length-1].periode} + 7 hari`}>
              <p className="text-3xl font-bold text-amber-400">{kebutuhan} <span className="text-base text-slate-400">{b?.satuan}</span></p>
            </Card>
            <Card title="MAPE (Kesalahan Rata-rata)">
              <p className={`text-3xl font-bold ${hasil.mape < 10 ? 'text-emerald-400' : hasil.mape < 20 ? 'text-amber-400' : 'text-red-400'}`}>
                {hasil.mape.toFixed(2)}%</p>
              <p className="mt-1 text-xs text-slate-500">Ketepatan {Math.max(0, 100 - hasil.mape).toFixed(2)}%</p>
            </Card>
            <Card title="MPE (Bias Kesalahan)">
              <p className="text-3xl font-bold text-sky-400">{hasil.mpe.toFixed(2)}%</p>
            </Card>
            <Card title="Rekomendasi Pengadaan">
              <p className="text-3xl font-bold text-emerald-400">{beli} <span className="text-base text-slate-400">{b?.satuan}</span></p>
              <p className="mt-1 text-xs text-slate-500">stok saat ini {fmt(b?.stok_saat_ini)}</p>
            </Card>
          </div>
          <Card title="Grafik: Aktual vs Prediksi (Pemakaian Mingguan)">
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" />
                <XAxis dataKey="periode" tick={{ fill: '#94a3b8', fontSize: 11 }} />
                <YAxis tick={{ fill: '#94a3b8', fontSize: 11 }} />
                <Tooltip contentStyle={{ background: '#0f172a', border: '1px solid #334155' }} />
                <Legend />
                <Line type="monotone" dataKey="Aktual" stroke="#38bdf8" strokeWidth={2} dot={false} />
                <Line type="monotone" dataKey="Prediksi" stroke="#f59e0b" strokeWidth={2} strokeDasharray="6 3" dot={false} />
              </LineChart>
            </ResponsiveContainer>
          </Card>
          <Card title="Tabel Perhitungan Step-by-Step (Lt, Tt, Ft, PE)">
            <div className="max-h-96 overflow-auto">
              <table className="w-full">
                <thead className="sticky top-0 bg-slate-900"><tr>
                  <Th>t</Th><Th>Periode</Th><Th className="text-right">Yt (Aktual)</Th>
                  <Th className="text-right">Lt (Level)</Th><Th className="text-right">Tt (Trend)</Th>
                  <Th className="text-right">Ft (Forecast)</Th><Th className="text-right">PE (%)</Th>
                </tr></thead>
                <tbody>{hasil.detail.map(d => (
                  <tr key={d.t} className="border-b border-slate-800 last:border-0">
                    <Td className="text-slate-500">{d.t}</Td>
                    <Td className="text-slate-400">{d.periode}</Td>
                    <Td className="text-right">{fmt(d.yt)}</Td>
                    <Td className="text-right text-slate-300">{fmt(d.lt)}</Td>
                    <Td className="text-right text-slate-300">{fmt(d.tt)}</Td>
                    <Td className="text-right text-amber-400">{d.ft == null ? '-' : fmt(d.ft)}</Td>
                    <Td className={`text-right ${d.pe != null && Math.abs(d.pe) > 10 ? 'text-red-400' : 'text-emerald-400'}`}>
                      {d.pe == null ? '-' : d.pe.toFixed(2)}</Td>
                  </tr>))}</tbody>
              </table>
            </div>
          </Card>
        </>
      )}
    </div>
  );
}

const Slider = ({ label, v, set, hint }) => (
  <label className="block text-sm">
    <span className="mb-1 flex items-center justify-between text-slate-400">
      {label} <span className="font-mono text-amber-400">{Number(v).toFixed(2)}</span>
      {hint && <span className="text-[10px] text-emerald-400">{hint}</span>}
    </span>
    <input type="range" min="0.01" max="0.99" step="0.01" value={v}
      onChange={e => set(+e.target.value)} className="w-full accent-amber-500" />
  </label>
);

/* ---------- Riwayat ---------- */
function Riwayat({ bahan }) {
  const [list, setList] = useState([]);
  const [bahanId, setBahanId] = useState('');
  useEffect(() => { api('/prediksi' + (bahanId ? '?bahanId=' + bahanId : '')).then(setList); }, [bahanId]);
  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Riwayat Prediksi</h1>
        <select value={bahanId} onChange={e => setBahanId(e.target.value)} className={inp() + ' w-64'}>
          <option value="">Semua bahan</option>
          {bahan.map(b => <option key={b.id} value={b.id}>{b.nama}</option>)}
        </select>
      </div>
      <Card>
        {list.length === 0 ? <p className="text-sm text-slate-400">Belum ada prediksi tersimpan.</p> :
          <table className="w-full">
            <thead><tr><Th>Waktu Hitung</Th><Th>Bahan</Th><Th>α</Th><Th>β</Th><Th>Periode Forecast</Th>
              <Th className="text-right">Forecast</Th><Th className="text-right">MAPE</Th></tr></thead>
            <tbody>{list.map(p => (
              <tr key={p.id} className="border-b border-slate-800 last:border-0">
                <Td className="text-slate-500">{p.created_at}</Td>
                <Td className="font-medium">{p.nama_bahan}</Td>
                <Td className="font-mono text-sky-400">{p.alpha}</Td>
                <Td className="font-mono text-sky-400">{p.beta}</Td>
                <Td className="text-slate-400">{p.periode}</Td>
                <Td className="text-right font-semibold text-amber-400">{fmt(p.forecast_next)} {p.satuan}</Td>
                <Td className="text-right text-emerald-400">{Number(p.mape).toFixed(2)}%</Td>
              </tr>))}</tbody>
          </table>}
      </Card>
    </div>
  );
}
