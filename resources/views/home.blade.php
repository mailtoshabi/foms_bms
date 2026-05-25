<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOMS Academy - Business Management System</title>
    <link rel="manifest" href="{{ url('manifest-home.json') }}">
    <meta name="theme-color" content="#ec1d23">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .bg-bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            list-style: none;
            overflow: hidden;
        }

        .bg-bubbles li {
            position: absolute;
            bottom: -160px;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            animation: rise 20s infinite ease-in;
        }

        .bg-bubbles li:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-duration: 16s; }
        .bg-bubbles li:nth-child(2) { width: 20px; height: 20px; left: 20%; animation-duration: 22s; animation-delay: 2s; }
        .bg-bubbles li:nth-child(3) { width: 50px; height: 50px; left: 35%; animation-duration: 18s; animation-delay: 4s; }
        .bg-bubbles li:nth-child(4) { width: 60px; height: 60px; left: 50%; animation-duration: 24s; animation-delay: 0s; }
        .bg-bubbles li:nth-child(5) { width: 30px; height: 30px; left: 65%; animation-duration: 14s; animation-delay: 3s; }
        .bg-bubbles li:nth-child(6) { width: 70px; height: 70px; left: 75%; animation-duration: 20s; animation-delay: 6s; }
        .bg-bubbles li:nth-child(7) { width: 45px; height: 45px; left: 85%; animation-duration: 26s; animation-delay: 1s; }
        .bg-bubbles li:nth-child(8) { width: 25px; height: 25px; left: 45%; animation-duration: 19s; animation-delay: 5s; }

        @keyframes rise {
            0%   { bottom: -160px; transform: translateX(0) rotate(0deg); opacity: 0; }
            10%  { opacity: 0.04; }
            100% { bottom: 110%; transform: translateX(100px) rotate(720deg); opacity: 0; }
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 700px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.4));
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 1.15rem;
            color: rgba(255, 255, 255, 0.65);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .features {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .feature {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.25rem 1rem 1rem;
            backdrop-filter: blur(10px);
            transition: transform 0.2s, background 0.2s;
            width: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        .feature:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.12);
        }

        .feature-link {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
            text-align: center;
        }

        .feature-link:hover { color: inherit; }

        .feature-icon {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
        }

        .feature h3 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .feature p {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 0;
        }

        .card-install-btn {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            margin-top: 0.85rem;
            width: 100%;
            padding: 0.4rem 0;
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.85);
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .card-install-btn:hover {
            background: rgba(255,255,255,0.18);
            border-color: rgba(255,255,255,0.55);
        }

        .ios-hint {
            display: none;
            font-size: 0.82rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            margin-top: 0.75rem;
        }

        .ios-hint svg { vertical-align: middle; margin: 0 2px; }

        .footer {
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.8rem;
            margin-top: 1rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
        }

        .footer a:hover {
            color: #e94560;
        }

        @media (max-width: 480px) {
            h1 { font-size: 1.75rem; }
            .subtitle { font-size: 1rem; }
            .features { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li>
    </ul>

    <div class="container">
        <div class="logo-icon"><img src="{{ url('images/logo.png') }}" alt="FOMS Academy"></div>
        <h1>FOMS Academy</h1>
        <p class="subtitle">Education Management System
            {{-- <br>Manage your academy with confidence — from daily updates to big insights. --}}
        </p>

        <div class="features">
            {{-- Student Card --}}
            <div class="feature">
                <a href="{{ route('student.login') }}" class="feature-link">
                    <div class="feature-icon">&#x1F393;</div>
                    <h3>Students</h3>
                    <p>Classes &amp; Notes</p>
                </a>
                <button class="card-install-btn" id="install-student" type="button" onclick="this.disabled=true; this.innerText='Installing...';">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v13M8 11l4 4 4-4"/>
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
                    </svg>
                    Install App
                </button>
                <p class="ios-hint" id="ios-hint-student">
                    Tap
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                    → <strong style="color:#93c5fd;">Add to Home Screen</strong>
                </p>
            </div>

            {{-- Teacher Card --}}
            <div class="feature">
                <a href="{{ route('teacher.login') }}" class="feature-link">
                    <div class="feature-icon">&#x1F468;&#x200D;&#x1F3EB;</div>
                    <h3>Teachers</h3>
                    <p>Classes &amp; Students</p>
                </a>
                <button class="card-install-btn" id="install-teacher" type="button" onclick="this.disabled=true; this.innerText='Installing...';">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v13M8 11l4 4 4-4"/>
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
                    </svg>
                    Install App
                </button>
                <p class="ios-hint" id="ios-hint-teacher">
                    Tap
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                    → <strong style="color:#93c5fd;">Add to Home Screen</strong>
                </p>
            </div>
        </div>

        <p class="footer">&copy; {{ date('Y') }} FOMS Academy. All rights reserved. Crafted by <a href="https://webmahal.com" target="_blank" rel="noopener">Web Mahal</a></p>
    </div>
</body>
<script>
(function () {
    'use strict';

    var isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    var installBtns  = document.querySelectorAll('.card-install-btn');
    var deferredPrompt = null;

    // ── Service Worker Registration ──────────────────────────────────────────
    if ('serviceWorker' in navigator) {
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
                .catch(function (err) {
                    console.error('[PWA] SW registration failed:', err);
                });
        @endif
    }

    if (isStandalone) return;

    // ── Android / Chrome ─────────────────────────────────────────────────────
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        installBtns.forEach(function (btn) {
            btn.style.display = 'inline-flex';
        });
    });

    installBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function () {
                deferredPrompt = null;
                installBtns.forEach(function (b) { b.style.display = 'none'; });
            });
        });
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        installBtns.forEach(function (btn) { btn.style.display = 'none'; });
    });

    // ── iOS Safari ───────────────────────────────────────────────────────────
    var isIOS = /ipad|iphone|ipod/i.test(navigator.userAgent) && !window.MSStream;
    if (isIOS && !sessionStorage.getItem('pwa_home_ios_dismissed')) {
        document.querySelectorAll('.ios-hint').forEach(function (el) {
            el.style.display = 'block';
        });
    }

})();
</script>
</html>
