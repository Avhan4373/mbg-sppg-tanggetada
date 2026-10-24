@extends('layouts.admin')

@section('title', 'Satuan — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Satuan</h1><p>Satuan dasar yang dipakai pada bahan dan transaksi.</p></div>
        <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm">+ Tambah Satuan</a>
    </div>

    <div class="card tight">
        @if ($units->isEmpty())
            <div class="empty"><strong>Belum ada satuan.</strong>Tambahkan satuan pertama.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Simbol</th><th>Nama</th><th class="num">Jumlah bahan</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($units as $unit)
                            <tr>
                                <td><span class="badge">{{ $unit->symbol }}</span></td>
                                <td><strong>{{ $unit->name }}</strong></td>
                                <td class="num">{{ $unit->ingredients_count }}</td>
                                <td class="actions">
                                    <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" class="inline-form" onsubmit="return confirm('Hapus satuan ini?')">
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
