@extends('admin.layouts.master-without-nav')
@section('title')
    Admin Login
@endsection

@section('css')
    <style>
        /* Premium Modern Styles for Admin Login */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
            background-color: #0f172a !important;
        }

        /* Left form panel customization */
        .auth-page {
            background: #090d16 !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-full-page-content {
            background-color: #ffffff !important;
            box-shadow: 20px 0 80px rgba(0, 0, 0, 0.08) !important;
            border-right: 1px solid rgba(226, 232, 240, 0.8) !important;
            position: relative;
            z-index: 2;
            transition: all 0.4s ease;
        }

        body[data-layout-mode="dark"] .auth-full-page-content {
            background-color: #0f172a !important;
            border-right: 1px solid rgba(51, 65, 85, 0.4) !important;
            box-shadow: 20px 0 80px rgba(0, 0, 0, 0.3) !important;
        }

        /* Title & Brand Header */
        .portal-title {
            font-family: 'Outfit', sans-serif !important;
            font-weight: 850 !important;
            font-size: 2.2rem !important;
            letter-spacing: -0.04em !important;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px !important;
            display: inline-block;
        }

        .portal-subtitle {
            font-size: 0.92rem !important;
            color: #64748b !important;
            font-weight: 500 !important;
            max-width: 300px;
            margin: 0 auto !important;
            line-height: 1.5;
        }

        /* Floating form inputs styling */
        .form-floating-custom .form-control {
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 12px !important;
            color: #1e293b !important;
            font-weight: 600 !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        body[data-layout-mode="dark"] .form-floating-custom .form-control {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        /* Focused State */
        .form-floating-custom .form-control:focus {
            border-color: #4f46e5 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
        }

        body[data-layout-mode="dark"] .form-floating-custom .form-control:focus {
            background-color: #0f172a !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.18) !important;
        }

        /* Active label color on focus */
        .form-floating-custom .form-control:focus~label {
            color: #4f46e5 !important;
            font-weight: 700 !important;
        }

        /* Floating icons */
        .form-floating-icon {
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        /* Active icon color on focus */
        .form-floating-custom .form-control:focus~.form-floating-icon {
            color: #4f46e5 !important;
            transform: translateY(-50%) scale(1.05);
        }

        /* Password addon button positioning and hover */
        #password-addon {
            background: transparent !important;
            border: none !important;
            z-index: 5;
            transition: color 0.2s ease;
        }

        #password-addon:hover {
            color: #4f46e5 !important;
        }

        /* Styled Login Button */
        .btn-zopa {
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
            border: none !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            height: 52px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.22) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .btn-zopa:hover {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.35) !important;
            transform: translateY(-2px) !important;
            color: #ffffff !important;
        }

        .btn-zopa:active {
            transform: translateY(0px) !important;
        }

        /* Nice Premium Checkbox styling */
        .form-check-input {
            border: 2px solid #cbd5e1 !important;
            border-radius: 6px !important;
            width: 19px !important;
            height: 19px !important;
            margin-top: 0.18em !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body[data-layout-mode="dark"] .form-check-input {
            border-color: #475569 !important;
            background-color: #1e293b;
        }

        .form-check-input:checked {
            background-color: #4f46e5 !important;
            border-color: #4f46e5 !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3) !important;
        }

        .form-check-label {
            font-weight: 600 !important;
            color: #475569 !important;
            padding-left: 6px;
            cursor: pointer;
            user-select: none;
            font-size: 0.9rem !important;
        }

        body[data-layout-mode="dark"] .form-check-label {
            color: #94a3b8 !important;
        }

        /* Right-Side visual panel customization */
        .auth-bg {
            background-image: url('{{ asset("assets/images/auth-bg.jpg") }}') !important;
            background-size: cover !important;
            background-position: center !important;
            position: relative;
        }

        .bg-overlay {
            background: linear-gradient(135deg, rgba(30, 27, 75, 0.9) 0%, rgba(15, 23, 42, 0.92) 50%, rgba(6, 78, 59, 0.88) 100%) !important;
            backdrop-filter: blur(4px) !important;
            opacity: 1 !important;
            z-index: 1;
        }

        /* Floating dynamic blurred bubbles */
        .bg-bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
            z-index: 2;
        }

        .bg-bubbles li {
            position: absolute;
            list-style: none;
            display: block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            bottom: -180px;
            border-radius: 50%;
            animation: square 28s infinite linear;
            transition-property: background-color;
        }

        .bg-bubbles li:nth-child(1) {
            left: 10%;
            width: 90px;
            height: 90px;
            animation-delay: 0s;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0.02) 100%);
        }

        .bg-bubbles li:nth-child(2) {
            left: 20%;
            width: 140px;
            height: 140px;
            animation-duration: 18s;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.18) 0%, rgba(6, 182, 212, 0.02) 100%);
        }

        .bg-bubbles li:nth-child(3) {
            left: 25%;
            width: 60px;
            height: 60px;
            animation-delay: 4s;
        }

        .bg-bubbles li:nth-child(4) {
            left: 40%;
            width: 120px;
            height: 120px;
            animation-duration: 24s;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.18) 0%, rgba(79, 70, 229, 0.02) 100%);
        }

        .bg-bubbles li:nth-child(5) {
            left: 70%;
            width: 80px;
            height: 80px;
        }

        .bg-bubbles li:nth-child(6) {
            left: 80%;
            width: 160px;
            height: 160px;
            animation-delay: 3s;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.02) 100%);
        }

        .bg-bubbles li:nth-child(7) {
            left: 32%;
            width: 110px;
            height: 110px;
            animation-delay: 7s;
        }

        .bg-bubbles li:nth-child(8) {
            left: 55%;
            width: 50px;
            height: 50px;
            animation-duration: 42s;
        }

        .bg-bubbles li:nth-child(9) {
            left: 90%;
            width: 70px;
            height: 70px;
            animation-delay: 2s;
        }

        .bg-bubbles li:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-duration: 13s;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.12) 0%, rgba(79, 70, 229, 0.02) 100%);
        }

        @keyframes square {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0;
                border-radius: 30%;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-1000px) rotate(600deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        /* Elegant testimonial container */
        .testi-contain {
            position: relative;
            z-index: 5;
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(18px) !important;
            -webkit-backdrop-filter: blur(18px) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 24px !important;
            padding: 40px !important;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.24) !important;
        }

        .testi-contain h4 {
            font-family: 'Outfit', sans-serif !important;
            font-size: 1.65rem !important;
            font-weight: 600 !important;
            line-height: 1.55 !important;
            letter-spacing: -0.01em !important;
        }

        /* Footer heart pulse animation */
        .mdi-heart.text-danger {
            animation: heartPulse 1.2s infinite ease-in-out;
            display: inline-block;
        }

        @keyframes heartPulse {
            0% { transform: scale(1); }
            14% { transform: scale(1.15); }
            28% { transform: scale(1); }
            42% { transform: scale(1.15); }
            70% { transform: scale(1); }
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .auth-full-page-content {
                padding: 40px 24px !important;
                border-right: none !important;
            }

            .portal-title {
                font-size: 1.85rem !important;
            }
        }
    </style>
@endsection

@section('content')

    <div class="auth-page">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-xxl-3 col-lg-4 col-md-5">
                    <div class="auth-full-page-content d-flex p-sm-5 p-4">
                        <div class="w-100">
                            <div class="d-flex flex-column h-100">
                                <div class="mb-4 text-center"></div>
                                <div class="auth-content my-auto">
                                    <div class="text-center">
                                        <h5 class="portal-title mb-0">Admin Portal</h5>
                                        <p class="portal-subtitle text-muted mt-2">Sign in to manage and configure your system.</p>
                                    </div>
                                    <form class="mt-4 pt-2" action="{{ route('admin.login.submit') }}" method="POST">
                                        @csrf
                                        <div class="form-floating form-floating-custom mb-4">
                                            <input type="text"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                value="{{ old('phone', '') }}" id="input-username"
                                                placeholder="Enter User Name" name="phone" required>
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <label for="input-username">Username</label>
                                            <div class="form-floating-icon">
                                                <i data-feather="users"></i>
                                            </div>
                                        </div>

                                        <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                            <input type="password"
                                                class="form-control pe-5 @error('password') is-invalid @enderror"
                                                name="password" id="password-input" placeholder="Enter Password" value="">
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0"
                                                id="password-addon">
                                                <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                            </button>
                                            <label for="password-input">Password</label>
                                            <div class="form-floating-icon">
                                                <i data-feather="lock"></i>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="form-check font-size-15">
                                                    <input class="form-check-input" type="checkbox" id="remember-check">
                                                    <label class="form-check-label" for="remember-check">
                                                        Remember me
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button class="btn btn-zopa w-100 waves-effect waves-light" type="submit"
                                                onclick="this.disabled=true; this.innerText='Logging in...'; this.form.submit();">Log In</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="mt-4 mt-md-5 text-center">
                                    <p class="mb-0 text-muted" style="font-size: 0.85rem; font-weight: 500;">©
                                        <script>document.write(new Date().getFullYear())</script> FOMS ACADEMY Business Management System<br> Crafted with <i class="mdi mdi-heart text-danger"></i> by <a target="_blank" href="https://webmahal.com" class="text-primary fw-semibold">Web Mahal</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-9 col-lg-8 col-md-7">
                    <div class="auth-bg pt-md-5 p-4 d-flex">
                        <div class="bg-overlay"></div>
                        <ul class="bg-bubbles">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                        <div class="row justify-content-center align-items-end w-100 h-100 m-0">
                            <div class="col-xl-7 my-auto">
                                <div class="p-0 p-sm-4 px-xl-0">
                                    <div id="reviewcarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <div class="carousel-item active">
                                                <div class="testi-contain text-center text-white">
                                                    <i class="bx bxs-quote-alt-left text-info display-4 mb-3"></i>
                                                    <h4 class="mt-2 fw-medium lh-base text-white">“From daily updates to big insights — manage everything with confidence.”</h4>
                                                    <div class="mt-4 pt-1">
                                                        <h5 class="font-size-16 text-white mb-0">FOMS Academy</h5>
                                                        <p class="mb-0 text-white-50 small mt-1">Administrative & Operations Portal</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('assets/js/pages/pass-addon.init.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/feather-icon.init.js') }}"></script>
@endsection