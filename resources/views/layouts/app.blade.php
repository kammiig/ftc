<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ company_setting('company_name', 'FTC') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ftc-ink: #1f2937;
            --ftc-muted: #64748b;
            --ftc-border: #e5e7eb;
            --ftc-bg: #f5f7fb;
            --ftc-sidebar: #0b163f;
            --ftc-accent: #082c9d;
        }
        body {
            background: var(--ftc-bg);
            color: var(--ftc-ink);
            font-size: 14px;
        }
        .app-shell {
            min-height: 100vh;
        }
        .sidebar {
            background: var(--ftc-sidebar);
            min-height: 100vh;
            width: 264px;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1020;
        }
        .sidebar a {
            color: rgba(255,255,255,.72);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .68rem .85rem;
            text-decoration: none;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,.11);
            color: #fff;
        }
        .main {
            margin-left: 264px;
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--ftc-border);
            position: sticky;
            top: 0;
            z-index: 1010;
        }
        .content {
            padding: 24px;
        }
        .card, .btn, .form-control, .form-select, .dropdown-menu, .alert, .modal-content {
            border-radius: 8px;
        }
        .card {
            border-color: var(--ftc-border);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }
        .metric-card {
            min-height: 118px;
        }
        .metric-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e8edff;
            color: var(--ftc-accent);
        }
        .table thead th {
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
        }
        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            object-fit: cover;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            font-weight: 700;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            justify-content: space-between;
        }
        .form-section-title {
            font-size: 13px;
            text-transform: uppercase;
            color: var(--ftc-muted);
            margin-bottom: 12px;
            font-weight: 700;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }
            .main {
                margin-left: 0;
            }
            .content {
                padding: 16px;
            }
        }
        @media print {
            .sidebar, .topbar, .no-print {
                display: none !important;
            }
            .main {
                margin: 0;
            }
            .content {
                padding: 0;
            }
            body {
                background: #fff;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app-shell">
    @auth
        <aside class="sidebar d-none d-lg-flex flex-column p-3">
            <div class="d-flex align-items-center gap-2 mb-4 text-white">
                <img src="{{ company_logo_url() }}" alt="FTC" class="avatar bg-white">
                <div>
                    <div class="fw-bold">{{ company_setting('company_name', 'FTC') }}</div>
                    <small class="text-white-50">Installment System</small>
                </div>
            </div>

            <nav class="d-grid gap-1">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"><i data-lucide="users"></i> Customers</a>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><i data-lucide="package"></i> Products</a>
                <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><i data-lucide="file-plus-2"></i> Installment Sales</a>
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}"><i data-lucide="wallet-cards"></i> Payments</a>
                <a href="{{ route('pending.index') }}" class="{{ request()->routeIs('pending.*') ? 'active' : '' }}"><i data-lucide="alarm-clock"></i> Pending & Overdue</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i data-lucide="bar-chart-3"></i> Reports</a>
                    <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><i data-lucide="settings"></i> Settings</a>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i data-lucide="shield-check"></i> Users</a>
                    <a href="{{ route('backups.index') }}" class="{{ request()->routeIs('backups.*') ? 'active' : '' }}"><i data-lucide="database-backup"></i> Backups</a>
                @endif
            </nav>
        </aside>

        <div class="offcanvas offcanvas-start d-lg-none no-print" tabindex="-1" id="mobileNav">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><img src="{{ company_logo_url() }}" alt="FTC" style="height: 28px; width: 28px; object-fit: contain" class="me-2">{{ company_setting('company_name', 'FTC') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <nav class="d-grid gap-2">
                    <a class="btn btn-outline-secondary text-start" href="{{ route('dashboard') }}"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('customers.index') }}"><i data-lucide="users"></i> Customers</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('products.index') }}"><i data-lucide="package"></i> Products</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('sales.index') }}"><i data-lucide="file-plus-2"></i> Installment Sales</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('payments.index') }}"><i data-lucide="wallet-cards"></i> Payments</a>
                    <a class="btn btn-outline-secondary text-start" href="{{ route('pending.index') }}"><i data-lucide="alarm-clock"></i> Pending & Overdue</a>
                    @if(auth()->user()->isAdmin())
                        <a class="btn btn-outline-secondary text-start" href="{{ route('reports.index') }}"><i data-lucide="bar-chart-3"></i> Reports</a>
                        <a class="btn btn-outline-secondary text-start" href="{{ route('settings.edit') }}"><i data-lucide="settings"></i> Settings</a>
                        <a class="btn btn-outline-secondary text-start" href="{{ route('users.index') }}"><i data-lucide="shield-check"></i> Users</a>
                        <a class="btn btn-outline-secondary text-start" href="{{ route('backups.index') }}"><i data-lucide="database-backup"></i> Backups</a>
                    @endif
                </nav>
            </div>
        </div>
    @endauth

    <main class="main">
        @auth
            <header class="topbar no-print">
                <div class="container-fluid py-3 px-4 d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" title="Menu">
                            <i data-lucide="menu"></i>
                        </button>
                        <img src="{{ company_logo_url() }}" alt="FTC logo" class="d-none d-sm-block" style="height: 34px; width: 34px; object-fit: contain">
                        <div>
                            <h1 class="h5 mb-0">@yield('title', 'Dashboard')</h1>
                            <small class="text-muted">@yield('subtitle', now()->format('l, d M Y'))</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-none d-md-block text-end me-2">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ readable_status(auth()->user()->role) }}</small>
                        </div>
                        <a href="{{ route('payments.create') }}" class="btn btn-success btn-sm"><i data-lucide="plus"></i> Payment</a>
                        <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm"><i data-lucide="file-plus-2"></i> Sale</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm" title="Logout"><i data-lucide="log-out"></i></button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <div class="@auth content @endauth">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
    lucide.createIcons({attrs: {width: 18, height: 18, strokeWidth: 2}});
</script>
@stack('scripts')
</body>
</html>
