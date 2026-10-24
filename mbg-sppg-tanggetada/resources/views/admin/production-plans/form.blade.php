@extends('layouts.admin')

@section('title', ($plan->exists ? 'Ubah' : 'Tambah') . ' Rencana Produksi — SIPRES MBG')

@section('admin-content')
    @php($editing = $plan->exists)

    <div class="breadcrumb"><a href="{{ route('admin.production-plans.index') }}">Rencana Produksi</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head"><h1>{{ $editing ? 'Ubah Rencana Produksi' : 'Tambah Rencana Produksi' }}</h1></div>

    <form method="POST" action="{{ $editing ? route('admin.production-plans.update', $plan) : route('admin.production-plans.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="production_date">Tanggal produksi</label>
                <input id="production_date" type="date" name="production_date" value="{{ old('production_date', $plan->production_date?->toDateString() ?? now()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label for="menu_id">Menu</label>
                <select id="menu_id" name="menu_id" required>
                    <option value="">— Pilih menu —</option>
                    @foreach ($menus as $menu)
                        <option value="{{ $menu->id }}" @selected((string) old('menu_id', $plan->menu_id) === (string) $menu->id)>{{ $menu->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="portions">Jumlah porsi</label>
                <input id="portions" type="number" min="1" step="1" name="portions" value="{{ old('portions', $plan->portions) }}" required>
            </div>
            <div class="field span-2">
                <label for="note">Catatan</label>
                <textarea id="note" name="note" maxlength="1000">{{ old('note', $plan->note) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.production-plans.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
