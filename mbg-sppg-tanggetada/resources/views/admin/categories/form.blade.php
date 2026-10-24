@extends('layouts.admin')

@section('title', ($category->exists ? 'Ubah' : 'Tambah') . ' Kategori — SIPRES MBG')

@section('admin-content')
    @php($editing = $category->exists)

    <div class="breadcrumb"><a href="{{ route('admin.categories.index') }}">Kategori</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head"><h1>{{ $editing ? 'Ubah Kategori' : 'Tambah Kategori' }}</h1></div>

    <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nama kategori</label>
                <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label>Status</label>
                <div class="check-row">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                    <label for="is_active">Aktif</label>
                </div>
            </div>
            <div class="field span-2">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" maxlength="1000">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
