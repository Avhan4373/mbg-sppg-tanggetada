@extends('layouts.admin')

@section('title', ($supplier->exists ? 'Ubah' : 'Tambah') . ' Supplier — SIPRES MBG')

@section('admin-content')
    @php($editing = $supplier->exists)

    <div class="breadcrumb"><a href="{{ route('admin.suppliers.index') }}">Supplier</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head"><h1>{{ $editing ? 'Ubah Supplier' : 'Tambah Supplier' }}</h1></div>

    <form method="POST" action="{{ $editing ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nama supplier</label>
                <input id="name" type="text" name="name" value="{{ old('name', $supplier->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label for="phone">Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="30">
            </div>
            <div class="field span-2">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" maxlength="1000">{{ old('address', $supplier->address) }}</textarea>
            </div>
            <div class="field">
                <label>Status</label>
                <div class="check-row">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))>
                    <label for="is_active">Aktif</label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
