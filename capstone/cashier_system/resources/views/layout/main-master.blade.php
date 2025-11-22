<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PUPTeC</title>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    {{-- Vite --}}
    @vite(['resources/sass/app.scss','resources/js/app.js'])

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- jQuery + Bootstrap JS (kept for other parts of your app) --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* ---------- Base ---------- */
        :root {
            --maroon-1: #7a0a0a;
            --maroon-2: #8b0000;
            --white: #ffffff;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
            background: #f5f5f5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a { text-decoration: none; }

        /* ---------- Top Navbar ---------- */
        .navbar-modern {
            background: var(--maroon-1);
            height: 65px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            box-shadow: 0 3px 12px rgba(0,0,0,0.12);
        }

        .toggle-btn {
            background: rgba(255,255,255,0.12);
            border: none;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 1.15rem;
            color: var(--white);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .18s ease, transform .12s ease;
        }

        .toggle-btn:hover { background: rgba(255,255,255,0.22); transform: translateY(-1px); }

        .navbar-brand {
            color: var(--white);
            font-size: 1.35rem;
            font-weight: 700;
            margin-left: 6px;
            letter-spacing: .3px;
        }

        .nav-right {
            margin-left: auto;
            display: flex;
            gap: 14px;
            align-items: center;
            color: var(--white);
        }

        .nav-right a { color: var(--white); opacity: .95; font-size: .95rem; }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            width: 250px;
            height: calc(100vh - 65px);
            background: var(--maroon-2);
            color: var(--white);
            overflow-y: auto;
            transition: width .25s ease-in-out, transform .22s ease;
            box-shadow: 3px 0 10px rgba(0,0,0,0.12);
            padding-top: 10px;
        }

        .sidebar.collapsed { width: 70px; }

        .sidebar .nav {
            list-style: none;
            margin: 0;
            padding: 6px 6px;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--white);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: .95rem;
            transition: background .16s ease, transform .08s ease;
        }

        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            font-size: 1.05rem;
        }

        .sidebar .nav-link:hover { background: rgba(255,255,255,0.08); transform: translateX(4px); }

        /* chevron on the right */
        .toggle-chevron {
            margin-left: auto;
            transition: transform .22s ease;
            opacity: .9;
            font-size: .9rem;
        }

        /* ---------- Submenu (custom — always in layout to prevent snap) ---------- */
        .submenu {
            display: block;              /* always in layout (prevents snap) */
            padding-left: 44px;         /* final indent for submenu items */
            max-height: 0;              /* collapsed by default */
            overflow: hidden;
            opacity: 0;
            transition: max-height .28s cubic-bezier(.2,.9,.3,1), opacity .18s linear;
        }

        .submenu.open {
            /* JS sets exact max-height for smoothness; this is a large upper bound fallback */
            max-height: 900px;
            opacity: 1;
        }

        .submenu .nav-link {
            padding: 9px 8px;
            background: transparent;
            font-size: .92rem;
            gap: 10px;
            border-radius: 6px;
        }

        .submenu .nav-link:hover { background: rgba(255,255,255,0.06); transform: translateX(4px); }

        /* Hide submenu labels in collapsed (mini) sidebar */
        .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .submenu { max-height: 0 !important; opacity: 0 !important; padding-left: 0 !important; }

        /* Active link */
        .nav-link.active { background: rgba(255,255,255,0.16); font-weight: 600; }

        /* ---------- Content ---------- */
        .content {
            margin-left: 250px;
            padding: 90px 24px 24px;
            transition: margin-left .25s ease-in-out;
        }

        .sidebar.collapsed ~ .content { margin-left: 70px; }

        /* ---------- Responsive (mobile) ---------- */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.collapsed {
                transform: translateX(0);
                width: 250px;
            }
            .sidebar.collapsed ~ .content { margin-left: 0; }
            .content { margin-left: 0 !important; padding-top: 90px; }
        }

        /* ---------- Accessibility / Prefers reduced motion ---------- */
        @media (prefers-reduced-motion: reduce) {
            .submenu, .sidebar, .content, .toggle-chevron, .toggle-btn { transition: none !important; }
        }
    </style>
</head>

<body>
    <!-- TOP NAVBAR -->
    <nav class="navbar-modern no-print">
        <button id="toggleSidebar" class="toggle-btn" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">PUPTeC</a>

        <div class="nav-right d-none d-md-flex">
            <a href="{{ route('user.profile') }}"><i class="fa-solid fa-user"></i>&nbsp;<span>Profile</span></a>
            <a href="{{ route('logout') }}"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;<span>Logout</span></a>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <nav id="sidebar" class="sidebar no-print" aria-label="Main sidebar">
        <ul class="nav flex-column">
            <!-- Home -->
            <li>
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>

            <!-- Payment Forms -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="paymentsMenu" aria-expanded="false">
                    <i class="fas fa-money-bill"></i>
                    <span>Payment Forms</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="paymentsMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('payments.customer.new') }}" class="nav-link">Customer Payment Form</a>
                </div>
            </li>

            <!-- Transactions -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="transactionsMenu" aria-expanded="false">
                    <i class="fas fa-receipt"></i>
                    <span>Transactions</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="transactionsMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('payments.pending') }}" class="nav-link">Pending Transactions</a>
                    <a href="{{ route('receipts.list') }}" class="nav-link">Transaction History</a>
                </div>
            </li>

            <!-- Billing -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="billingMenu" aria-expanded="false">
                    <i class="fas fa-file-invoice"></i>
                    <span>Billing</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="billingMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('concessionaires.billing.list') }}" class="nav-link">Concessionaire Billing List</a>
                    <a href="{{ route('concessionaires.billing.new') }}" class="nav-link">Create Billing Statement</a>
                </div>
            </li>

            <!-- Maintenance -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="maintenanceMenu" aria-expanded="false">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="maintenanceMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('fees.list') }}" class="nav-link">Manage Fees</a>
                    <a href="{{ route('receipts.manage') }}" class="nav-link">Manage Receipts</a>
                    <a href="{{ route('concessionaires.list') }}" class="nav-link">Manage Concessionaires</a>
                    <a href="{{ route('users.list') }}" class="nav-link">Manage Users</a>
                </div>
            </li>

            <!-- Reports & Analytics -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="reportsMenu" aria-expanded="false">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports & Analytics</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="reportsMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('data.analytics') }}" class="nav-link">View Analytics</a>
                    <a href="{{ route('reports.page') }}" class="nav-link">View Reports</a>
                </div>
            </li>

            <!-- Admin Controls -->
            <li>
                <a href="javascript:void(0)" class="nav-link menu-toggle" data-submenu="adminMenu" aria-expanded="false">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin Controls</span>
                    <i class="fas fa-chevron-right toggle-chevron" aria-hidden="true"></i>
                </a>
                <div id="adminMenu" class="submenu" aria-hidden="true">
                    <a href="{{ route('audit.logs') }}" class="nav-link">Audit Logs</a>
                    <a href="{{ route('backups.manage') }}" class="nav-link">Backups</a>
                </div>
            </li>

        </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="content">
        @yield('content')
        @extends('layout.footer')
    </div>

    <!-- ---------- JS: Sidebar + submenu toggles ---------- -->
    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebar');

            /* Toggle main sidebar (full <-> mini) */
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                // When collapsing, close all submenus and reset chevrons
                if (sidebar.classList.contains('collapsed')) {
                    document.querySelectorAll('.submenu.open').forEach(s => {
                        s.style.maxHeight = '0';
                        s.classList.remove('open');
                        s.setAttribute('aria-hidden', 'true');
                    });
                    document.querySelectorAll('.menu-toggle').forEach(btn => {
                        btn.setAttribute('aria-expanded', 'false');
                        const ch = btn.querySelector('.toggle-chevron');
                        if (ch) ch.style.transform = '';
                    });
                }
            });

            /* Submenu toggles (custom, no bootstrap collapse) */
            document.querySelectorAll('.menu-toggle').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    const submenuId = btn.getAttribute('data-submenu');
                    if (!submenuId) return;
                    const submenu = document.getElementById(submenuId);
                    if (!submenu) return;

                    // If sidebar is collapsed (mini), don't open submenus
                    if (sidebar.classList.contains('collapsed')) return;

                    const isOpen = submenu.classList.contains('open');

                    // Optional: accordion behavior — close other open submenus
                    document.querySelectorAll('.submenu.open').forEach(s => {
                        if (s === submenu) return;
                        s.style.maxHeight = s.scrollHeight + 'px'; // set current height
                        requestAnimationFrame(() => {
                            s.style.maxHeight = '0';
                            s.classList.remove('open');
                            s.setAttribute('aria-hidden', 'true');
                        });
                        const otherToggle = document.querySelector('[data-submenu="' + s.id + '"]');
                        if (otherToggle) {
                            otherToggle.setAttribute('aria-expanded', 'false');
                            const otherChevron = otherToggle.querySelector('.toggle-chevron');
                            if (otherChevron) otherChevron.style.transform = '';
                        }
                    });

                    if (!isOpen) {
                        // open current submenu
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                        submenu.classList.add('open');
                        submenu.setAttribute('aria-hidden', 'false');
                        btn.setAttribute('aria-expanded', 'true');

                        // rotate chevron
                        const chevron = btn.querySelector('.toggle-chevron');
                        if (chevron) chevron.style.transform = 'rotate(90deg)';

                        // after transition, clear inline maxHeight so it can resize naturally
                        const onTransitionEnd = function (ev) {
                            if (ev.propertyName === 'max-height') {
                                submenu.style.maxHeight = 'none';
                                submenu.removeEventListener('transitionend', onTransitionEnd);
                            }
                        };
                        submenu.addEventListener('transitionend', onTransitionEnd);

                    } else {
                        // close current submenu smoothly
                        submenu.style.maxHeight = submenu.scrollHeight + 'px'; // start from actual height
                        // force reflow
                        void submenu.offsetHeight;
                        submenu.style.maxHeight = '0';
                        submenu.classList.remove('open');
                        submenu.setAttribute('aria-hidden', 'true');
                        btn.setAttribute('aria-expanded', 'false');

                        // reset chevron
                        const chevron = btn.querySelector('.toggle-chevron');
                        if (chevron) chevron.style.transform = '';

                        // clear inline maxHeight after animation
                        const onTransitionEndClose = function (ev) {
                            if (ev.propertyName === 'max-height') {
                                submenu.style.maxHeight = '';
                                submenu.removeEventListener('transitionend', onTransitionEndClose);
                            }
                        };
                        submenu.addEventListener('transitionend', onTransitionEndClose);
                    }
                });
            });

            /* Optional: close sidebar on Escape (mobile friendly) */
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('collapsed')) {
                        sidebar.classList.remove('collapsed');
                    }
                }
            });

            /* Optional: click outside to close mobile sidebar */
            document.addEventListener('click', function (ev) {
                const target = ev.target;
                const isClickInsideSidebar = sidebar.contains(target);
                const isClickToggle = toggleBtn.contains(target);
                if (window.innerWidth <= 768 && !isClickInsideSidebar && !isClickToggle && sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                }
            });

        })();
    </script>
</body>

</html>
