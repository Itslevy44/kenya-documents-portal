/**
 * sw.js — Kenya Docs Service Worker
 *
 * Strategy:
 *  - Precache static shell assets on install
 *  - Network-first for /api/* routes (always fresh data)
 *  - Cache-first for static assets (CSS, JS, images)
 *  - Network-first for all other navigation requests
 */

const CACHE_NAME    = 'kenya-docs-v1';
const API_CACHE     = 'kenya-docs-api-v1';

const PRECACHE_URLS = [
    '/',
    '/css/app.css',
    '/js/api.js',
    '/js/home.js',
    '/manifest.json',
];

// ---------- Install: precache static shell ----------
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
    );
    self.skipWaiting();
});

// ---------- Activate: clean old caches ----------
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => k !== CACHE_NAME && k !== API_CACHE)
                    .map((k) => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ---------- Fetch ----------
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin GET requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    // API routes: network-first, no persistent cache (fresh data required)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request, API_CACHE));
        return;
    }

    // Static assets (css, js, images, fonts): cache-first
    if (/\.(css|js|woff2?|ttf|png|jpg|jpeg|gif|svg|ico|webp)$/i.test(url.pathname)) {
        event.respondWith(cacheFirst(request, CACHE_NAME));
        return;
    }

    // HTML pages / navigation: network-first
    event.respondWith(networkFirst(request, CACHE_NAME));
});

// ---------- Strategies ----------

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached || caches.match('/');
    }
}

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}
