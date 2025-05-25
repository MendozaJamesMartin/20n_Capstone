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

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                            <a class="nav-link active text-light" aria-current="page" href="{{ route('admin.dashboard') }}">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Payments
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('payments.pending') }} ">Pending Payments</a></li>
                                <li><a class="dropdown-item" href=" {{ route('payments.student.new') }} ">Student Payment Form</a></li>
                                <li><a class="dropdown-item" href=" {{ route('payments.outsider.new') }} ">Outsider Payment Form</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Fees
                            </a>    
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('fees.list') }} ">Accepted Fees</a></li>
                                <li><a class="dropdown-item" href=" {{ route('fees.list.deleted') }} ">Deleted Fees</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Transactions
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('receipts.list') }} ">Transaction History and Receipts</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Concessionaires
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('concessionaires.list') }} ">List of Concessionaires</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href=" {{ route('concessionaires.billing.list') }} ">Concessionaire Billing List</a></li>
                                <li><a class="dropdown-item" href=" {{ route('concessionaires.billing.new') }} ">Create Billing Statement</a></li>
                                <li><a class="dropdown-item" href=" {{ route('concessionaires.billing.payment') }} ">Bills Payment</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                User Management
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href=" {{ route('user.profile') }} ">User Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href=" {{ route('users.list') }} ">List of Users</a></li>
                                <li><a class="dropdown-item" href=" {{ route('register') }} ">Register User</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href=" {{ route('logout') }} ">Logout</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Data Analysis and Reports
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Dropdown Option 1</a></li>
                                <li><a class="dropdown-item" href="#">Dropdown Option 2</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-dark text-light" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>

    @yield('content')
    @include('layout.flash-toast')
    @extends('layout.footer')

</body>