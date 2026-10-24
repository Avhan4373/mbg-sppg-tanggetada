@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', ($menu->exists ? 'Ubah' : 'Tambah') . ' Menu — SIPRES MBG')

@section('admin-content')
    @php($editing = $menu->exists)

    <div class="breadcrumb"><a href="{{ route('admin.menus.index') }}">Menu</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head">
        <div>
            <h1>{{ $editing ? 'Ubah Menu' : 'Tambah Menu' }}</h1>
            <p>Isi kuantitas bahan per porsi. Kosongkan atau isi 0 bila bahan tidak dipakai.</p>
        </div>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nama menu</label>
                <input id="name" type="text" name="name" value="{{ old('name', $menu->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label>Status</label>
                <div class="check-row">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $menu->is_active ?? true))>
                    <label for="is_active">Aktif</label>
                </div>
            </div>
            <div class="field span-2">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" maxlength="1000">{{ old('description', $menu->description) }}</textarea>
            </div>
        </div>

        <h3 class="section-title" style="margin-top:22px">Komposisi per porsi</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Bahan</th><th>Satuan</th><th class="num" style="width:220px">Jumlah per porsi</th></tr>
                </thead>
                <tbody>
                    @foreach ($ingredients as $ingredient)
                        @php($value = old('composition.' . $ingredient->id, $composition[$ingredient->id] ?? null))
                        <tr>
                            <td><strong>{{ $ingredient->name }}</strong></td>
                            <td>{{ $ingredient->unit->symbol ?? '-' }}</td>
                            <td class="num">
                                <input type="number" step="0.0001" min="0" name="composition[{{ $ingredient->id }}]" value="{{ $value }}" placeholder="0" style="text-align:right">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.menus.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
