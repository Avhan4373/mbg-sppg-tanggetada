@extends('layouts.admin')

@section('title', ($transaction->exists ? 'Ubah' : 'Catat') . ' Transaksi — SIPRES MBG')

@section('admin-content')
    @php($editing = $transaction->exists)

    <div class="breadcrumb"><a href="{{ route('admin.stock-transactions.index') }}">Transaksi Stok</a><span>/</span>{{ $editing ? 'Ubah' : 'Catat' }}</div>
    <div class="page-head">
        <div>
            <h1>{{ $editing ? 'Ubah Transaksi' : 'Catat Transaksi Stok' }}</h1>
            <p>Gunakan angka positif. Hanya jenis “Pemakaian” yang dipakai sebagai data prediksi.</p>
        </div>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.stock-transactions.update', $transaction) : route('admin.stock-transactions.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="ingredient_id">Bahan makanan</label>
                <select id="ingredient_id" name="ingredient_id" required>
                    <option value="">— Pilih bahan —</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}" @selected((string) old('ingredient_id', $transaction->ingredient_id) === (string) $ingredient->id)>{{ $ingredient->name }} ({{ $ingredient->unit->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="type">Jenis transaksi</label>
                <select id="type" name="type" required>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $transaction->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="quantity">Jumlah</label>
                <input id="quantity" type="number" step="0.001" name="quantity" value="{{ old('quantity', $transaction->quantity) }}" required>
            </div>
            <div class="field">
                <label for="transaction_date">Tanggal transaksi</label>
                <input id="transaction_date" type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date?->toDateString() ?? now()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label for="batch_number">Nomor batch (opsional)</label>
                <input id="batch_number" type="text" name="batch_number" value="{{ old('batch_number', $transaction->batch_number) }}" maxlength="50">
            </div>
            <div class="field">
                <label for="expiry_date">Tanggal kedaluwarsa (opsional)</label>
                <input id="expiry_date" type="date" name="expiry_date" value="{{ old('expiry_date', $transaction->expiry_date?->toDateString()) }}">
            </div>
            <div class="field span-2">
                <label for="note">Catatan</label>
                <textarea id="note" name="note" maxlength="1000">{{ old('note', $transaction->note) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.stock-transactions.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
