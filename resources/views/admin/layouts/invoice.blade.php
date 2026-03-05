<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        {{-- <meta charset="utf-8" /> --}}
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title> @yield('title') | FOMS ACADEMY Business Management System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">





        <!-- Bootstrap Css -->
        <link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        <link href="{{ URL::asset('assets/css/global.css') }}" id="app-style" rel="stylesheet" type="text/css" />
  </head>

    @yield('content')
    </body>
</html>
