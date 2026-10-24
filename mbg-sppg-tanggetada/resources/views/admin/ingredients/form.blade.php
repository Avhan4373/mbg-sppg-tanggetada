@extends('layouts.admin')

@section('title', ($ingredient->exists ? 'Ubah' : 'Tambah') . ' Bahan — SIPRES MBG')

@section('admin-content')
    @php($editing = $ingredient->exists)

    <div class="breadcrumb">
        <a href="{{ route('admin.ingredients.index') }}">Bahan Makanan</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}
    </div>

    <div class="page-head">
        <div>
            <h1>{{ $editing ? 'Ubah Bahan Makanan' : 'Tambah Bahan Makanan' }}</h1>
            <p>Lengkapi data dasar bahan. Stok aktual tidak diisi di sini, melainkan lewat transaksi stok.</p>
        </div>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.ingredients.update', $ingredient) : route('admin.ingredients.store') }}" class="card">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="field">
                <label for="code">Kode bahan</label>
                <input id="code" type="text" name="code" value="{{ old('code', $ingredient->code) }}" maxlength="30" required>
            </div>
            <div class="field">
                <label for="name">Nama bahan</label>
                <input id="name" type="text" name="name" value="{{ old('name', $ingredient->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $ingredient->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="unit_id">Satuan dasar</label>
                <select id="unit_id" name="unit_id" required>
                    <option value="">— Pilih satuan —</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id', $ingredient->unit_id) === (string) $unit->id)>{{ $unit->name }} ({{ $unit->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">— Tanpa supplier —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $ingredient->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="purchase_price">Harga satuan (Rp)</label>
                <input id="purchase_price" type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $ingredient->purchase_price) }}" required>
            </div>
            <div class="field">
                <label for="minimum_stock">Stok minimum</label>
                <input id="minimum_stock" type="number" step="0.001" min="0" name="minimum_stock" value="{{ old('minimum_stock', $ingredient->minimum_stock) }}" required>
                <span class="hint">Batas yang memicu status kritis.</span>
            </div>
            <div class="field">
                <label for="lead_time_days">Lead time (hari)</label>
                <input id="lead_time_days" type="number" step="1" min="1" max="365" name="lead_time_days" value="{{ old('lead_time_days', $ingredient->lead_time_days) }}" required>
            </div>
            <div class="field">
                <label for="shelf_life_days">Masa simpan (hari, opsional)</label>
                <input id="shelf_life_days" type="number" step="1" min="1" max="3650" name="shelf_life_days" value="{{ old('shelf_life_days', $ingredient->shelf_life_days) }}">
            </div>
            <div class="field">
                <label>Status</label>
                <div class="check-row">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $ingredient->is_active ?? true))>
                    <label for="is_active">Aktif digunakan</label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.ingredients.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
