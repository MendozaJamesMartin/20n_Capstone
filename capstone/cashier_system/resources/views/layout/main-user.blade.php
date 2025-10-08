<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getlocale())}}">

</html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>laravel</title>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Script --}}
    @vite(['resources/sass/app.scss','resources/js/app.js'])

</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
    <nav class="navbar navbar-expand-lg bg-danger bg-gradient">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href=" {{ route('home') }}">PUPTeC</a>
        </div>
    </nav>

    @yield('content')
    @extends('layout.footer')

</body>