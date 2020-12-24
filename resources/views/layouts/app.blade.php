<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} v{{ config('app.version') }}</title>

    <!-- Styles -->
    <!-- Bootstrap 4.5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            margin-top: 70px;
        }

        input[type=submit]:hover,
        button:hover {
            cursor: pointer;
        }

        /* Add Button */
        .button--bottom-float {
            font-family: Arial;
            font-size: 3rem;
            line-height:1.2rem;
            height: 3.5rem;
            width: 3.5rem;

            background-color: #db4437;
            color: white;
            box-shadow: 0 6px 10px 0 rgba(0,0,0,0.14),0 1px 18px 0 rgba(0,0,0,0.12),0 3px 5px -1px rgba(0,0,0,0.2);

            position: absolute;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        @yield('style')
    </style>
</head>
<body>
    <div id='app' class='container'>
        @include('layouts.nav')
        @include('partials.alert')
        <div class='row'>
            @yield('content')
        </div>
    </div>



    {{-- Blade Directives: https://laravel.com/docs/8.x/blade#blade-directives --}}
    {{-- For debugging purpose only, specified in `app\Providers\AppServiceProvider.php` --}}
    @debug
        @include('debug.screen')
    @enddebug

    <!-- Scripts -->
    {{-- Bootstrap 4 CDN: https://getbootstrap.com/docs/4.5/getting-started/download/ --}}
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>

    <!-- Bootstrap 4.5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

    {{-- Vue.js 3 CDN: https://cdnjs.com/libraries/vue --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.0.4/vue.global.js" integrity="sha512-cE6lrhkmjYwRHW+whCyfYcCyPtpTKLPRL9ANEKWDiOEqckBDyaU8ltXx7AtNfoWAq8QxJEiKawVRGiwkGetczA==" crossorigin="anonymous"></script>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        @yield('javascript')
    </script>
</body>
</html>
