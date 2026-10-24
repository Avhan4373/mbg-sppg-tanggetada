@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Bahan Makanan — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div>
            <h1>Bahan Makanan</h1>
            <p>Master bahan, stok minimum, lead time, dan harga satuan.</p>
        </div>
        <a href="{{ route('admin.ingredients.create') }}" class="btn btn-primary btn-sm">+ Tambah Bahan</a>
    </div>

    <div class="card tight" style="margin-bottom:16px">
        <form method="GET" class="form-grid" style="grid-template-columns:2fr 1fr auto;align-items:end">
            <div class="field">
                <label for="q">Cari</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama atau kode bahan">
            </div>
            <div class="field">
                <label for="category">Kategori</label>
                <select id="category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.ingredients.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <div class="card tight">
        @if ($ingredients->isEmpty())
            <div class="empty"><strong>Tidak ada bahan.</strong>Tambahkan bahan makanan terlebih dahulu.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th class="num">Stok</th>
                            <th class="num">Minimum</th>
                            <th class="num">Lead time</th>
                            <th class="num">Harga</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ingredients as $ingredient)
                            <tr>
                                <td><span class="badge">{{ $ingredient->code }}</span></td>
                                <td><strong>{{ $ingredient->name }}</strong></td>
                                <td>{{ $ingredient->category->name ?? '-' }}</td>
                                <td>{{ $ingredient->unit->symbol ?? '-' }}</td>
                                <td class="num">{{ NumberFormat::id($ingredient->currentStock(), 2) }}</td>
                                <td class="num">{{ NumberFormat::id($ingredient->minimum_stock, 2) }}</td>
                                <td class="num">{{ $ingredient->lead_time_days }} hari</td>
                                <td class="num">{{ NumberFormat::rupiah($ingredient->purchase_price) }}</td>
                                <td>
                                    <span class="badge {{ match ($ingredient->stockStatus()) {
                                        'Aman' => 'badge-success',
                                        'Kritis' => 'badge-warning',
                                        default => 'badge-danger',
                                    } }}">{{ $ingredient->stockStatus() }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('admin.ingredients.edit', $ingredient) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.ingredients.destroy', $ingredient) }}" class="inline-form" onsubmit="return confirm('Hapus bahan ini?')">
                                        @csrf
                                        @method('DELETE')
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
