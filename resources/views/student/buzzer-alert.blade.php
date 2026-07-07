<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#ec1d23">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Class Join Reminder — FOMS ACADEMY</title>
    <link rel="manifest" href="{{ asset('manifest-student.json') }}">
    <link rel="icon" href="{{ asset('assets/favicon/favicon.ico') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Pulse rings behind the card */
        .pulse-ring {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            border: 3px solid rgba(236, 29, 35, 0.4);
            animation: ring-pulse 1.8s ease-out infinite;
        }
        .pulse-ring:nth-child(2) { animation-delay: 0.6s; }
        .pulse-ring:nth-child(3) { animation-delay: 1.2s; }
        @keyframes ring-pulse {
            0%   { width: 120px; height: 120px; opacity: 1; }
            100% { width: 500px; height: 500px; opacity: 0; }
        }

        .card {
            position: relative;
            z-index: 10;
            background: white;
            border-radius: 24px;
            padding: 40px 32px 36px;
            max-width: 400px;
            width: 92%;
            text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
            animation: card-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes card-bounce {
            from { transform: scale(0.7) translateY(40px); opacity: 0; }
            to   { transform: scale(1) translateY(0);     opacity: 1; }
        }

        .bell-wrap {
            width: 90px; height: 90px;
            background: rgba(236, 29, 35, 0.1);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            animation: bell-shake 0.4s ease-in-out infinite alternate;
            border: 3px solid rgba(236, 29, 35, 0.25);
        }
        @keyframes bell-shake {
            0%   { transform: rotate(-18deg) scale(1); }
            100% { transform: rotate(18deg)  scale(1.08); }
        }
        .bell-wrap svg {
            width: 44px; height: 44px;
            fill: #ec1d23;
        }

        h1 {
            font-size: 22px; font-weight: 800;
            color: #1e293b; margin-bottom: 10px;
        }
        p {
            font-size: 15px; color: #64748b;
            line-height: 1.5; margin-bottom: 32px;
        }

        .btn-join {
            display: block; width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ec1d23, #c9151a);
            color: white; border: none; border-radius: 14px;
            font-size: 17px; font-weight: 700;
            cursor: pointer; text-decoration: none;
            margin-bottom: 12px;
            animation: btn-pulse 1.5s ease-in-out infinite;
            box-shadow: 0 8px 24px rgba(236, 29, 35, 0.4);
        }
        @keyframes btn-pulse {
            0%, 100% { box-shadow: 0 8px 24px rgba(236, 29, 35, 0.4); }
            50%       { box-shadow: 0 8px 36px rgba(236, 29, 35, 0.7); }
        }

        .btn-dismiss {
            display: block; width: 100%;
            padding: 14px;
            background: transparent; color: #94a3b8;
            border: 1.5px solid #e2e8f0; border-radius: 14px;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
        }
        .btn-dismiss:hover { background: #f8fafc; }

        .sound-status {
            margin-top: 20px;
            font-size: 12px; color: #94a3b8;
        }
        .sound-status span { display: inline-block; }
    </style>
</head>
<body>

    <!-- Background pulse rings -->
    <div class="pulse-ring"></div>
    <div class="pulse-ring"></div>
    <div class="pulse-ring"></div>

    <div class="card">
        <div class="bell-wrap">
            <!-- Bell SVG -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6V11c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
        </div>

        <h1>Class Join Reminder!</h1>
        <p>Your teacher is buzzing you to join the class session immediately. Please join now.</p>

        @if($googleMeetLink)
            <a href="{{ $googleMeetLink }}" target="_blank" class="btn-join" id="joinBtn">
                📲 Join Class Now
            </a>
        @elseif($classHourId)
            <a href="/student/classes/join/{{ $classHourId }}" class="btn-join" id="joinBtn">
                📲 Join Class Now
            </a>
        @else
            <a href="/student/dashboard" class="btn-join" id="joinBtn">
                📲 Go to Dashboard
            </a>
        @endif

        <button class="btn-dismiss" id="dismissBtn">Dismiss</button>

        <p class="sound-status" id="soundStatus">🔊 Playing buzzer sound...</p>
    </div>

    <script>
        // ─────────────────────────────────────────────────────────────────────
        // This page is opened by the service worker when the push notification
        // is tapped. Because it was opened by a user gesture (the tap), the
        // browser ALLOWS audio autoplay immediately — no unlock needed.
        // ─────────────────────────────────────────────────────────────────────

        let buzzerStopped = false;
        let buzzerInterval = null;

        /**
         * Play buzzer using Web Audio API (synthesized beeps).
         * This always works when a page is opened via user interaction.
         */
        function playBuzzerSynthesis(ctx) {
            if (buzzerStopped) return;
            const numBeeps = 3, beepDuration = 0.35, silenceDuration = 0.2;
            for (let i = 0; i < numBeeps; i++) {
                const t = ctx.currentTime + i * (beepDuration + silenceDuration);
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(880, t);
                gain.gain.setValueAtTime(0, t);
                gain.gain.linearRampToValueAtTime(0.6, t + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.01, t + beepDuration - 0.05);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(t);
                osc.stop(t + beepDuration);
            }
            // Total cycle = 3 × (0.35 + 0.2) = 1.65 s, repeat every 2 s
        }

        function startBuzzer() {
            let audioCtx;
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } catch(e) {
                document.getElementById('soundStatus').textContent = '⚠️ Audio not supported on this device';
                return;
            }

            // Also try Audio element (WAV file) as primary
            const audio = new Audio('/sounds/buzzer.wav');
            audio.loop = true;
            audio.volume = 1.0;
            const wavPlayPromise = audio.play();

            if (wavPlayPromise !== undefined) {
                wavPlayPromise.then(() => {
                    document.getElementById('soundStatus').textContent = '🔊 Buzzer sound playing';
                    // Stop WAV when dismissed
                    document.getElementById('dismissBtn').addEventListener('click', () => { audio.pause(); });
                    document.getElementById('joinBtn').addEventListener('click',    () => { audio.pause(); });
                }).catch(() => {
                    // WAV blocked — use synthesised beeps
                    document.getElementById('soundStatus').textContent = '🔊 Buzzer playing (beeps)';
                    playBuzzerSynthesis(audioCtx);
                    buzzerInterval = setInterval(() => playBuzzerSynthesis(audioCtx), 2000);
                });
            } else {
                playBuzzerSynthesis(audioCtx);
                buzzerInterval = setInterval(() => playBuzzerSynthesis(audioCtx), 2000);
            }

            // Vibrate repeatedly
            if (navigator.vibrate) {
                function vibrateCycle() {
                    if (!buzzerStopped) {
                        navigator.vibrate([400, 150, 400, 150, 600]);
                        setTimeout(vibrateCycle, 2200);
                    }
                }
                vibrateCycle();
            }
        }

        function stopBuzzer() {
            buzzerStopped = true;
            if (buzzerInterval) { clearInterval(buzzerInterval); }
            if (navigator.vibrate) { navigator.vibrate(0); }
        }

        // Start immediately — page opened by user tap so autoplay is allowed
        startBuzzer();

        // Dismiss button
        document.getElementById('dismissBtn').addEventListener('click', function() {
            stopBuzzer();
            // Go back or close
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/student/dashboard';
            }
        });

        // Stop buzzer when join is clicked
        document.getElementById('joinBtn').addEventListener('click', function() {
            stopBuzzer();
        });

        // Auto-stop after 60 seconds so it doesn't loop forever
        setTimeout(() => {
            stopBuzzer();
            document.getElementById('soundStatus').textContent = '⏱ Buzzer stopped (60s timeout)';
        }, 60000);
    </script>

</body>
</html>
