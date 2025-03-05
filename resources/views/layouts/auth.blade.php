<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Master Magical Key') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mystical.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/stars.js') }}" defer></script>
    <script src="{{ asset('js/mystical-transitions.js') }}"></script>
</head>
<body>
    <div id="app">
        <main class="py-4">
        <div class="container px-3">
            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10 col-12">
                    <!-- Mystical Logo/Title -->
                    <div class="text-center mb-4 mt-4 mt-md-5">
                        <h1 class="mystical-title text-white" style="font-weight: 700; letter-spacing: 4px; text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);">
                            MASTER MAGICAL KEY
                        </h1>
                        <p class="text-white" style="text-align:center;letter-spacing: 3px; font-size: 1.2rem;">TO THE UNIVERSE</p>
                    </div>
                    
                    @yield('content')
                </div>
            </div>
        </div>
        </main>
    </div>
</body>
</html>