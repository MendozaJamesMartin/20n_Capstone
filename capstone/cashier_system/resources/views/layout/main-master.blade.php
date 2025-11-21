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

    {{-- Script --}}
    @vite(['resources/sass/app.scss','resources/js/app.js'])

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>


    <style>
        body {
            margin: 0;
            overflow-x: hidden;
            font-family: 'Nunito', sans-serif;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            height: 60px;
        }

        .sidebar {
            width: 250px;
            background-color: #dc3545;
            color: white;
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            padding: 1rem;
            transition: width 0.3s ease;
            z-index: 1040;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        /* Remove border/background completely */
        .toggle-btn {
            background: none;
            border: none;
            outline: none;
            box-shadow: none;
            font-size: 1.5rem;
            padding: 2px;
            cursor: pointer;
        }

        /* Make icon solid white */
        .toggle-btn i {
            color: #fff !important;
        }

        /* Hover state: softer white */
        .toggle-btn:hover {
            color: #4d0006ff; /* light red/soft white tint */
        }

        .sidebar .nav-link {
            color: white;
            padding: 0.5rem 1rem;
            display: block;
            white-space: nowrap;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .sidebar.collapsed h4,
        .sidebar.collapsed .nav-link:not(.toggle-btn) {
            display: none;
        }

        .content {
            margin-left: 250px;
            padding: 80px 20px 20px;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .content {
            margin-left: 60px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.collapsed {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
                padding-top: 80px;
            }

            .sidebar.collapsed ~ .content {
                margin-left: 0;
            }
        }
        
    </style>

</head>

<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-danger bg-danger px-3 no-print">

        <button id="toggleSidebar" class="toggle-btn"> 
            <i class="fas fa-bars"></i> 
        </button>

        <a class="navbar-brand text-light" href=" {{ route('admin.dashboard') }}">PUPTeC</a>
    </nav>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar d-flex flex-column no-print">

        <ul class="nav flex-column">

            <!-- Home Button -->
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                <i class="fas fa-home me-2"></i> Home
            </a>

            <!-- Payments -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#paymentsSubmenu" role="button" aria-expanded="false" aria-controls="paymentsSubmenu">
                    Payment Forms
                </a>
                <div class="collapse ps-3" id="paymentsSubmenu">
                    <a href="{{ route('payments.customer.new') }}" class="nav-link">Customer Payment Form</a>
                </div>
            </li>

            <!-- Payments -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#transactionsSubmenu" role="button" aria-expanded="false" aria-controls="transactionsSubmenu">
                    Transactions
                </a>
                <div class="collapse ps-3" id="transactionsSubmenu">
                    <a href="{{ route('payments.pending') }}" class="nav-link">Pending Transactions</a>
                    <a href="{{ route('receipts.list') }}" class="nav-link">Transaction History</a>
                </div>
            </li>

            <!-- Billing -->
            <li class="nav-item mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#billingSubmenu" role="button" aria-expanded="false" aria-controls="billingSubmenu">
                    Billing
                </a>
                <div class="collapse ps-3" id="billingSubmenu">
                    <a href="{{ route('concessionaires.billing.list') }}" class="nav-link">Concessionaire Billing List</a>
                    <a href="{{ route('concessionaires.billing.new') }}" class="nav-link">Create Billing Statement</a>
                </div>
            </li>

            <!-- Maintenance -->
            <li class="nav-item mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#maintenanceSubmenu" role="button" aria-expanded="false" aria-controls="maintenanceSubmenu">
                    Maintenance
                </a>
                <div class="collapse ps-3" id="maintenanceSubmenu">
                    <a href="{{ route('fees.list') }}" class="nav-link">Manage Fees</a>
                    <a href="{{ route('receipts.manage') }}" class="nav-link">Manage Receipts</a>
                    <a href="{{ route('concessionaires.list') }}" class="nav-link">Manage Concessionaires</a>
                    <a href="{{ route('users.list') }}" class="nav-link">Manage Users</a>
                </div>
            </li>

            <!-- Data Analytics and Report -->
            <li class="nav-item mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#reportSubmenu" role="button" aria-expanded="false" aria-controls="reportSubmenu">
                    Data Analytics and Reports
                </a>
                <div class="collapse ps-3" id="reportSubmenu">
                    <a href="{{ route('data.analytics') }}" class="nav-link">View Analytics</a>
                    <a href="{{ route('reports.page') }}" class="nav-link">View Reports</a>
                </div>
            </li>

            <!-- Maintenance -->
            <li class="nav-item mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#admincontrolsSubmenu" role="button" aria-expanded="false" aria-controls="admincontrolsSubmenu">
                    Admin Controls
                </a>
                <div class="collapse ps-3" id="admincontrolsSubmenu">
                    <a href="{{ route('audit.logs') }}" class="nav-link">Audit Logs</a>
                    <a href="{{ route('backups.manage') }}" class="nav-link">Backups</a>
                </div>
            </li>

            <!-- Account -->
            <li class="nav-item mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#accountSubmenu" role="button" aria-expanded="false" aria-controls="accountSubmenu">
                    Account
                </a>
                <div class="collapse ps-3" id="accountSubmenu">
                    <a href="{{ route('user.profile') }}" class="nav-link">User Profile</a>
                    <a href="{{ route('logout') }}" class="nav-link">Sign out</a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="content">
        @yield('content')
        @extends('layout.footer')
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>

</body>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>

</html>