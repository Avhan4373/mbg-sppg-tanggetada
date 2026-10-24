@extends('layouts.admin')

@section('title', 'Kategori — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Kategori</h1><p>Pengelompokan bahan makanan.</p></div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">+ Tambah Kategori</a>
    </div>

    <div class="card tight">
        @if ($categories->isEmpty())
            <div class="empty"><strong>Belum ada kategori.</strong>Tambahkan kategori pertama.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nama</th><th>Deskripsi</th><th class="num">Jumlah bahan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->description ?: '—' }}</td>
                                <td class="num">{{ $category->ingredients_count }}</td>
                                <td><span class="badge {{ $category->is_active ? 'badge-success' : 'badge-gray' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Hapus kategori ini?')">
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
