<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-logo">🥗</span>
                <span>
                    SIPRES MBG
                    <small>SPPG Kec. Tanggetada</small>
                </span>
            </a>

            <nav class="site-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Prediksi</a>
                <a href="{{ route('guide') }}" class="{{ request()->routeIs('guide') ? 'active' : '' }}">Panduan Metode</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">Kelola Data</a>
                @else
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Masuk</a>
                @endauth
            </nav>

            <div class="header-tools">
                <select id="theme-select" class="theme-select" aria-label="Pilih tema warna">
                    <option value="hijau">Tema Hijau</option>
                    <option value="biru">Tema Biru</option>
                    <option value="oranye">Tema Oranye</option>
                    <option value="ungu">Tema Ungu</option>
                </select>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-ghost" style="color:#fff;border-color:rgba(255,255,255,.35)">Keluar</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</header>
