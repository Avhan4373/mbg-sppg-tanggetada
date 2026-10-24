@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Pengguna — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Pengguna</h1><p>Akun yang dapat mengelola data SPPG.</p></div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Tambah Pengguna</a>
    </div>

    <div class="card tight">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Dibuat</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-brand">{{ match ($user->role) {
                                'admin' => 'Admin SPPG',
                                'ahli_gizi' => 'Ahli Gizi',
                                'kepala_sppg' => 'Kepala SPPG',
                                default => $user->role,
                            } }}</span></td>
                            <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-gray' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>{{ NumberFormat::date($user->created_at) }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-form" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
