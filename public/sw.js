const CACHE_NAME = 'estateflow-v1';
const OFFLINE_URL = '/estateflow/public/offline';

const STATIC_ASSETS = [
    '/estateflow/public/',
    '/estateflow/public/manifest.json',
    'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
];

// Install — cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch — network first, fallback to cache
self.addEventListener('fetch', event => {
    // Skip non-GET and browser-extension requests
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Cache successful responses for static assets
                if (response && response.status === 200) {
                    const url = event.request.url;
                    if (
                        url.includes('tailwindcss') ||
                        url.includes('font-awesome') ||
                        url.includes('manifest.json') ||
                        url.includes('/icons/')
                    ) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                }
                return response;
            })
            .catch(() => {
                // Return cached version if available
                return caches.match(event.request).then(cached => {
                    if (cached) return cached;
                    // For navigation requests, show offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match('/estateflow/public/') || new Response(
                            '<html><body style="font-family:sans-serif;text-align:center;padding:60px"><h1>You are offline</h1><p>Please check your internet connection.</p></body></html>',
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    }
                });
            })
    );
});
