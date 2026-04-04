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

    <!-- Right Sidebar -->
    @include('student.layouts.right-sidebar')
    <!-- END Right Sidebar -->

    <x-pwa-install-button />
    @include('student.layouts.vendor-scripts')
</body>
<script src="{{ URL::asset('/assets/js/app.min.js') }}" ></script>
</html>
