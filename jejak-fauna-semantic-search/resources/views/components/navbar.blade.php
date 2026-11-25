<header class="glass sticky top-0 z-50 border-x-0 border-t-0">
    <nav class="container-page flex h-[4.5rem] items-center justify-between gap-5" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="Jejak Fauna Kalimantan, Beranda">
            <span class="grid size-9 place-items-center rounded-full bg-fig-leaf/25 text-fresh-grown" aria-hidden="true">❧</span>
            <span class="hidden text-[.77rem] font-semibold tracking-wide sm:inline">Jejak <span class="text-fresh-grown">Fauna Kalimantan</span></span>
            <span class="text-[.76rem] font-semibold sm:hidden">Jejak Fauna</span>
        </a>
        <div class="hidden items-center gap-1 lg:flex">
            <a class="nav-link {{ request()->routeIs('home') ? 'nav-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="nav-link {{ request()->routeIs('explore.*', 'compare') ? 'nav-active' : '' }}" href="{{ route('explore') }}">Eksplorasi Fauna</a>
            <a class="nav-link {{ request()->routeIs('map') ? 'nav-active' : '' }}" href="{{ route('map') }}">Peta Persebaran</a>
            <a class="nav-link {{ request()->routeIs('graph') ? 'nav-active' : '' }}" href="{{ route('graph') }}">Knowledge Graph</a>
            <a class="nav-link" href="{{ route('home') }}#tentang">Tentang</a>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2" data-auth-guest>
                <a class="hidden rounded-full px-3 py-2 text-xs text-muted transition hover:text-[var(--text)] sm:inline-flex" href="{{ route('login') }}">Masuk</a>
                <a class="btn-primary min-h-9 px-3 py-2 text-xs" href="{{ route('register') }}">Daftar</a>
            </div>
            <div class="relative hidden" data-auth-user>
                <button class="btn-secondary min-h-9 px-3 py-2 text-xs" type="button" data-account-toggle aria-expanded="false" aria-haspopup="true">
                    <span class="grid size-5 place-items-center rounded-full bg-fig-leaf/50">A</span> Akun <span aria-hidden="true">⌄</span>
                </button>
                <div class="glass absolute right-0 top-12 z-50 hidden w-48 rounded-xl p-2 shadow-xl" data-account-menu>
                    <a class="dropdown-link" href="{{ route('profile') }}">Profil saya</a>
                    <a class="dropdown-link" href="{{ route('profile') }}#laporan-saya">Laporan saya</a>
                    <button class="dropdown-link w-full text-left" data-logout type="button">Keluar</button>
                </div>
            </div>
            <button class="grid size-9 place-items-center rounded-full border border-soft text-lg text-fresh-grown transition hover:bg-white/5" data-theme-toggle type="button" aria-label="Aktifkan tema terang" aria-pressed="false">
                <span data-theme-icon aria-hidden="true">☼</span>
            </button>
            <button class="grid size-9 place-items-center rounded-full border border-soft text-lg lg:hidden" data-menu-toggle type="button" aria-expanded="false" aria-controls="menu-mobile" aria-label="Buka navigasi">☰</button>
        </div>
    </nav>
    <div class="hidden border-t border-soft px-4 pb-4 pt-2 lg:hidden" id="menu-mobile" data-mobile-menu>
        <div class="container-page grid gap-1">
            <a class="nav-link {{ request()->routeIs('home') ? 'nav-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="nav-link {{ request()->routeIs('explore.*', 'compare') ? 'nav-active' : '' }}" href="{{ route('explore') }}">Eksplorasi Fauna</a>
            <a class="nav-link {{ request()->routeIs('map') ? 'nav-active' : '' }}" href="{{ route('map') }}">Peta Persebaran</a>
            <a class="nav-link {{ request()->routeIs('graph') ? 'nav-active' : '' }}" href="{{ route('graph') }}">Knowledge Graph</a>
            <a class="nav-link" href="{{ route('home') }}#tentang">Tentang</a>
        </div>
    </div>
</header>
