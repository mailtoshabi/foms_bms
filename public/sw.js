/**
 * FOMS BMS – Service Worker
 * Cache version: bump CACHE_VER whenever you deploy new assets.
 */

const CACHE_VER    = 'v1';
const STATIC_CACHE = `foms-static-${CACHE_VER}`;
const DYNAMIC_CACHE = `foms-dynamic-${CACHE_VER}`;

// ── Assets pre-cached on install ─────────────────────────────────────────────
const PRECACHE_URLS = [
    '/offline.html',
    '/assets/css/bootstrap.min.css',
    '/assets/css/icons.min.css',
    '/assets/css/app.min.css',
    '/assets/css/preloader.min.css',
    '/assets/css/global.css',
    '/assets/libs/jquery/jquery.min.js',
    '/assets/libs/bootstrap/bootstrap.min.js',
    '/assets/libs/metismenu/metismenu.min.js',
    '/assets/libs/simplebar/simplebar.min.js',
    '/assets/libs/node-waves/node-waves.min.js',
    '/assets/libs/feather-icons/feather-icons.min.js',
    '/assets/libs/sweetalert2/sweetalert2.min.js',
    '/assets/js/app.min.js',
    '/assets/js/global.js',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

// ── Authenticated/sensitive routes – never cache, always network ──────────────
const NEVER_CACHE_PREFIXES = [
    // Admin portal
    '/admin/dashboard',
    '/admin/students',
    '/admin/teachers',
    '/admin/fees',
    '/admin/payments',
    '/admin/salary',
    '/admin/expenses',
    '/admin/reports',
    '/admin/staff',
    '/admin/classes',
    '/admin/courses',
    '/admin/roles',
    '/admin/messages',
    '/admin/attendance',
    '/admin/leads',
    '/admin/profile',
    '/admin/logout',
    // Staff portal
    '/departments/dashboard',
    '/departments/profile',
    '/departments/logout',
    '/departments/fees',
    '/departments/attendance',
    '/departments/salary',
    '/departments/reports',
    '/departments/messages',
    // Teacher portal
    '/teacher/dashboard',
    '/teacher/profile',
    '/teacher/logout',
    '/teacher/attendance',
    '/teacher/classes',
    '/teacher/messages',
    '/teacher/salary',
    // Student portal
    '/student/dashboard',
    '/student/profile',
    '/student/logout',
    '/student/classes',
    '/student/messages',
    '/student/attendance',
    '/student/fees',
    // Other
    '/all_cache',
    '/admission',
];

// ── Helpers ───────────────────────────────────────────────────────────────────
function isNeverCache(pathname) {
    return NEVER_CACHE_PREFIXES.some(prefix => pathname.startsWith(prefix));
}

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|webp)(\?.*)?$/.test(pathname);
}

// ── Install – pre-cache static assets ────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            // addAll fails on any error; use individual adds so one bad URL
            // doesn't break the whole install.
            return Promise.allSettled(
                PRECACHE_URLS.map(url =>
                    cache.add(url).catch(() => console.warn('[SW] Pre-cache skipped:', url))
                )
            );
        })
    );
    // Activate immediately – do not wait for old SW to be discarded
    self.skipWaiting();
});

// ── Activate – purge stale caches ────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    const validCaches = [STATIC_CACHE, DYNAMIC_CACHE];
    event.waitUntil(
        caches.keys().then((names) =>
            Promise.all(
                names
                    .filter(name => !validCaches.includes(name))
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            )
        )
    );
    self.clients.claim();
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // Only intercept same-origin GET requests
    if (req.method !== 'GET' || url.origin !== location.origin) {
        return;
    }

    // ── Sensitive authenticated routes → network only ─────────────────────
    if (isNeverCache(url.pathname)) {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // ── Static assets (CSS/JS/images/fonts) → cache-first ────────────────
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(req).then((cached) => {
                if (cached) return cached;
                return fetch(req).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(STATIC_CACHE).then(c => c.put(req, clone));
                    }
                    return response;
                }).catch(() => caches.match('/offline.html'));
            })
        );
        return;
    }

    // ── Login / public pages → network-first, fallback to cache ──────────
    if (
        url.pathname === '/' ||
        url.pathname === '/admin' ||
        url.pathname === '/admin/' ||
        url.pathname === '/admin/login'
    ) {
        event.respondWith(
            fetch(req).then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(DYNAMIC_CACHE).then(c => c.put(req, clone));
                }
                return response;
            }).catch(() => caches.match(req).then(c => c || caches.match('/offline.html')))
        );
        return;
    }

    // ── Everything else → network-first, offline fallback ────────────────
    event.respondWith(
        fetch(req).catch(() => caches.match('/offline.html'))
    );
});

// ── Message – allow client to trigger skipWaiting (update flow) ───────────────
self.addEventListener('message', (event) => {
    if (event.data && event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }
});
