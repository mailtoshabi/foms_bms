<script>
    /**
 * FOMS BMS – PWA Handler
 * Responsibilities:
 *   1. Register /sw.js service worker
 *   2. Capture beforeinstallprompt (Android/Chrome install banner)
 *   3. Show iOS "Add to Home Screen" instructions
 *   4. Trigger page reload when a new SW version activates
 */
(function () {
    'use strict';

    // ── 1. Service Worker Registration ───────────────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            @if(config('app.env') === 'local')
                // Unregister any active service worker in local development to avoid stale caching & false offline errors
                navigator.serviceWorker.getRegistrations().then(function (registrations) {
                    for (var registration of registrations) {
                        registration.unregister().then(function (unregistered) {
                            if (unregistered) {
                                console.log('[PWA] Unregistered service worker in local development:', registration);
                            }
                        });
                    }
                });
            @else
                navigator.serviceWorker
                    .register('{{ url('sw.js') }}', { scope: '/' })
                    .then(function (registration) {
                        // Listen for an updated SW being installed in the background
                        registration.addEventListener('updatefound', function () {
                            var newWorker = registration.installing;
                            if (!newWorker) return;

                            newWorker.addEventListener('statechange', function () {
                                // When new SW is installed and old SW is still controlling,
                                // send skipWaiting so new SW activates immediately.
                                if (
                                    newWorker.state === 'installed' &&
                                    navigator.serviceWorker.controller
                                ) {
                                    newWorker.postMessage({ action: 'skipWaiting' });
                                }
                            });
                        });
                    })
                    .catch(function (err) {
                        console.error('[PWA] Service Worker registration failed:', err);
                    });

                // When a new SW takes control, reload so users get the latest version
                var refreshing = false;
                navigator.serviceWorker.addEventListener('controllerchange', function () {
                    if (!refreshing) {
                        refreshing = true;
                        window.location.reload();
                    }
                });
            @endif
        });
    }

    // ── 2. Install Prompt (Android / Chrome / Edge) ───────────────────────────
    var deferredPrompt  = null;
    var installBanner   = document.getElementById('pwa-install-banner');
    var installBtn      = document.getElementById('pwa-install-btn');
    var dismissBtn      = document.getElementById('pwa-install-dismiss');

    // Detect standalone (already installed)
    var isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    // Hide banner if already installed
    if (isStandalone && installBanner) {
        installBanner.style.display = 'none';
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();          // Stop default mini-info bar
        deferredPrompt = e;

        if (installBanner && !isStandalone && !sessionStorage.getItem('pwa_android_dismissed')) {
            installBanner.classList.remove('d-none');
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (!deferredPrompt) return;

            deferredPrompt.prompt();

            deferredPrompt.userChoice.then(function (choiceResult) {
                deferredPrompt = null;
                if (installBanner) installBanner.classList.add('d-none');
                if (choiceResult.outcome === 'accepted') {
                    console.log('[PWA] User accepted the install prompt');
                    sessionStorage.setItem('pwa_android_dismissed', '1');
                }
            });
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            if (installBanner) installBanner.classList.add('d-none');
            sessionStorage.setItem('pwa_android_dismissed', '1');
        });
    }

    // Hide banner when app is installed via the prompt
    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        if (installBanner) installBanner.classList.add('d-none');
        sessionStorage.setItem('pwa_android_dismissed', '1');
        console.log('[PWA] App installed successfully');
    });

    // ── 3. iOS "Add to Home Screen" Banner ───────────────────────────────────
    var isIOS    = /ipad|iphone|ipod/i.test(navigator.userAgent) && !window.MSStream;
    var iosBanner = document.getElementById('pwa-ios-banner');
    var iosDismiss = document.getElementById('pwa-ios-dismiss');

    if (isIOS && !isStandalone && iosBanner) {
        // Only show once per session (use sessionStorage to track)
        if (!sessionStorage.getItem('pwa_ios_dismissed')) {
            iosBanner.classList.remove('d-none');
        }
    }

    if (iosDismiss) {
        iosDismiss.addEventListener('click', function () {
            if (iosBanner) iosBanner.classList.add('d-none');
            sessionStorage.setItem('pwa_ios_dismissed', '1');
        });
    }

    // ── 4. Standalone display-mode check ─────────────────────────────────────
    window.matchMedia('(display-mode: standalone)').addEventListener('change', function (e) {
        if (e.matches) {
            // Running as installed app – hide all install UI
            if (installBanner) installBanner.classList.add('d-none');
            if (iosBanner) iosBanner.classList.add('d-none');
        }
    });

})();

</script>
