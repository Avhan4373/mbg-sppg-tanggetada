@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Riwayat Prediksi — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Riwayat Prediksi</h1><p>Seluruh perhitungan Holt yang tersimpan.</p></div>
        <a href="{{ route('home') }}#form-prediksi" class="btn btn-primary btn-sm">+ Prediksi Baru</a>
    </div>

    <div class="card tight" style="margin-bottom:16px">
        <form method="GET" class="form-grid" style="grid-template-columns:1fr auto;align-items:end">
            <div class="field">
                <label for="ingredient">Bahan</label>
                <select id="ingredient" name="ingredient">
                    <option value="">Semua bahan</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" @selected((string) request('ingredient') === (string) $ingredient->id)>{{ $ingredient->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.forecasts.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <div class="card tight">
        @if ($runs->isEmpty())
            <div class="empty"><strong>Belum ada riwayat.</strong>Jalankan prediksi dari halaman utama.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Waktu</th><th>Bahan</th><th class="num">α</th><th class="num">β</th><th class="num">MAPE</th><th class="num">Saran beli</th><th>Akurasi</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($runs as $run)
                            <tr>
                                <td>{{ NumberFormat::dateTime($run->created_at) }}</td>
                                <td><strong>{{ $run->ingredient->name }}</strong></td>
                                <td class="num">{{ NumberFormat::id($run->alpha, 2) }}</td>
                                <td class="num">{{ NumberFormat::id($run->beta, 2) }}</td>
                                <td class="num">{{ $run->mape === null ? '-' : NumberFormat::percent($run->mape) }}</td>
                                <td class="num">{{ NumberFormat::id($run->recommended_purchase, 2) }} {{ $run->ingredient->unit->symbol ?? '' }}</td>
                                <td><span class="badge badge-{{ $run->accuracyCategory()['tone'] }}">{{ $run->accuracyCategory()['label'] }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('predictions.show', $run) }}" class="btn btn-ghost btn-sm">Detail</a>
                                    <form method="POST" action="{{ route('admin.forecasts.destroy', $run) }}" class="inline-form" onsubmit="return confirm('Hapus riwayat prediksi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $runs->links('partials.pagination') }}
        @endif
    </div>
@endsection
