<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | FOMS ACADEMY Business Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="FOMS ACADEMY Business Management System" name="description" />
    <meta content="Web Mahal Web Service" name="author" />
    <!-- Standard favicon -->
    <link rel="icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">

    <!-- For modern browsers -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">

    <!-- Apple Touch Icon (iPhone/iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">

    <!-- Android Chrome Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/favicon/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/favicon/android-chrome-512x512.png') }}">

    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="#ec1d23">
    <meta name="msapplication-TileImage" content="{{ asset('assets/favicon/android-chrome-192x192.png') }}">

    <!-- Theme Color (browser UI) -->
    <meta name="theme-color" content="#ec1d23">

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest-student.json') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FOMS BMS">
    <link rel="apple-touch-icon" sizes="72x72"  href="{{ asset('images/icons/icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="96x96"  href="{{ asset('images/icons/icon-96x96.png') }}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{ asset('images/icons/icon-128x128.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="384x384" href="{{ asset('images/icons/icon-384x384.png') }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('images/icons/icon-512x512.png') }}">

    @include('student.layouts.head-css')
    <style>
        body[data-layout=horizontal] .page-content {
            margin-top: 20px;
        }
    </style>

</head>

@section('body')
    @include('student.layouts.body')
@show

    <!-- Begin page -->
    <div id="layout-wrapper">
 <body data-layout="horizontal">

        @include('student.layouts.horizontal')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <!-- Start content -->
                <div class="container-fluid">
                    @yield('content')
                </div> <!-- content -->
            </div>
            @include('student.layouts.footer')
        </div>
        <!-- ============================================================== -->
        <!-- End Right content here -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- Right Sidebars -->
    @include('student.layouts.right-sidebar-messages')
    @include('student.layouts.right-sidebar-sessions')
    
    <script>
        document.querySelectorAll('.right-bar-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                if (target) {
                    // Hide all sidebars first
                    document.querySelectorAll('.right-bar').forEach(function(bar) {
                        bar.style.display = 'none';
                    });
                    // Show the target sidebar
                    const targetBar = document.getElementById(target);
                    if (targetBar) {
                        targetBar.style.display = 'block';
                    }
                }
            });
        });
    </script>
    <!-- END Right Sidebars -->

    <x-pwa-install-button />
    @include('student.layouts.vendor-scripts')

    @auth('student')
    <!-- Buzzer Alert Overlay -->
    <div id="globalBuzzerOverlay" style="display:none; position: fixed; z-index: 10000; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; max-width: 450px; width: 90%; padding: 24px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); border: 2px solid #ec1d23; animation: pulseBorder 2s infinite;">
            <div style="width: 70px; height: 70px; background: rgba(236, 29, 35, 0.1); color: #ec1d23; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; font-size: 28px; animation: ringBell 0.5s infinite alternate;">
                <i class="fas fa-bell"></i>
            </div>
            <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 8px;">Class Join Reminder!</h4>
            <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Your teacher is buzzing you to join the class session immediately.</p>
            <div style="display: flex; gap: 12px;">
                <button id="closeBuzzerBtn" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #64748b; font-weight: 600; cursor: pointer;">Dismiss</button>
                <a id="joinBuzzerLink" href="#" target="_blank" style="flex: 1; padding: 10px; border-radius: 8px; border: none; background: #ec1d23; color: white; text-decoration: none; font-weight: 600; cursor: pointer; text-align: center;">Join Class</a>
            </div>
        </div>
    </div>
    <style>
        @keyframes pulseBorder {
            0% { border-color: #ec1d23; }
            50% { border-color: rgba(236, 29, 35, 0.3); }
            100% { border-color: #ec1d23; }
        }
        @keyframes ringBell {
            0% { transform: rotate(-15deg); }
            100% { transform: rotate(15deg); }
        }
    </style>

    <script>
        let audioCtx = null;
        function initAudioContext() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }
        document.addEventListener('click', initAudioContext, { once: true });
        document.addEventListener('touchstart', initAudioContext, { once: true });

        function playBuzzerSound() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                
                let duration = 0.4;
                let numBeeps = 3;
                let delay = 0.5;

                for (let i = 0; i < numBeeps; i++) {
                    let startTime = audioCtx.currentTime + (i * delay);
                    let osc = audioCtx.createOscillator();
                    let gain = audioCtx.createGain();

                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(660, startTime);
                    
                    gain.gain.setValueAtTime(0.15, startTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration - 0.05);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                }
            } catch (e) {
                console.error("Audio Context could not play sound: ", e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('globalBuzzerOverlay');
            const closeBtn = document.getElementById('closeBuzzerBtn');
            const joinLink = document.getElementById('joinBuzzerLink');
            
            let buzzerInterval = null;
            let activeBuzzerId = null;

            function checkBuzzer() {
                fetch('/student/classes/check-buzzer')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.buzzer_id && activeBuzzerId !== data.buzzer_id) {
                            activeBuzzerId = data.buzzer_id;
                            
                            // Show overlay
                            overlay.style.display = 'flex';
                            
                            // Play sound & vibrate
                            playBuzzerSound();
                            if (navigator.vibrate) {
                                navigator.vibrate([200, 100, 200, 100, 200]);
                            }

                            // Link action
                            joinLink.onclick = function(e) {
                                e.preventDefault();
                                fetch('/student/classes/buzzers/' + data.buzzer_id + '/read', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                }).then(() => {
                                    overlay.style.display = 'none';
                                    activeBuzzerId = null;
                                    window.open('/student/classes/join/' + data.class_hour_id, '_blank');
                                });
                            };

                            closeBtn.onclick = function() {
                                fetch('/student/classes/buzzers/' + data.buzzer_id + '/read', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                }).then(() => {
                                    overlay.style.display = 'none';
                                    activeBuzzerId = null;
                                });
                            };
                        }
                    })
                    .catch(err => console.error("Error checking buzzer: ", err));
            }

            // Start polling every 5 seconds
            buzzerInterval = setInterval(checkBuzzer, 5000);
            checkBuzzer();
        });
    </script>
    @endauth
</body>
<script src="{{ URL::asset('/assets/js/app.min.js') }}" ></script>
</html>
