<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a3d12">
    <meta name="description" content="@yield('meta-description', 'Kenya Docs — official document templates, CV builder and legal forms for Kenyan citizens.')">

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Variables -->
    <style>
        :root {
            --primary:             #1B5E20;
            --primary-light:       #2E7D32;
            --primary-dark:        #0a3d12;
            --accent:              #E65100;
            --accent-light:        #FFB300;
            --accent-dark:         #BF360C;
            --bg:                  #F5F7F5;
            --surface:             #FFFFFF;
            --text:                #111827;
            --text-muted:          #6B7280;
            --border:              #E5E7EB;
            --error:               #DC2626;
            --success:             #16A34A;
            --radius:              8px;
            --radius-lg:           14px;
            --shadow:              0 1px 4px rgba(0,0,0,.07), 0 4px 12px rgba(0,0,0,.05);
            --shadow-md:           0 4px 16px rgba(0,0,0,.10);
            --shadow-lg:           0 12px 36px rgba(0,0,0,.14);
            --transition:          .18s ease;
            --gradient-hero:       linear-gradient(145deg, #051a07 0%, #0a3d12 40%, #1B5E20 80%, #2E7D32 100%);
            --font-heading:        'Plus Jakarta Sans', system-ui, sans-serif;
            --font-body:           'Inter', system-ui, sans-serif;
        }
    </style>

    <!-- App CSS -->
    <link rel="stylesheet" href="/css/app.css">

    @stack('head')
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="site-header" role="banner" id="site-header">
    <div class="container header-inner">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="logo" aria-label="Kenya Docs Home">
            <img src="/assets/images/logo.jpg" alt="Kenya Docs Logo" class="logo-img" width="38" height="38">
            <span class="logo-text">Kenya<strong>Docs</strong></span>
        </a>

        <!-- Mobile toggle -->
        <button class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-nav">
            <span></span><span></span><span></span>
        </button>

        <!-- Nav -->
        <nav id="main-nav" class="main-nav" role="navigation" aria-label="Main navigation">
            <ul>
                <li>
                    <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>
                        Home
                    </a>
                </li>
                <li>
                    <a href="{{ route('library.index') }}" @class(['active' => request()->routeIs('library.*')])>
                        Library
                    </a>
                </li>
                <li>
                    <a href="{{ route('cv.assistant') }}" @class(['active' => request()->routeIs('cv.assistant')])>
                        CV Assistant
                    </a>
                </li>
                @auth
                    <li>
                        <a href="{{ route('my-documents') }}" @class(['active' => request()->routeIs('my-documents')])>
                            My Documents
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile') }}" @class(['active' => request()->routeIs('profile')])>
                            Profile
                        </a>
                    </li>
                    @if(auth()->user()->is_admin)
                        <li>
                            <a href="{{ route('admin.dashboard') }}"
                               @class(['active' => request()->routeIs('admin.*')])
                               style="color:#FFB300;font-weight:700;">
                                ⚙ Admin
                            </a>
                        </li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('signout') }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-link" style="color:rgba(255,255,255,.75);padding:.45rem .85rem;border-radius:6px;font-size:.92rem;font-weight:500;display:block;">
                                Sign Out
                            </button>
                        </form>
                    </li>
                @else
                    <li>
                        <a href="{{ route('signin') }}"
                           class="btn btn-sm"
                           style="background:var(--accent);color:#fff;border:none;margin-left:.25rem;">
                            Sign In Free
                        </a>
                    </li>
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
    <div class="container">
        <div class="footer-inner">

            <!-- Brand -->
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="logo footer-logo" aria-label="Kenya Docs">
                    <img src="/assets/images/logo.jpg" alt="Kenya Docs Logo" class="logo-img" width="32" height="32">
                    <span class="logo-text">Kenya<strong>Docs</strong></span>
                </a>
                <p class="footer-tagline">Professional Kenyan document templates, legal forms, and ATS-ready CVs — available 24/7 from your phone.</p>
            </div>

            <!-- Quick Links -->
            <div class="footer-links">
                <h4>Documents</h4>
                <ul>
                    <li><a href="{{ route('home') }}">All Templates</a></li>
                    <li><a href="{{ route('library.index') }}">Document Library</a></li>
                    <li><a href="{{ route('cv.assistant') }}">CV Builder</a></li>
                </ul>
            </div>

            <!-- Account Links -->
            <div class="footer-links">
                <h4>Account</h4>
                <ul>
                    @auth
                        <li><a href="{{ route('my-documents') }}">My Documents</a></li>
                        <li><a href="{{ route('profile') }}">My Profile</a></li>
                    @else
                        <li><a href="{{ route('signin') }}">Sign In</a></li>
                        <li><a href="{{ route('signin') }}">Create Account</a></li>
                    @endauth
                    @if(auth()->check() && auth()->user()->is_admin)
                        <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                    @endif
                </ul>
            </div>

        </div>

        <p class="footer-copy">
            &copy; {{ date('Y') }} Kenya Docs. All rights reserved. &nbsp;|&nbsp;
            Serving Kenyans with professional document solutions.
        </p>
    </div>
</footer>

<!-- ===== TOAST ===== -->
<div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true"></div>

<!-- ===== SCRIPTS ===== -->
<script src="/js/api.js"></script>
<script>
(function () {
    // Mobile nav toggle
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav   = document.getElementById('main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!expanded));
            mainNav.classList.toggle('open');
        });
        // Close nav on outside click
        document.addEventListener('click', function (e) {
            if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Scroll-aware header
    const siteHeader = document.getElementById('site-header');
    if (siteHeader) {
        const onScroll = function () {
            siteHeader.classList.toggle('scrolled', window.scrollY > 40);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Global toast helper (overrides api.js showToast if needed)
    window.showToast = function (message, type) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.className = 'toast toast-' + (type || 'info') + ' show';
        toast.textContent = message;
        clearTimeout(toast._t);
        toast._t = setTimeout(function () {
            toast.classList.remove('show');
        }, 4000);
    };

    // PWA service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
}());
</script>

@stack('scripts')
</body>
</html>
