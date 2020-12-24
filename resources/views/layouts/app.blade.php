<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} v{{ config('app.version') }}</title>

    <!-- Styles -->
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

    <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
    </div>

    <!-- Blade Directives: https://laravel.com/docs/8.x/blade#blade-directives -->
    <!-- For debugging purpose only, specified in `app\Providers\AppServiceProvider.php` -->
    @debug
        @include('debug.screen')
    @enddebug

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        @yield('javascript')
    </script>
</body>
</html>
