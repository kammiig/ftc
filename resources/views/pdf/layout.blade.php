<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'FTC PDF')</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; line-height: 1.35; }
        .header { display: table; width: 100%; border-bottom: 2px solid #082c9d; padding-bottom: 12px; margin-bottom: 16px; }
        .header-logo { display: table-cell; width: 70px; vertical-align: top; }
        .header-logo img { width: 58px; height: 58px; object-fit: contain; }
        .header-company { display: table-cell; vertical-align: top; }
        .header-company h1 { margin: 0 0 4px; font-size: 20px; color: #082c9d; }
        .header-company div { color: #374151; }
        h2 { font-size: 16px; margin: 0 0 10px; }
        h3 { font-size: 13px; margin: 14px 0 6px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #eef2ff; color: #1f2937; text-align: left; }
        .text-end { text-align: right; }
        .muted { color: #6b7280; }
        .grid { display: table; width: 100%; margin-bottom: 10px; }
        .col { display: table-cell; width: 50%; vertical-align: top; }
        .summary .col { width: 25%; border: 1px solid #d1d5db; padding: 8px; }
        .signature { margin-top: 42px; text-align: right; }
        .signature-box { display: inline-block; width: 220px; border-top: 1px solid #111827; padding-top: 6px; text-align: center; }
        .signature img { max-width: 160px; max-height: 60px; display: block; margin: 0 auto 4px; }
    </style>
</head>
<body>
<div class="header">
    <div class="header-logo">
        @if(company_logo_data_uri())
            <img src="{{ company_logo_data_uri() }}" alt="FTC logo">
        @endif
    </div>
    <div class="header-company">
        <h1>{{ company_setting('company_name', 'FTC') }}</h1>
        <div>{{ company_setting('company_address', '') }}</div>
        <div>{{ company_setting('company_phone', '') }} | {{ company_setting('company_email', 'contact@ftc.com') }}</div>
    </div>
</div>

@yield('content')
</body>
</html>
