<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOMS Academy - Business Management System</title>
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
            padding: 1.25rem 1rem;
            backdrop-filter: blur(10px);
            transition: transform 0.2s, background 0.2s;
            width: 180px;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .feature:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.12);
            color: inherit;
        }

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
        }

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
        <div class="logo-icon"><img src="{{ asset('images/icon.png') }}" alt="FOMS Academy"></div>
        <h1>FOMS Academy</h1>
        <p class="subtitle">Business Management System
            {{-- <br>Manage your academy with confidence — from daily updates to big insights. --}}
        </p>

        <div class="features">
            <a href="{{ route('student.login') }}" class="feature">
                <div class="feature-icon">&#x1F393;</div>
                <h3>Students</h3>
                <p>Classes &amp; Notes</p>
            </a>
            <a href="{{ route('teacher.login') }}" class="feature">
                <div class="feature-icon">&#x1F468;&#x200D;&#x1F3EB;</div>
                <h3>Teachers</h3>
                <p>Classes &amp; Students</p>
            </a>
        </div>

        <p class="footer">&copy; {{ date('Y') }} FOMS Academy Business Management System. Crafted by <a href="https://webmahal.com" target="_blank" rel="noopener">Web Mahal</a></p>
    </div>
</body>
</html>
