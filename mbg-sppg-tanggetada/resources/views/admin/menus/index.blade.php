@extends('layouts.admin')

@section('title', 'Menu & Komposisi — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Menu &amp; Komposisi</h1><p>Menu MBG beserta kebutuhan bahan per porsi.</p></div>
        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">+ Tambah Menu</a>
    </div>

    <div class="card tight">
        @if ($menus->isEmpty())
            <div class="empty"><strong>Belum ada menu.</strong>Tambahkan menu beserta komposisi bahannya.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nama menu</th><th>Deskripsi</th><th class="num">Bahan</th><th class="num">Rencana produksi</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($menus as $menu)
                            <tr>
                                <td><strong>{{ $menu->name }}</strong></td>
                                <td>{{ \Illuminate\Support\Str::limit($menu->description, 60) ?: '—' }}</td>
                                <td class="num">{{ $menu->ingredients_count }}</td>
                                <td class="num">{{ $menu->production_plans_count }}</td>
                                <td><span class="badge {{ $menu->is_active ? 'badge-success' : 'badge-gray' }}">{{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" class="inline-form" onsubmit="return confirm('Hapus menu ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
