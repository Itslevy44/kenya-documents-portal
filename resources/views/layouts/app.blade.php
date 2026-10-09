<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1B5E20">
    <meta name="description" content="Kenya Docs — official document templates for Kenyan citizens">

    <title>@yield('title', 'Kenya Docs') | Kenya Document Assistant</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="/assets/images/favicon.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/assets/images/favicon.jpg">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

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
            --bg:             #F4F6F4;
            --surface:        #FFFFFF;
            --text:           #1A1A1A;
            --text-muted:     #6B7280;
            --border:         #E5E7EB;
            --error:          #C62828;
            --success:        #2E7D32;
            --radius:         10px;
            --radius-lg:      16px;
            --shadow:         0 2px 8px rgba(0,0,0,.10);
            --shadow-md:      0 4px 16px rgba(0,0,0,.12);
            --shadow-lg:      0 8px 32px rgba(0,0,0,.16);
            --card-hover-shadow: 0 12px 36px rgba(27,94,32,.18);
            --transition:     0.2s ease;
            --gradient-hero:  linear-gradient(135deg, #0a3d12 0%, #1B5E20 45%, #2E7D32 100%);
            --font-heading:   'Plus Jakarta Sans', system-ui, sans-serif;
            --font-body:      'Inter', system-ui, sans-serif;
        }
    </style>

    @stack('head')
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="site-header" role="banner" id="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="logo" aria-label="Kenya Docs Home">
                <img src="/assets/images/logo.jpg" alt="Kenya Docs Logo" class="logo-img" width="36" height="36">
                <span class="logo-text">Kenya<strong>Docs</strong></span>
            </a>

            <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-nav">
                <span></span><span></span><span></span>
            </button>

            <nav id="main-nav" class="main-nav" role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Home</a></li>
                    <li><a href="{{ route('library.index') }}" @class(['active' => request()->routeIs('library.*')])>Library</a></li>
                    <li><a href="/cv-assistant" @class(['active' => request()->is('cv-assistant')])>CV Assistant</a></li>
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
                <a href="{{ route('home') }}" class="logo footer-logo" aria-label="Kenya Docs">
                    <img src="/assets/images/logo.jpg" alt="Kenya Docs Logo" class="logo-img" width="30" height="30">
                    <span class="logo-text">Kenya<strong>Docs</strong></span>
                </a>
                <p class="footer-tagline">Official Kenyan document templates at your fingertips.</p>
            </div>
            <nav class="footer-nav" aria-label="Footer navigation">
                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('library.index') }}">Library</a></li>
                    <li><a href="/cv-assistant">CV Assistant</a></li>
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

        // Glassmorphism header on scroll
        const siteHeader = document.getElementById('site-header');
        if (siteHeader) {
            window.addEventListener('scroll', function () {
                if (window.scrollY > 40) {
                    siteHeader.classList.add('scrolled');
                } else {
                    siteHeader.classList.remove('scrolled');
                }
            }, { passive: true });
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
