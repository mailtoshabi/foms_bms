<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />
        <title> @yield('title') | FOMS ACADEMY Business Management System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
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
        @include('student.layouts.head-css')
  </head>

    @yield('body')

    @yield('content')

    @include('student.layouts.vendor-scripts')
    </body>
</html>
