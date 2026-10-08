<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B5E20">
    <meta name="description" content="Kenya Docs — official document templates for Kenyan citizens">

    <title>@yield('title', 'Kenya Docs') | Kenya Document Assistant</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- CSS -->
    <link rel="stylesheet" href="/css/app.css">

    <!-- CSS Variables & Theme -->
    <style>
        :root {
            --primary:        #1B5E20;
            --primary-light:  #2E7D32;
            --primary-dark:   #145214;
            --accent:         #FF8F00;
            --accent-light:   #FFB300;
            --accent-dark:    #E65100;
            --bg:             #F5F5F5;
            --surface:        #FFFFFF;
            --text:           #212121;
            --text-muted:     #757575;
            --border:         #E0E0E0;
            --error:          #C62828;
            --success:        #2E7D32;
            --radius:         8px;
            --shadow:         0 2px 8px rgba(0,0,0,.12);
            --transition:     0.2s ease;
        }
    </style>

    @stack('head')
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="site-header" role="banner">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="logo" aria-label="Kenya Docs Home">
                <span class="logo-icon">🇰🇪</span>
                <span class="logo-text">Kenya<strong>Docs</strong></span>
            </a>

            <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-nav">
                <span></span><span></span><span></span>
            </button>

            <nav id="main-nav" class="main-nav" role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Home</a></li>
                    <li><a href="{{ route('library.index') }}" @class(['active' => request()->routeIs('library.*')])>Library</a></li>
                    @auth
                        <li><a href="{{ route('my-documents') }}" @class(['active' => request()->routeIs('my-documents')])>My Documents</a></li>
                        <li><a href="{{ route('profile') }}" @class(['active' => request()->routeIs('profile')])>Profile</a></li>
                        @if(auth()->user()->is_admin)
                            <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        @endif
                        <li>
                            <form method="POST" action="/signout" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-link">Sign Out</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('signin') }}" class="btn btn-accent btn-sm">Sign In</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main id="main-content" role="main" tabindex="-1">
        @yield('content')
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="site-footer" role="contentinfo">
        <div class="container footer-inner">
            <div class="footer-brand">
                <span class="logo-icon">🇰🇪</span>
                <span class="logo-text">Kenya<strong>Docs</strong></span>
                <p class="footer-tagline">Official Kenyan document templates at your fingertips.</p>
            </div>
            <nav class="footer-nav" aria-label="Footer navigation">
                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('library.index') }}">Library</a></li>
                    <li><a href="{{ route('signin') }}">Sign In</a></li>
                </ul>
            </nav>
            <p class="footer-copy">&copy; {{ date('Y') }} Kenya Docs. All rights reserved.</p>
        </div>
    </footer>

    <!-- ===== TOAST NOTIFICATION ===== -->
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true"></div>

    <!-- ===== SCRIPTS ===== -->
    <script src="/js/api.js"></script>
    <script>
        // Mobile nav toggle
        const navToggle = document.querySelector('.nav-toggle');
        const mainNav   = document.getElementById('main-nav');
        if (navToggle && mainNav) {
            navToggle.addEventListener('click', function () {
                const expanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', String(!expanded));
                mainNav.classList.toggle('open');
            });
        }
        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
