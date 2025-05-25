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

    {{-- Script --}}
    @vite(['resources/sass/app.scss','resources/js/app.js'])

</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">
    <nav class="navbar navbar-expand-lg bg-danger bg-gradient">
        <div class="container-fluid">
            <a class="navbar-brand text-light" href=" {{ route('home') }}">PUPTeC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-dark text-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    @yield('content')
    @extends('layout.footer')

</body>