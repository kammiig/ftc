<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Print') - {{ company_setting('company_name', 'FTC') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
            color: #111827;
            font-size: 13px;
        }
        .print-page {
            max-width: 980px;
            margin: 24px auto;
            padding: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .print-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .table th {
            background: #f8fafc;
        }
        .signature-line {
            width: 220px;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-page {
                max-width: none;
                margin: 0;
                padding: 0;
                border: 0;
                border-radius: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
<main class="print-page">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ company_logo_url() }}" class="print-logo" alt="FTC logo">
            <div>
                <h1 class="h4 mb-1">{{ company_setting('company_name', 'FTC') }}</h1>
                <div>{{ company_setting('company_address', '') }}</div>
                <div>{{ company_setting('company_phone', '') }} | {{ company_setting('company_email', 'contact@ftc.com') }}</div>
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-dark btn-sm no-print">Print / PDF</button>
    </div>

    @yield('content')
</main>
</body>
</html>
