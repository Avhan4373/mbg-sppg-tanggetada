@extends('layouts.admin')

@section('title', ($unit->exists ? 'Ubah' : 'Tambah') . ' Satuan — SIPRES MBG')

@section('admin-content')
    @php($editing = $unit->exists)

    <div class="breadcrumb"><a href="{{ route('admin.units.index') }}">Satuan</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head"><h1>{{ $editing ? 'Ubah Satuan' : 'Tambah Satuan' }}</h1></div>

    <form method="POST" action="{{ $editing ? route('admin.units.update', $unit) : route('admin.units.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nama satuan</label>
                <input id="name" type="text" name="name" value="{{ old('name', $unit->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label for="symbol">Simbol</label>
                <input id="symbol" type="text" name="symbol" value="{{ old('symbol', $unit->symbol) }}" maxlength="20" required>
                <span class="hint">Contoh: kg, liter, ikat.</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.units.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
