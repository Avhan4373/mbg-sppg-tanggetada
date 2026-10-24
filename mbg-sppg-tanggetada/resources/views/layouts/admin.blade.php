@extends('layouts.app')

@section('content')
    <div class="admin-shell">
        <aside class="admin-side no-print">
            <div class="side-title">Ringkasan</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dasbor</a>
            <a href="{{ route('admin.forecasts.index') }}" class="{{ request()->routeIs('admin.forecasts.*') ? 'active' : '' }}">🔮 Riwayat Prediksi</a>

            <hr>
            <div class="side-title">Operasional</div>
            <a href="{{ route('admin.stock-transactions.index') }}" class="{{ request()->routeIs('admin.stock-transactions.*') ? 'active' : '' }}">🔄 Transaksi Stok</a>
            <a href="{{ route('admin.menus.index') }}" class="{{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">🍽️ Menu &amp; Komposisi</a>
            <a href="{{ route('admin.production-plans.index') }}" class="{{ request()->routeIs('admin.production-plans.*') ? 'active' : '' }}">📅 Rencana Produksi</a>

            <hr>
            <div class="side-title">Master Data</div>
            <a href="{{ route('admin.ingredients.index') }}" class="{{ request()->routeIs('admin.ingredients.*') ? 'active' : '' }}">🥕 Bahan Makanan</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">🏷️ Kategori</a>
            <a href="{{ route('admin.units.index') }}" class="{{ request()->routeIs('admin.units.*') ? 'active' : '' }}">⚖️ Satuan</a>
            <a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">🚚 Supplier</a>

            @if (auth()->user()?->isAdmin())
                <hr>
                <div class="side-title">Sistem</div>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">👥 Pengguna</a>
            @endif

            <hr>
            <a href="{{ route('home') }}">🌐 Lihat Halaman Prediksi</a>
        </aside>

        <div>
            @yield('admin-content')
        </div>
    </div>
@endsection
