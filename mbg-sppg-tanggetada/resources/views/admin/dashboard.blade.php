@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Dasbor — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div>
            <h1>Dasbor</h1>
            <p>Ringkasan persediaan dan aktivitas prediksi SPPG Kecamatan Tanggetada.</p>
        </div>
        <a href="{{ route('home') }}#form-prediksi" class="btn btn-primary btn-sm">Buat Prediksi</a>
    </div>

    <div class="grid grid-4" style="margin-bottom:18px">
        <div class="stat">
            <div class="stat-label">Bahan aktif</div>
            <div class="stat-value">{{ $stats['ingredients'] }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Stok kritis</div>
            <div class="stat-value" style="color:{{ $stats['critical'] > 0 ? 'var(--danger)' : 'var(--success)' }}">{{ $stats['critical'] }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Nilai persediaan</div>
            <div class="stat-value" style="font-size:20px">{{ NumberFormat::rupiah($stats['value']) }}</div>
        </div>
        <div class="stat">
            <div class="stat-label">Prediksi tersimpan</div>
            <div class="stat-value">{{ $stats['runs'] }}</div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head">
                <div>
                    <h2>Stok Perlu Perhatian</h2>
                    <p>Bahan pada atau di bawah stok minimum.</p>
                </div>
                <a href="{{ route('admin.ingredients.index') }}" class="btn btn-ghost btn-sm">Semua bahan</a>
            </div>

            @if ($critical->isEmpty())
                <div class="empty"><strong>Tidak ada stok kritis.</strong>Seluruh bahan berada di atas batas minimum.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Bahan</th><th class="num">Stok</th><th class="num">Minimum</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($critical as $ingredient)
                                <tr>
                                    <td>{{ $ingredient->name }} <span style="color:var(--muted)">({{ $ingredient->unit->symbol ?? '' }})</span></td>
                                    <td class="num">{{ NumberFormat::id($ingredient->currentStock(), 2) }}</td>
                                    <td class="num">{{ NumberFormat::id($ingredient->minimum_stock, 2) }}</td>
                                    <td><span class="badge {{ $ingredient->stockStatus() === 'Habis' ? 'badge-danger' : 'badge-warning' }}">{{ $ingredient->stockStatus() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h2>Prediksi Terbaru</h2>
                    <p>Hasil perhitungan Holt terakhir.</p>
                </div>
                <a href="{{ route('admin.forecasts.index') }}" class="btn btn-ghost btn-sm">Riwayat</a>
            </div>

            @if ($recentRuns->isEmpty())
                <div class="empty"><strong>Belum ada prediksi.</strong>Jalankan prediksi dari halaman utama.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Bahan</th><th>Waktu</th><th class="num">MAPE</th><th class="num">Saran beli</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRuns as $run)
                                <tr>
                                    <td><a href="{{ route('predictions.show', $run) }}">{{ $run->ingredient->name }}</a></td>
                                    <td>{{ NumberFormat::dateTime($run->created_at) }}</td>
                                    <td class="num">{{ $run->mape === null ? '-' : NumberFormat::percent($run->mape) }}</td>
                                    <td class="num">{{ NumberFormat::id($run->recommended_purchase, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top:18px">
        <div class="card-head">
            <div>
                <h2>Transaksi Terbaru</h2>
                <p>Delapan transaksi terakhir berdasarkan tanggal.</p>
            </div>
            <a href="{{ route('admin.stock-transactions.index') }}" class="btn btn-ghost btn-sm">Semua transaksi</a>
        </div>

        @if ($recentTransactions->isEmpty())
            <div class="empty"><strong>Belum ada transaksi.</strong>Catat stok masuk dan pemakaian terlebih dahulu.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Bahan</th><th>Jenis</th><th class="num">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $transaction)
                            <tr>
                                <td>{{ NumberFormat::date($transaction->transaction_date) }}</td>
                                <td>{{ $transaction->ingredient->name }}</td>
                                <td><span class="badge">{{ \App\Models\StockTransaction::TYPES[$transaction->type] ?? $transaction->type }}</span></td>
                                <td class="num">{{ NumberFormat::id($transaction->quantity, 3) }} {{ $transaction->ingredient->unit->symbol ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
