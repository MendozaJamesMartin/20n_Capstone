<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getlocale())}}">

</html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>PUPTeC</title>

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
    
<nav class="navbar-login no-print">
    <div class="container-fluid d-flex align-items-center">
        <span class="navbar-brand-login">PUPTeC</span>
    </div>
</nav>

<style>
    /* -----------------------------
        LOGIN NAVBAR (MODERN MAROON)
    ------------------------------ */
    .navbar-login {
        background: #7a0a0a;
        height: 65px;
        display: flex;
        align-items: center;
        padding: 0 20px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        position: relative;
        z-index: 1050;
    }

    .navbar-brand-login {
        font-size: 1.4rem;
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>


    @yield('content')
    @extends('layout.footer')

</body>