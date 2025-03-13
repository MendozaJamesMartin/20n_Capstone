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
    <div>
        <nav class="navbar navbar-expand-lg bg-danger bg-gradient">
            <div class="container-fluid">
                <a class="navbar-brand text-light" href="#">PUPTeC</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active text-light" aria-current="page" href="{{ url('/home') }}">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Forms
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('InsertNewConcessionaireTransaction') }} ">Concessionaire Payment Form</a></li>
                                <li><a class="dropdown-item" href=" {{ route('InsertNewStudentTransaction') }} ">Student Payment Form</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Concessionaires
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('ConcessionairesList') }} ">List of Concessionaires</a></li>
                                <li><a class="dropdown-item" href=" {{ route('ConcessionaireBilling') }} ">Concessionaire Billing List</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href=" {{ route('AddConcessionaireBilling') }} ">Create Billing Statement</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Transactions
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('FeesList') }} ">List of Student Fees</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href=" {{ route('TransactionsList') }} ">Transaction History</a></li>
                                <li><a class="dropdown-item" href=" {{ route('ReceiptsList') }} ">Receipts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="">Data Analytics and Reports</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>

    @yield('content')
    @extends('layout.footer')

</body>