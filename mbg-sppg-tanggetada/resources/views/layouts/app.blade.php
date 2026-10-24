<!DOCTYPE html>
<html lang="id" data-theme="hijau">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPRES MBG Tanggetada')</title>
    <meta name="description" content="@yield('description', 'Sistem prediksi stok bahan makanan MBG SPPG Kecamatan Tanggetada dengan metode Double Exponential Smoothing (Holt).')">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('sipres-theme');
                if (theme) document.documentElement.dataset.theme = theme;
            } catch (e) {}
        })();
    </script>
    @stack('head')
</head>
<body>
    @include('partials.header')

    <main>
        <div class="container">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <span>&copy; {{ date('Y') }} SIPRES MBG — SPPG Kecamatan Tanggetada</span>
            <span>Metode Double Exponential Smoothing (Holt)</span>
        </div>
    </footer>

    <script>
        (function () {
            var select = document.getElementById('theme-select');
            if (!select) return;
            select.value = document.documentElement.dataset.theme || 'hijau';
            select.addEventListener('change', function () {
                document.documentElement.dataset.theme = this.value;
                try { localStorage.setItem('sipres-theme', this.value); } catch (e) {}
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
