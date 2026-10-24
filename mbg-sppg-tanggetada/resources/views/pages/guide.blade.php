@extends('layouts.app')

@section('title', 'Panduan Metode Holt — SIPRES MBG Tanggetada')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Prediksi</a><span>/</span>Panduan Metode
    </div>

    <div class="page-head">
        <div>
            <h1>Panduan Metode Double Exponential Smoothing (Holt)</h1>
            <p>
                Penjelasan lengkap alur perhitungan yang dipakai sistem: dari agregasi data mingguan,
                pemilihan α dan β, ukuran error, hingga safety stock dan saran pembelian.
            </p>
        </div>
        <a href="{{ route('home') }}#form-prediksi" class="btn btn-primary">Coba Prediksi</a>
    </div>

    <div class="grid grid-sidebar" style="grid-template-columns:minmax(0,1fr) 300px">
        <div class="card">
            <div class="step">
                <h3>1. Mengapa Holt, bukan Holt-Winters?</h3>
                <p>
                    Data pemakaian bahan MBG umumnya menunjukkan <strong>level</strong> (besaran pemakaian
                    saat ini) dan <strong>trend</strong> (kecenderungan naik/turun). Metode Holt menangani
                    keduanya tanpa komponen musiman. Holt-Winters akan menambah komponen musiman yang
                    membutuhkan setidaknya dua siklus musim lengkap; pada data mingguan yang pendek,
                    komponen itu justru rawan overfitting.
                </p>
                <p>
                    Karena itu sistem ini memakai Holt murni. Bila kelak tersedia data minimal dua tahun
                    dan pola musiman terbukti kuat, komponen musiman dapat ditambahkan.
                </p>
            </div>

            <div class="step">
                <h3>2. Menyiapkan deret waktu mingguan</h3>
                <p>
                    Hanya transaksi <strong>Pemakaian</strong> yang dipakai sebagai Y<sub>t</sub>.
                    Transaksi stok masuk, bahan terbuang, dan penyesuaian tidak dihitung sebagai permintaan.
                </p>
                <div class="formula">
                    Y<sub>t</sub> = Σ kuantitas pemakaian pada minggu ke-t
                </div>
                <p>
                    Minggu tanpa transaksi tetap dimasukkan dengan nilai 0. Hal ini menjaga jarak antar
                    periode tetap satu minggu. Sistem mensyaratkan <strong>minimal 12 periode</strong>
                    agar estimasi level dan trend tidak terlalu rapuh.
                </p>
            </div>

            <div class="step">
                <h3>3. Inisialisasi</h3>
                <div class="formula">
                    L<sub>1</sub> = Y<sub>1</sub><br>
                    T<sub>1</sub> = Y<sub>2</sub> − Y<sub>1</sub>
                </div>
                <p>
                    Level awal disamakan dengan pengamatan pertama; trend awal adalah selisih dua
                    pengamatan pertama. Nilai ini hanya titik mulai — iterasi berikutnya akan
                    mengoreksinya secara bertahap.
                </p>
            </div>

            <div class="step">
                <h3>4. Iterasi level dan trend</h3>
                <p>Untuk t = 2, 3, …, n:</p>
                <div class="formula">
                    F<sub>t</sub> = L<sub>t−1</sub> + T<sub>t−1</sub><br>
                    L<sub>t</sub> = α · Y<sub>t</sub> + (1 − α) · F<sub>t</sub><br>
                    T<sub>t</sub> = β · (L<sub>t</sub> − L<sub>t−1</sub>) + (1 − β) · T<sub>t−1</sub><br>
                    e<sub>t</sub> = Y<sub>t</sub> − F<sub>t</sub>
                </div>
                <ul style="padding-left:18px">
                    <li><strong>α (alpha)</strong> mengatur seberapa cepat level mengikuti data terbaru. α besar = responsif, α kecil = halus.</li>
                    <li><strong>β (beta)</strong> mengatur seberapa cepat trend berubah. β besar = trend cepat menyesuaikan, β kecil = trend stabil.</li>
                    <li><strong>F<sub>t</sub></strong> adalah prediksi satu langkah ke depan yang dihitung sebelum melihat Y<sub>t</sub>, sehingga adil dipakai untuk menghitung error.</li>
                </ul>
            </div>

            <div class="step">
                <h3>5. Memilih α dan β secara objektif</h3>
                <p>
                    Sistem tidak meminta pengguna menebak parameter. Kombinasi α dan β diuji dengan
                    <em>grid search</em>: masing-masing dari 0,01 sampai 0,99 dengan langkah 0,01,
                    sehingga terdapat <strong>99 × 99 = 9.801 kombinasi</strong>.
                </p>
                <ol style="padding-left:18px">
                    <li>Deret dibagi menjadi data latih (sekitar 80%) dan data uji (20% terakhir, minimal 4 periode).</li>
                    <li>Setiap kombinasi dilatih pada data latih, lalu memprediksi data uji secara berjalan.</li>
                    <li>Kombinasi dengan <strong>RMSE terkecil</strong> dipilih; MAPE menjadi penentu bila RMSE seri.</li>
                    <li>Model dilatih ulang dengan seluruh data memakai parameter terpilih untuk menghasilkan ramalan akhir.</li>
                </ol>
                <p>
                    Pemisahan data uji mencegah parameter “menghafal” data dan membuat laporan akurasi
                    lebih jujur.
                </p>
            </div>

            <div class="step">
                <h3>6. Ukuran error</h3>
                <div class="formula">
                    MAE = (1/n) · Σ |e<sub>t</sub>|<br>
                    MSE = (1/n) · Σ e<sub>t</sub><sup>2</sup><br>
                    RMSE = √MSE<br>
                    MAPE = (100% / n) · Σ |e<sub>t</sub> / Y<sub>t</sub>|
                </div>
                <p>
                    MAE dan RMSE memakai satuan bahan (mis. kg), sehingga mudah dibaca tetapi sulit
                    dibandingkan antar bahan. MAPE berupa persentase dan menjadi ukuran utama pada
                    laporan. Nilai Y<sub>t</sub> = 0 dilewati saat menghitung MAPE agar tidak terjadi
                    pembagian nol.
                </p>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>MAPE</th><th>Kategori akurasi</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>&lt; 10%</td><td><span class="badge badge-success">Sangat akurat</span></td></tr>
                            <tr><td>10% – 20%</td><td><span class="badge badge-info">Baik</span></td></tr>
                            <tr><td>20% – 50%</td><td><span class="badge badge-warning">Cukup</span></td></tr>
                            <tr><td>&gt; 50%</td><td><span class="badge badge-danger">Kurang akurat</span></td></tr>
                        </tbody>
                    </table>
                </div>
                <p style="font-size:13px;color:var(--muted);margin-top:8px">
                    Ambang ini mengikuti skala Lewis yang lazim dipakai pada peramalan permintaan.
                </p>
            </div>

            <div class="step">
                <h3>7. Ramalan ke depan</h3>
                <div class="formula">
                    F<sub>n+h</sub> = L<sub>n</sub> + h · T<sub>n</sub>
                </div>
                <p>
                    h adalah jarak periode dari data terakhir. Prediksi bersifat linear karena Holt
                    mengasumsikan trend berlanjut dengan laju T<sub>n</sub>. Nilai negatif dibatasi
                    menjadi nol. Total kebutuhan horizon = jumlah seluruh F<sub>n+h</sub>.
                </p>
            </div>

            <div class="step">
                <h3>8. Safety stock</h3>
                <div class="formula">
                    SS = Z · σ<sub>e</sub> · √LT
                </div>
                <p>
                    σ<sub>e</sub> adalah simpangan baku error historis (dengan pembagi n − 1), dan LT
                    adalah lead time dalam minggu (lead time hari ÷ 7, minimal 1). Z bergantung pada
                    service level:
                </p>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Service level</th><th class="num">Z</th><th>Makna</th></tr></thead>
                        <tbody>
                            <tr><td>90%</td><td class="num">1,28</td><td>10% kemungkinan kekurangan</td></tr>
                            <tr><td>95%</td><td class="num">1,65</td><td>5% kemungkinan kekurangan</td></tr>
                            <tr><td>99%</td><td class="num">2,33</td><td>1% kemungkinan kekurangan</td></tr>
                        </tbody>
                    </table>
                </div>
                <p style="font-size:13px;color:var(--muted);margin-top:8px">
                    Jika σ<sub>e</sub> sangat kecil, sistem memakai cadangan minimum 20% dari rata-rata
                    ramalan agar rekomendasi tetap masuk akal.
                </p>
            </div>

            <div class="step">
                <h3>9. Reorder point dan saran pembelian</h3>
                <div class="formula">
                    Rata-rata ramalan = (Σ F<sub>n+h</sub>) / h<br>
                    ROP = (rata-rata ramalan × LT) + SS<br>
                    Saran beli = Σ F<sub>n+h</sub> + SS − stok saat ini − stok dalam perjalanan
                </div>
                <p>
                    ROP menjawab “kapan harus memesan?”, sedangkan saran pembelian menjawab
                    “berapa yang perlu dipesan?”. Stok saat ini dihitung dari seluruh transaksi:
                    stok masuk + penyesuaian − pemakaian − bahan terbuang.
                </p>
            </div>

            <div class="step" style="border-left-color:var(--accent)">
                <h3>10. Batasan yang perlu disadari</h3>
                <ul style="padding-left:18px;margin:0">
                    <li>Tidak memodelkan musim/ hari besar; gunakan penyesuaian manual bila perlu.</li>
                    <li>Kualitas hasil sangat bergantung pada kelengkapan pencatatan pemakaian.</li>
                    <li>Lead time dianggap stabil; perubahan pemasok dapat mengubah safety stock.</li>
                    <li>Prediksi bukan keputusan final — tetap validasi dengan kondisi dapur dan anggaran.</li>
                </ul>
            </div>
        </div>

        <aside class="grid" style="gap:14px;align-content:start">
            <div class="card tight">
                <div class="section-title">Alur singkat</div>
                <ol style="margin:0;padding-left:18px;font-size:13.5px;line-height:1.9">
                    <li>Pilih bahan &amp; rentang data.</li>
                    <li>Sistem agregasi pemakaian per minggu.</li>
                    <li>Grid search α, β pada data uji.</li>
                    <li>Latih ulang dengan seluruh data.</li>
                    <li>Ramalkan h periode ke depan.</li>
                    <li>Hitung SS, ROP, saran beli.</li>
                    <li>Tampilkan grafik &amp; tabel perhitungan.</li>
                </ol>
            </div>
            <div class="card tight">
                <div class="section-title">Simbol</div>
                <dl class="kv" style="grid-template-columns:44px 1fr;font-size:13px">
                    <dt>Y</dt><dd>aktual pemakaian</dd>
                    <dt>L</dt><dd>level</dd>
                    <dt>T</dt><dd>trend</dd>
                    <dt>F</dt><dd>prediksi</dd>
                    <dt>e</dt><dd>error</dd>
                    <dt>α</dt><dd>pemulus level</dd>
                    <dt>β</dt><dd>pemulus trend</dd>
                    <dt>SS</dt><dd>safety stock</dd>
                    <dt>LT</dt><dd>lead time (minggu)</dd>
                    <dt>Z</dt><dd>faktor service level</dd>
                </dl>
            </div>
            <div class="note">
                Ingin melihat penerapan angka nyata? Buka salah satu hasil prediksi — setiap langkah
                ditampilkan dengan nilai aktual dari data SPPG.
            </div>
        </aside>
    </div>
@endsection
