<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Frontier Station</title>
    <link rel="canonical" href="@yield('canonical', 'https://shipyard.frontierstation14.com')" />

    <meta name="description" content="@yield('description')">
    <meta name="keywords" content="@yield('keywords', 'Space Station 14, Frontier Station, gaming, space simulation, multiplayer, SS14')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css?v=2.0.2') }}">

    <!-- JS -->
    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>

    <!-- Подключение Interact.js через CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.27/interact.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <style>html { background-color: #000000; }</style>

    <meta name="robots" content="index, follow">
    <meta name="author" content="Mr_Samuel from Frontier Station">
    <meta property="og:title" content="@yield('og_title', 'Frontier Station')">
    <meta property="og:description" content="@yield('og_description')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    @yield('head_scripts')
    @yield('head_styles')
</head>
<body>
    @hasSection('navigation')
        @yield('navigation')
    @else
        @include('layouts.navigation')
    @endif

    @yield('content')

    @yield('footer_scripts')
</body>
</html>
