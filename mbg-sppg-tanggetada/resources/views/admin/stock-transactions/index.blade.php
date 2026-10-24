@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Transaksi Stok — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div>
            <h1>Transaksi Stok</h1>
            <p>Catat stok masuk, pemakaian, bahan terbuang, dan penyesuaian.</p>
        </div>
        <a href="{{ route('admin.stock-transactions.create') }}" class="btn btn-primary btn-sm">+ Catat Transaksi</a>
    </div>

    <div class="card tight" style="margin-bottom:16px">
        <form method="GET" class="form-grid" style="grid-template-columns:1fr 1fr auto;align-items:end">
            <div class="field">
                <label for="type">Jenis</label>
                <select id="type" name="type">
                    <option value="">Semua jenis</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
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
                <a href="{{ route('admin.stock-transactions.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <div class="card tight">
        @if ($transactions->isEmpty())
            <div class="empty"><strong>Belum ada transaksi.</strong>Catat transaksi pertama untuk mulai mengumpulkan data pemakaian.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Bahan</th><th>Jenis</th><th class="num">Jumlah</th><th>Batch</th><th>Catatan</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ NumberFormat::date($transaction->transaction_date) }}</td>
                                <td><strong>{{ $transaction->ingredient->name }}</strong></td>
                                <td>
                                    <span class="badge {{ match ($transaction->type) {
                                        'in' => 'badge-success',
                                        'out' => 'badge-warning',
                                        'waste' => 'badge-danger',
                                        default => 'badge-info',
                                    } }}">{{ $types[$transaction->type] ?? $transaction->type }}</span>
                                </td>
                                <td class="num">{{ NumberFormat::id($transaction->quantity, 3) }} {{ $transaction->ingredient->unit->symbol ?? '' }}</td>
                                <td>{{ $transaction->batch_number ?: '—' }}</td>
                                <td>{{ $transaction->note ?: '—' }}</td>
                                <td class="actions">
                                    <a href="{{ route('admin.stock-transactions.edit', $transaction) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.stock-transactions.destroy', $transaction) }}" class="inline-form" onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $transactions->links('partials.pagination') }}
        @endif
    </div>
@endsection
