// BPMP NTB Service Worker
// Cache strategies: Network First for API, Cache First for static assets

const CACHE_NAME = 'bpmpntb-v1';
const STATIC_CACHE = 'bpmpntb-static-v1';
const API_CACHE = 'bpmpntb-api-v1';
const IMAGE_CACHE = 'bpmpntb-images-v1';

// Static assets to pre-cache on install
const PRECACHE_URLS = [
    '/',
    '/manifest.json',
    '/fonts/quicksand/quicksand.css',
    '/fonts/quicksand/Quicksand.ttf',
    '/vendor/fontawesome/css/all.min.css',
    '/vendor/quill/quill.snow.css',
    '/offline.html',
];

// Install event - pre-cache critical assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    const currentCaches = [CACHE_NAME, STATIC_CACHE, API_CACHE, IMAGE_CACHE];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!currentCaches.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - apply strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip chrome-extension and other non-http
    if (!url.protocol.startsWith('http')) return;

    // API requests: Network First with short cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request, API_CACHE, 5 * 60 * 1000));
        return;
    }

    // Images: Cache First
    if (request.destination === 'image' || url.pathname.match(/\.(png|jpg|jpeg|gif|webp|svg|ico)$/i)) {
        event.respondWith(cacheFirst(request, IMAGE_CACHE));
        return;
    }

    // Static assets (CSS, JS, fonts): Cache First
    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'font' ||
        url.pathname.match(/\.(css|js|woff2?|ttf|eot)$/i) ||
        url.pathname.startsWith('/vendor/') || url.pathname.startsWith('/fonts/')) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // HTML pages: Network First with offline fallback
    if (request.destination === 'document' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstWithOffline(request));
        return;
    }

    // Everything else: Network First
    event.respondWith(networkFirst(request, CACHE_NAME));
});

// Strategy: Cache First - serve from cache, fallback to network
async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        return new Response('Offline', { status: 503 });
    }
}

// Strategy: Network First - try network, fallback to cache
async function networkFirst(request, cacheName, maxAge = 0) {
    const cache = await caches.open(cacheName);
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) {
            // Check if cached response is still fresh
            if (maxAge > 0) {
                const cachedDate = new Date(cached.headers.get('date'));
                if (Date.now() - cachedDate.getTime() > maxAge) {
                    return cached; // Return stale cache better than nothing
                }
            }
            return cached;
        }
        return new Response('Offline', { status: 503 });
    }
}

// Strategy: Network First with offline HTML fallback
async function networkFirstWithOffline(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cache = await caches.open(CACHE_NAME);
        const cached = await cache.match(request);
        if (cached) return cached;

        // Return offline page
        const offline = await cache.match('/offline.html');
        if (offline) return offline;

        return new Response(getOfflineHTML(), {
            headers: { 'Content-Type': 'text/html' },
            status: 200,
        });
    }
}

// Inline offline HTML fallback
function getOfflineHTML() {
    return `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Offline - BPMP NTB</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Quicksand',system-ui,sans-serif;background:#f8fafc;color:#1e293b;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
.box{text-align:center;max-width:400px}
.icon{font-size:64px;margin-bottom:16px;color:#94a3b8}
h1{font-size:24px;font-weight:700;margin-bottom:8px}
p{color:#64748b;margin-bottom:24px;line-height:1.6}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none}
.btn:hover{background:#1d4ed8}
</style>
</head>
<body>
<div class="box">
<div class="icon">&#127760;</div>
<h1>Anda Sedang Offline</h1>
<p>Koneksi internet tidak tersedia. Beberapa halaman mungkin masih bisa diakses dari cache.</p>
<button class="btn" onclick="location.reload()">&#8635; Coba Lagi</button>
</div>
</body>
</html>`;
}

// Listen for messages from client
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});
