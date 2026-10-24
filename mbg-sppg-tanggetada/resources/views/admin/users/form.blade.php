@extends('layouts.admin')

@section('title', ($user->exists ? 'Ubah' : 'Tambah') . ' Pengguna — SIPRES MBG')

@section('admin-content')
    @php($editing = $user->exists)

    <div class="breadcrumb"><a href="{{ route('admin.users.index') }}">Pengguna</a><span>/</span>{{ $editing ? 'Ubah' : 'Tambah' }}</div>
    <div class="page-head">
        <div>
            <h1>{{ $editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h1>
            <p>{{ $editing ? 'Biarkan password kosong bila tidak ingin menggantinya.' : 'Password minimal 8 karakter.' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="form-grid">
            <div class="field">
                <label for="name">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" required>
            </div>
            <div class="field">
                <label for="role">Peran</label>
                <select id="role" name="role" required>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin SPPG</option>
                    <option value="ahli_gizi" @selected(old('role', $user->role) === 'ahli_gizi')>Ahli Gizi</option>
                    <option value="kepala_sppg" @selected(old('role', $user->role) === 'kepala_sppg')>Kepala SPPG</option>
                </select>
            </div>
            <div class="field">
                <label for="password">Password {{ $editing ? '(opsional)' : '' }}</label>
                <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" @required(! $editing)>
            </div>
            <div class="field">
                <label>Status</label>
                <div class="check-row">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    <label for="is_active">Aktif</label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
@endsection
