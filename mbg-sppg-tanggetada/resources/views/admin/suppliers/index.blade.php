@extends('layouts.admin')

@section('title', 'Supplier — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Supplier</h1><p>Pemasok bahan makanan SPPG.</p></div>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">+ Tambah Supplier</a>
    </div>

    <div class="card tight">
        @if ($suppliers->isEmpty())
            <div class="empty"><strong>Belum ada supplier.</strong>Tambahkan supplier pertama.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th class="num">Bahan</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td><strong>{{ $supplier->name }}</strong></td>
                                <td>{{ $supplier->phone ?: '—' }}</td>
                                <td>{{ $supplier->address ?: '—' }}</td>
                                <td class="num">{{ $supplier->ingredients_count }}</td>
                                <td><span class="badge {{ $supplier->is_active ? 'badge-success' : 'badge-gray' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="actions">
                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" class="inline-form" onsubmit="return confirm('Nonaktifkan supplier ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Nonaktifkan</button>
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
