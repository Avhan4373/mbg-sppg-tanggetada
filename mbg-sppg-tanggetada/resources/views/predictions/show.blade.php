@extends('layouts.app')

@use('App\Support\NumberFormat')

@section('title', 'Hasil Prediksi ' . $run->ingredient->name . ' — SIPRES MBG')

@section('content')
    @php
        $unit = $run->ingredient->unit->symbol ?? '';
        $alpha = (float) $run->alpha;
        $beta = (float) $run->beta;
        $oneMinusAlpha = 1 - $alpha;
        $oneMinusBeta = 1 - $beta;
        $n = $history->count();
    @endphp

    <div class="breadcrumb no-print">
        <a href="{{ route('home') }}">Prediksi</a><span>/</span>Hasil {{ $run->ingredient->name }}
    </div>

    <div class="page-head">
        <div>
            <h1>Hasil Prediksi — {{ $run->ingredient->name }}</h1>
            <p>
                Dihitung {{ NumberFormat::dateTime($run->created_at) }} ·
                data {{ NumberFormat::date($run->start_date) }} s.d. {{ NumberFormat::date($run->end_date) }} ·
                horizon {{ $run->horizon }} minggu
                @if ($run->creator) · oleh {{ $run->creator->name }} @endif
            </p>
        </div>
        <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap">
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">Cetak</button>
            <a href="{{ route('home') }}" class="btn btn-primary btn-sm">Prediksi Baru</a>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-4" style="margin-bottom:18px">
        <div class="stat">
            <div class="stat-label">Saran pembelian</div>
            <div class="stat-value" style="color:var(--brand)">{{ NumberFormat::id($run->recommended_purchase, 2) }} <span class="unit">{{ $unit }}</span></div>
            <div class="stat-sub">{{ $run->recommended_purchase > 0 ? 'perlu pengadaan untuk horizon ini' : 'stok dinilai mencukupi' }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Safety stock</div>
            <div class="stat-value">{{ NumberFormat::id($run->safety_stock, 2) }} <span class="unit">{{ $unit }}</span></div>
            <div class="stat-sub">cadangan pada service level {{ NumberFormat::id((float) $run->service_level * 100, 0) }}%</div>
        </div>
        <div class="stat">
            <div class="stat-label">Reorder point</div>
            <div class="stat-value">{{ NumberFormat::id($run->reorder_point, 2) }} <span class="unit">{{ $unit }}</span></div>
            <div class="stat-sub">pesan ulang saat stok menyentuh angka ini</div>
        </div>
        <div class="stat">
            <div class="stat-label">MAPE / akurasi</div>
            <div class="stat-value">{{ $run->mape === null ? '-' : NumberFormat::percent($run->mape) }}</div>
            <div class="stat-sub">
                <span class="badge badge-{{ $explanation['accuracy']['tone'] }}">{{ $explanation['accuracy']['label'] }}</span>
                estimasi akurasi {{ $run->accuracyLabel() }}
            </div>
        </div>
    </div>

    <div class="alert {{ $run->recommended_purchase > 0 ? 'alert-info' : 'alert-success' }}" style="margin-bottom:18px">
        @if ($run->recommended_purchase > 0)
            Berdasarkan prediksi {{ $run->horizon }} minggu ke depan, kebutuhan total
            <strong>{{ NumberFormat::id($explanation['total_future'], 2) }} {{ $unit }}</strong> ditambah safety stock
            <strong>{{ NumberFormat::id($run->safety_stock, 2) }} {{ $unit }}</strong>, dikurangi stok saat ini
            <strong>{{ NumberFormat::id($run->current_stock, 2) }} {{ $unit }}</strong> dan stok dalam perjalanan
            <strong>{{ NumberFormat::id($run->on_order, 2) }} {{ $unit }}</strong>, sehingga disarankan membeli
            <strong>{{ NumberFormat::id($run->recommended_purchase, 2) }} {{ $unit }}</strong>.
        @else
            Stok saat ini ({{ NumberFormat::id($run->current_stock, 2) }} {{ $unit }}) dan stok dalam perjalanan
            ({{ NumberFormat::id($run->on_order, 2) }} {{ $unit }}) dinilai masih mencukupi kebutuhan
            {{ $run->horizon }} minggu ke depan beserta safety stock. Belum perlu pembelian tambahan.
        @endif
    </div>

    {{-- Grafik --}}
    <div class="card" style="margin-bottom:18px">
        <div class="card-head">
            <div>
                <h2>Grafik Aktual vs Prediksi</h2>
                <p>Garis biru adalah pemakaian aktual mingguan; garis oranye putus-putus adalah nilai prediksi Holt.</p>
            </div>
            <span class="badge badge-brand">{{ $n }} periode historis + {{ $future->count() }} periode prediksi</span>
        </div>
        <div class="chart-box">
            <canvas id="forecast-chart" aria-label="Grafik aktual dan prediksi" role="img"></canvas>
        </div>
    </div>

    {{-- Penjelasan perhitungan --}}
    <div class="card" style="margin-bottom:18px">
        <div class="card-head">
            <div>
                <h2>Penjelasan Perhitungan Lengkap</h2>
                <p>Setiap angka pada ringkasan di atas dapat ditelusuri langkah demi langkah di bawah ini.</p>
            </div>
        </div>

        <div class="step">
            <h3>1. Agregasi data pemakaian mingguan</h3>
            <p>
                Transaksi berjenis <strong>Pemakaian</strong> dijumlahkan per minggu (Senin–Minggu).
                Periode yang tidak memiliki transaksi tetap dihitung sebagai nol agar deret waktu tetap
                berjarak sama. Dari rentang {{ NumberFormat::date($run->start_date) }} sampai
                {{ NumberFormat::date($run->end_date) }} diperoleh <strong>{{ $n }} periode</strong>.
            </p>
            <p>
                Data dibagi menjadi <strong>{{ $run->training_periods }} periode latih</strong> dan
                <strong>{{ $run->validation_periods }} periode uji</strong>. Periode uji adalah bagian
                akhir deret (sekitar 20%, minimal 4 periode) dan tidak dipakai saat model latih mencari
                parameter.
            </p>
            <div class="formula">
                Y<sub>t</sub> = total kuantitas pemakaian pada minggu ke-t (satuan {{ $unit }})
            </div>
        </div>

        <div class="step">
            <h3>2. Inisialisasi level dan trend</h3>
            <p>Holt membutuhkan nilai awal level dan trend sebelum iterasi dimulai:</p>
            <div class="formula">
                L<sub>1</sub> = Y<sub>1</sub> = {{ NumberFormat::id($explanation['initial_level'], 4) }}<br>
                T<sub>1</sub> = Y<sub>2</sub> − Y<sub>1</sub> =
                {{ NumberFormat::id($explanation['second_actual'], 3) }} −
                {{ NumberFormat::id($explanation['first_actual'], 3) }} =
                {{ NumberFormat::id($explanation['initial_trend'], 4) }}
            </div>
            <p style="font-size:13px;color:var(--muted)">
                Y<sub>1</sub> = {{ NumberFormat::date($explanation['first_period']) }} ·
                Y<sub>2</sub> = {{ NumberFormat::date($explanation['second_period']) }}
            </p>
        </div>

        <div class="step">
            <h3>3. Persamaan smoothing Holt</h3>
            <p>Untuk setiap periode t ≥ 2, hitung berturut-turut:</p>
            <div class="formula">
                F<sub>t</sub> = L<sub>t−1</sub> + T<sub>t−1</sub> &nbsp;&nbsp;(prediksi satu langkah ke depan)<br>
                L<sub>t</sub> = α · Y<sub>t</sub> + (1 − α) · F<sub>t</sub> &nbsp;&nbsp;(level baru)<br>
                T<sub>t</sub> = β · (L<sub>t</sub> − L<sub>t−1</sub>) + (1 − β) · T<sub>t−1</sub> &nbsp;&nbsp;(trend baru)<br>
                e<sub>t</sub> = Y<sub>t</sub> − F<sub>t</sub> &nbsp;&nbsp;(error / selisih)
            </div>
            <p>
                Parameter terpilih: <strong>α = {{ NumberFormat::id($alpha, 4) }}</strong> dan
                <strong>β = {{ NumberFormat::id($beta, 4) }}</strong>, sehingga
                1 − α = {{ NumberFormat::id($oneMinusAlpha, 4) }} dan
                1 − β = {{ NumberFormat::id($oneMinusBeta, 4) }}.
            </p>

            @foreach ($explanation['worked_example'] as $index => $row)
                <div class="calc-item" style="margin:10px 0">
                    <div class="k">Contoh iterasi — periode {{ $index + 2 }} ({{ NumberFormat::date($row['period']) }})</div>
                    <div class="mono" style="margin-top:8px;font-size:13.5px;line-height:1.9">
                        F = L<sub>sebelumnya</sub> + T<sub>sebelumnya</sub> =
                        {{ NumberFormat::id($row['previous_level'], 4) }} + {{ NumberFormat::id($row['previous_trend'], 4) }} =
                        <strong>{{ NumberFormat::id($row['forecast'], 4) }}</strong><br>
                        L = ({{ NumberFormat::id($alpha, 4) }} × {{ NumberFormat::id($row['actual'], 3) }}) +
                        ({{ NumberFormat::id($oneMinusAlpha, 4) }} × {{ NumberFormat::id($row['forecast'], 4) }}) =
                        <strong>{{ NumberFormat::id($row['level'], 4) }}</strong><br>
                        T = ({{ NumberFormat::id($beta, 4) }} × ({{ NumberFormat::id($row['level'], 4) }} −
                        {{ NumberFormat::id($row['previous_level'], 4) }})) +
                        ({{ NumberFormat::id($oneMinusBeta, 4) }} × {{ NumberFormat::id($row['previous_trend'], 4) }}) =
                        <strong>{{ NumberFormat::id($row['trend'], 4) }}</strong><br>
                        e = {{ NumberFormat::id($row['actual'], 3) }} − {{ NumberFormat::id($row['forecast'], 3) }} =
                        <strong style="color:{{ $row['error'] < 0 ? 'var(--danger)' : 'var(--success)' }}">{{ NumberFormat::id($row['error'], 4) }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="step">
            <h3>4. Pemilihan α dan β secara otomatis</h3>
            <p>
                Sistem menguji <strong>9.801 kombinasi</strong> α dan β (masing-masing 0,01 sampai 0,99
                dengan langkah 0,01). Setiap kombinasi dilatih pada {{ $run->training_periods }} periode
                awal, lalu diuji pada {{ $run->validation_periods }} periode berikutnya. Kombinasi dengan
                <strong>RMSE terkecil</strong> dipilih; bila RMSE seri, MAPE menjadi penentu.
            </p>
            <div class="calc">
                <div class="calc-item">
                    <div class="k">α terpilih</div>
                    <div class="v">{{ NumberFormat::id($alpha, 2) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">β terpilih</div>
                    <div class="v">{{ NumberFormat::id($beta, 2) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">Periode latih / uji</div>
                    <div class="v">{{ $run->training_periods }} / {{ $run->validation_periods }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">RMSE validasi</div>
                    <div class="v">{{ NumberFormat::id($run->rmse, 3) }}</div>
                </div>
            </div>
            <p style="margin-top:10px">
                Setelah parameter terbaik ditemukan, model <strong>dilatih ulang memakai seluruh
                {{ $n }} periode</strong> agar prediksi akhir memanfaatkan data terbaru. Nilai
                α dan β tidak diubah pada tahap ini.
            </p>
        </div>

        <div class="step">
            <h3>5. Ukuran error dan akurasi</h3>
            <div class="formula">
                MAE = (1/n) · Σ |e<sub>t</sub>|<br>
                RMSE = √[(1/n) · Σ e<sub>t</sub><sup>2</sup>]<br>
                MAPE = (100% / n) · Σ |e<sub>t</sub> / Y<sub>t</sub>|
            </div>
            <div class="calc">
                <div class="calc-item">
                    <div class="k">MAE</div>
                    <div class="v">{{ NumberFormat::id($run->mae, 3) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">RMSE</div>
                    <div class="v">{{ NumberFormat::id($run->rmse, 3) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">MAPE</div>
                    <div class="v">{{ $run->mape === null ? '-' : NumberFormat::percent($run->mape) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">Kategori (skala Lewis)</div>
                    <div class="v"><span class="badge badge-{{ $explanation['accuracy']['tone'] }}">{{ $explanation['accuracy']['label'] }}</span></div>
                </div>
            </div>
            <p style="margin-top:10px;font-size:13px;color:var(--muted)">
                MAE dan RMSE memakai satuan {{ $unit }}; MAPE bersifat persentase sehingga mudah
                dibandingkan antar bahan. Semakin kecil nilainya, semakin dekat prediksi dengan aktual.
            </p>
        </div>

        <div class="step">
            <h3>6. Prediksi periode ke depan</h3>
            <p>Karena Holt hanya memodelkan level dan trend, ramalan h langkah ke depan adalah:</p>
            <div class="formula">
                F<sub>n+h</sub> = L<sub>n</sub> + h · T<sub>n</sub><br>
                L<sub>n</sub> = {{ NumberFormat::id($explanation['last_level'], 4) }} ·
                T<sub>n</sub> = {{ NumberFormat::id($explanation['last_trend'], 4) }}
                (posisi akhir {{ NumberFormat::date($explanation['last_period']) }})
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>h</th>
                            <th>Periode</th>
                            <th class="num">Perhitungan</th>
                            <th class="num">Prediksi ({{ $unit }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($future as $index => $row)
                            @php($h = $index + 1)
                            <tr>
                                <td>{{ $h }}</td>
                                <td>{{ NumberFormat::date($row->period_date) }}</td>
                                <td class="num mono" style="font-size:12.5px">{{ NumberFormat::id($explanation['last_level'], 2) }} + {{ $h }} × {{ NumberFormat::id($explanation['last_trend'], 2) }}</td>
                                <td class="num"><strong>{{ NumberFormat::id($row->forecast, 2) }}</strong></td>
                            </tr>
                        @endforeach
                        <tr style="background:var(--surface-2)">
                            <td colspan="3"><strong>Total kebutuhan {{ $run->horizon }} minggu</strong></td>
                            <td class="num"><strong>{{ NumberFormat::id($explanation['total_future'], 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="margin-top:8px;font-size:13px;color:var(--muted)">
                Prediksi negatif tidak mungkin secara fisik, sehingga nilai minimum dibatasi nol.
                Rata-rata prediksi per minggu = {{ NumberFormat::id($explanation['average_future'], 3) }} {{ $unit }}.
            </p>
        </div>

        <div class="step">
            <h3>7. Safety stock</h3>
            <p>
                Safety stock melindungi dari fluktuasi pemakaian dan ketidakpastian lead time.
                Simpangan baku diambil dari error historis model.
            </p>
            <div class="formula">
                SS = Z · σ<sub>e</sub> · √LT<br>
                SS = {{ NumberFormat::id($explanation['z'], 2) }} ×
                {{ NumberFormat::id($explanation['sigma'], 4) }} ×
                √{{ NumberFormat::id($explanation['lead_time_weeks'], 2) }} =
                <strong>{{ NumberFormat::id($explanation['safety_calculation'], 3) }}</strong> {{ $unit }}
            </div>
            <div class="calc">
                <div class="calc-item">
                    <div class="k">Z (service level {{ NumberFormat::id((float) $run->service_level * 100, 0) }}%)</div>
                    <div class="v">{{ NumberFormat::id($explanation['z'], 2) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">σ error (simpangan baku)</div>
                    <div class="v">{{ NumberFormat::id($explanation['sigma'], 4) }}</div>
                </div>
                <div class="calc-item">
                    <div class="k">Lead time</div>
                    <div class="v">{{ $run->ingredient->lead_time_days }} hari ≈ {{ NumberFormat::id($explanation['lead_time_weeks'], 2) }} minggu</div>
                </div>
                <div class="calc-item">
                    <div class="k">Safety stock tersimpan</div>
                    <div class="v">{{ NumberFormat::id($run->safety_stock, 3) }} {{ $unit }}</div>
                </div>
            </div>
            <p style="margin-top:8px;font-size:13px;color:var(--muted)">
                Bila error historis terlalu kecil (σ ≈ 0), sistem memakai cadangan minimum
                20% dari rata-rata prediksi agar rekomendasi tidak menjadi nol.
            </p>
        </div>

        <div class="step">
            <h3>8. Reorder point (ROP)</h3>
            <p>ROP adalah tingkat stok yang memicu pemesanan ulang sebelum barang habis.</p>
            <div class="formula">
                ROP = (rata-rata prediksi per minggu × LT) + SS<br>
                ROP = ({{ NumberFormat::id($explanation['average_future'], 3) }} ×
                {{ NumberFormat::id($explanation['lead_time_weeks'], 2) }}) +
                {{ NumberFormat::id($run->safety_stock, 3) }} =
                <strong>{{ NumberFormat::id($explanation['reorder_calculation'], 3) }}</strong> {{ $unit }}
            </div>
            <p>
                Stok saat ini <strong>{{ NumberFormat::id($run->current_stock, 2) }} {{ $unit }}</strong>
                {{ $run->current_stock <= $run->reorder_point ? 'berada pada atau di bawah ROP, sehingga pemesanan ulang perlu segera dilakukan.' : 'masih di atas ROP, sehingga pemesanan ulang belum mendesak.' }}
            </p>
        </div>

        <div class="step">
            <h3>9. Saran pembelian</h3>
            <p>
                Saran pembelian memastikan kebutuhan satu horizon penuh terpenuhi, sekaligus
                memperhitungkan cadangan, stok fisik, dan barang yang sudah dipesan.
            </p>
            <div class="formula">
                Saran beli = total prediksi + safety stock − stok saat ini − stok dalam perjalanan<br>
                Saran beli = {{ NumberFormat::id($explanation['total_future'], 3) }} +
                {{ NumberFormat::id($run->safety_stock, 3) }} −
                {{ NumberFormat::id($run->current_stock, 3) }} −
                {{ NumberFormat::id($run->on_order, 3) }} =
                <strong>{{ NumberFormat::id($run->recommended_purchase, 3) }}</strong> {{ $unit }}
            </div>
            <p style="margin-bottom:0">
                Nilai negatif berarti stok berlebih; sistem menampilkan saran pembelian sebagai nol.
            </p>
        </div>

        <div class="step" style="border-left-color:var(--accent)">
            <h3>10. Asumsi dan batasan</h3>
            <ul style="margin:0;padding-left:18px">
                <li>Holt memodelkan <strong>level dan trend</strong>; pola musiman tidak dimodelkan. Bila pemakaian sangat musiman, pertimbangkan Holt-Winters.</li>
                <li>Prediksi bergantung pada kualitas dan kelengkapan transaksi pemakaian. Periode tanpa transaksi dianggap nol.</li>
                <li>Agregasi mingguan dipilih agar fluktuasi harian tidak mendominasi; minimal 12 periode diperlukan.</li>
                <li>Safety stock memakai asumsi error berdistribusi normal dan lead time relatif stabil.</li>
                <li>Hasil adalah alat bantu keputusan, bukan pengganti pertimbangan ahli gizi dan kepala SPPG.</li>
            </ul>
        </div>

        @if ($run->notes)
            <div class="note" style="margin-top:16px">
                <strong>Catatan tersimpan:</strong> {{ $run->notes }}
            </div>
        @endif
    </div>

    {{-- Tabel perhitungan --}}
    <details class="disclosure" open>
        <summary>Detail tabel perhitungan ({{ $run->values->count() }} baris)</summary>
        <div class="body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th class="num">Aktual (Y)</th>
                            <th class="num">Level (L)</th>
                            <th class="num">Trend (T)</th>
                            <th class="num">Prediksi (F)</th>
                            <th class="num">Error (e)</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($run->values as $value)
                            <tr>
                                <td>{{ NumberFormat::date($value->period_date) }}</td>
                                <td class="num">{{ $value->actual === null ? '—' : NumberFormat::id($value->actual, 3) }}</td>
                                <td class="num">{{ NumberFormat::id($value->level, 4) }}</td>
                                <td class="num">{{ NumberFormat::id($value->trend, 4) }}</td>
                                <td class="num">{{ NumberFormat::id($value->forecast, 4) }}</td>
                                <td class="num" style="color:{{ $value->error === null ? 'inherit' : ((float) $value->error < 0 ? 'var(--danger)' : 'var(--success)') }}">
                                    {{ $value->error === null ? '—' : NumberFormat::id($value->error, 4) }}
                                </td>
                                <td>
                                    <span class="badge {{ $value->is_future ? 'badge-info' : '' }}">{{ $value->is_future ? 'Prediksi' : 'Historis' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </details>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script>
        (function () {
            const canvas = document.getElementById('forecast-chart');
            if (!canvas || typeof Chart === 'undefined') return;

            const labels = @json($run->values->map(fn ($value) => $value->period_date->format('d/m'))->all());
            const actual = @json($run->values->map(fn ($value) => $value->actual === null ? null : (float) $value->actual)->all());
            const forecast = @json($run->values->map(fn ($value) => (float) $value->forecast)->all());
            const futureStart = {{ $history->count() - 1 }};

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Aktual',
                            data: actual,
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14,165,233,.12)',
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            borderWidth: 2,
                            tension: .25,
                            spanGaps: false
                        },
                        {
                            label: 'Prediksi Holt',
                            data: forecast,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245,158,11,.10)',
                            borderDash: [7, 4],
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            borderWidth: 2,
                            tension: .25
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.parsed.y;
                                    return context.dataset.label + ': ' + (value === null ? '—' : value.toLocaleString('id-ID'));
                                },
                                afterBody: function (items) {
                                    const index = items[0]?.dataIndex ?? 0;
                                    return index > futureStart ? ['Periode prediksi'] : ['Periode historis'];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkipPadding: 12 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148,163,184,.18)' },
                            ticks: {
                                callback: function (value) { return Number(value).toLocaleString('id-ID'); }
                            }
                        }
                    }
                }
            });
        })();
    </script>
@endpush
