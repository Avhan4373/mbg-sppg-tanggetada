@extends('layouts.admin')

@use('App\Support\NumberFormat')

@section('title', 'Rencana Produksi — SIPRES MBG')

@section('admin-content')
    <div class="page-head">
        <div><h1>Rencana Produksi</h1><p>Jadwal produksi harian dan jumlah porsi.</p></div>
        <a href="{{ route('admin.production-plans.create') }}" class="btn btn-primary btn-sm">+ Tambah Rencana</a>
    </div>

    <div class="card tight">
        @if ($plans->isEmpty())
            <div class="empty"><strong>Belum ada rencana produksi.</strong>Tambahkan rencana pertama.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Menu</th><th class="num">Porsi</th><th>Dicatat oleh</th><th>Catatan</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($plans as $plan)
                            <tr>
                                <td>{{ NumberFormat::date($plan->production_date) }}</td>
                                <td><strong>{{ $plan->menu->name ?? '-' }}</strong></td>
                                <td class="num">{{ NumberFormat::id($plan->portions, 0) }}</td>
                                <td>{{ $plan->creator->name ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($plan->note, 50) ?: '—' }}</td>
                                <td class="actions">
                                    <a href="{{ route('admin.production-plans.edit', $plan) }}" class="btn btn-ghost btn-sm">Ubah</a>
                                    <form method="POST" action="{{ route('admin.production-plans.destroy', $plan) }}" class="inline-form" onsubmit="return confirm('Hapus rencana ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $plans->links('partials.pagination') }}
        @endif
    </div>
@endsection
