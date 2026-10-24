@extends('layouts.app')

@use('App\Support\NumberFormat')

@section('title', 'Prediksi Stok MBG — SPPG Kecamatan Tanggetada')

@section('content')
    <section class="hero">
        <span class="eyebrow">Program Makanan Bergizi Gratis</span>
        <h1>Prediksi Stok Bahan Makanan SPPG Kecamatan Tanggetada</h1>
        <p>
            Sistem ini menghitung perkiraan kebutuhan bahan makanan mingguan dengan
            <strong>Double Exponential Smoothing (Holt)</strong>. Parameter α dan β dipilih otomatis
            melalui validasi data, lalu hasilnya diterjemahkan menjadi safety stock, reorder point,
            dan saran pembelian.
        </p>
        <div class="hero-actions">
            <a href="#form-prediksi" class="btn btn-primary">Mulai Prediksi</a>
            <a href="{{ route('guide') }}" class="btn btn-ghost">Pelajari Perhitungannya</a>
        </div>
    </section>

    <div class="grid grid-4" style="margin-bottom:22px">
        <div class="stat">
            <div class="stat-label">Bahan aktif</div>
            <div class="stat-value">{{ $stats['ingredients'] }}</div>
            <div class="stat-sub">jenis bahan makanan</div>
        </div>
        <div class="stat">
            <div class="stat-label">Prediksi tersimpan</div>
            <div class="stat-value">{{ $stats['runs'] }}</div>
            <div class="stat-sub">riwayat perhitungan</div>
        </div>
        <div class="stat">
            <div class="stat-label">Stok kritis</div>
            <div class="stat-value" style="color:{{ $stats['critical'] > 0 ? 'var(--danger)' : 'var(--success)' }}">{{ $stats['critical'] }}</div>
            <div class="stat-sub">pada / di bawah batas minimum</div>
        </div>
        <div class="stat">
            <div class="stat-label">Pemakaian 4 minggu</div>
            <div class="stat-value">{{ NumberFormat::compact($stats['usage'], 1) }}</div>
            <div class="stat-sub">total kuantitas bahan keluar</div>
        </div>
    </div>

    <div class="grid grid-sidebar" style="grid-template-columns:minmax(0,1.65fr) minmax(280px,.75fr)">
        <div class="card" id="form-prediksi">
            <div class="card-head">
                <div>
                    <h2>Hitung Prediksi Baru</h2>
                    <p>Minimal 12 minggu data pemakaian. Data diambil dari transaksi berjenis “Pemakaian”.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('predictions.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field span-2">
                        <label for="ingredient_id">Bahan makanan</label>
                        <select id="ingredient_id" name="ingredient_id" required>
                            <option value="">— Pilih bahan —</option>
                            @foreach ($ingredients->groupBy('category.name') as $category => $items)
                                <optgroup label="{{ $category }}">
                                    @foreach ($items as $ingredient)
                                        <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id') === (string) $ingredient->id)>
                                            {{ $ingredient->name }} ({{ $ingredient->unit->symbol ?? '-' }}) — stok {{ NumberFormat::id($ingredient->currentStock(), 2) }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <span class="hint">Stok saat ini dihitung otomatis dari seluruh transaksi.</span>
                    </div>

                    <div class="field">
                        <label for="start_date">Tanggal awal data</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $defaultStart->toDateString()) }}" required>
                        <span class="hint">Awal minggu pengamatan.</span>
                    </div>

                    <div class="field">
                        <label for="end_date">Tanggal akhir data</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $defaultEnd->toDateString()) }}" required>
                        <span class="hint">Gunakan data yang sudah selesai (bukan minggu berjalan).</span>
                    </div>

                    <div class="field">
                        <label for="horizon">Horizon prediksi</label>
                        <select id="horizon" name="horizon" required>
                            @foreach ([1 => '1 minggu', 2 => '2 minggu', 4 => '4 minggu', 6 => '6 minggu', 8 => '8 minggu', 12 => '12 minggu'] as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('horizon', 4) === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="hint">Jumlah periode ke depan yang diprediksi.</span>
                    </div>

                    <div class="field">
                        <label for="service_level">Service level</label>
                        <select id="service_level" name="service_level" required>
                            <option value="0.90" @selected(old('service_level', '0.95') === '0.90')>90% (Z = 1,28)</option>
                            <option value="0.95" @selected(old('service_level', '0.95') === '0.95')>95% (Z = 1,65)</option>
                            <option value="0.99" @selected(old('service_level', '0.95') === '0.99')>99% (Z = 2,33)</option>
                        </select>
                        <span class="hint">Tingkat layanan menentukan besarnya safety stock.</span>
                    </div>

                    <div class="field">
                        <label for="on_order">Stok dalam perjalanan (opsional)</label>
                        <input id="on_order" type="number" step="0.001" min="0" name="on_order" value="{{ old('on_order', 0) }}">
                        <span class="hint">Barang sudah dipesan tetapi belum diterima.</span>
                    </div>

                    <div class="field">
                        <label for="note">Catatan (opsional)</label>
                        <input id="note" type="text" name="note" value="{{ old('note') }}" maxlength="500" placeholder="Contoh: persiapan menu pekan depan">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Hitung Prediksi</button>
                    <a href="{{ route('guide') }}" class="btn btn-ghost">Lihat rumus &amp; penjelasan</a>
                </div>
            </form>
        </div>

        <div class="grid" style="gap:14px">
            <div class="card tight">
                <div class="section-title">Cara kerja singkat</div>
                <ol style="margin:0;padding-left:18px;font-size:13.5px;line-height:1.8">
                    <li>Transaksi pemakaian dijumlahkan per minggu.</li>
                    <li>Sistem mencari α dan β terbaik (0,01–0,99) dengan validasi.</li>
                    <li>Level dan trend dihitung ulang dengan seluruh data.</li>
                    <li>Prediksi <em>h</em> minggu ke depan: <strong>L + h·T</strong>.</li>
                    <li>Hasil diterjemahkan menjadi safety stock, ROP, dan saran beli.</li>
                </ol>
            </div>

            @if ($latest)
                <div class="card tight">
                    <div class="section-title">Prediksi terakhir</div>
                    <div style="font-weight:700;font-size:16px">{{ $latest->ingredient->name }}</div>
                    <div style="color:var(--muted);font-size:12.5px;margin-bottom:10px">
                        {{ NumberFormat::dateTime($latest->created_at) }} · horizon {{ $latest->horizon }} minggu
                    </div>
                    <div class="kv" style="grid-template-columns:1fr auto;font-size:13px">
                        <dt>Saran beli</dt>
                        <dd>{{ NumberFormat::id($latest->recommended_purchase, 2) }} {{ $latest->ingredient->unit->symbol ?? '' }}</dd>
                        <dt>MAPE</dt>
                        <dd>{{ $latest->mape === null ? '-' : NumberFormat::percent($latest->mape) }}</dd>
                        <dt>Akurasi</dt>
                        <dd><span class="badge badge-{{ $latest->accuracyCategory()['tone'] }}">{{ $latest->accuracyCategory()['label'] }}</span></dd>
                    </div>
                    <a href="{{ route('predictions.show', $latest) }}" class="btn btn-ghost btn-sm btn-block" style="margin-top:12px">Buka detail &amp; perhitungan</a>
                </div>
            @endif

            <div class="note">
                Halaman ini dapat langsung dibuka tanpa login. Login hanya diperlukan untuk
                mengelola master data dan transaksi.
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:22px">
        <div class="card-head">
            <div>
                <h2>Riwayat Prediksi Terakhir</h2>
                <p>Klik salah satu baris untuk melihat grafik dan rincian perhitungan lengkap.</p>
            </div>
            @auth
                <a href="{{ route('admin.forecasts.index') }}" class="btn btn-ghost btn-sm">Kelola riwayat</a>
            @endauth
        </div>

        @if ($recentRuns->isEmpty())
            <div class="empty">
                <strong>Belum ada prediksi.</strong>
                Pilih bahan makanan pada formulir di atas untuk memulai perhitungan pertama.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Bahan</th>
                            <th class="num">α</th>
                            <th class="num">β</th>
                            <th class="num">MAPE</th>
                            <th class="num">Safety stock</th>
                            <th class="num">Saran beli</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentRuns as $run)
                            <tr>
                                <td>{{ NumberFormat::dateTime($run->created_at) }}</td>
                                <td>
                                    <strong>{{ $run->ingredient->name }}</strong><br>
                                    <span style="color:var(--muted);font-size:12px">{{ $run->horizon }} minggu ke depan</span>
                                </td>
                                <td class="num">{{ NumberFormat::id($run->alpha, 2) }}</td>
                                <td class="num">{{ NumberFormat::id($run->beta, 2) }}</td>
                                <td class="num">{{ $run->mape === null ? '-' : NumberFormat::percent($run->mape) }}</td>
                                <td class="num">{{ NumberFormat::id($run->safety_stock, 2) }}</td>
                                <td class="num"><strong>{{ NumberFormat::id($run->recommended_purchase, 2) }}</strong></td>
                                <td class="actions">
                                    <a href="{{ route('predictions.show', $run) }}" class="btn btn-ghost btn-sm">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
