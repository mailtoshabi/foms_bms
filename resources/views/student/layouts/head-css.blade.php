<!-- preloader css -->
<link rel="stylesheet" href="{{ URL::asset('assets/css/preloader.min.css') }}" type="text/css" />

<!-- Bootstrap Css -->
<link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/css/modern.css') }}?v={{ filemtime(public_path('assets/css/modern.css')) }}" id="modern-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/global.css') }}?v={{ filemtime(public_path('assets/css/global.css')) }}" id="gloabal-style" rel="stylesheet" type="text/css" />

@yield('css')
