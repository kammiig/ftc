<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customer Ledger</title>
    <style>
        @page { margin: 28px 30px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 10.5px;
            line-height: 1.35;
        }
        .page {
            position: relative;
        }
        .watermark {
            position: fixed;
            left: 145px;
            top: 270px;
            width: 300px;
            opacity: .07;
            z-index: 0;
        }
        .watermark-text {
            position: fixed;
            left: 120px;
            top: 300px;
            width: 360px;
            text-align: center;
            font-size: 76px;
            font-weight: bold;
            color: #082c9d;
            opacity: .07;
            z-index: 0;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #082c9d;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-logo {
            display: table-cell;
            width: 68px;
            vertical-align: top;
        }
        .header-logo img {
            width: 56px;
            height: 56px;
        }
        .header-company {
            display: table-cell;
            vertical-align: top;
        }
        .header-company h1 {
            margin: 0 0 4px;
            color: #082c9d;
            font-size: 20px;
        }
        .header-company div {
            color: #374151;
        }
        h2 {
            margin: 0 0 8px;
            font-size: 16px;
        }
        h3 {
            margin: 14px 0 6px;
            font-size: 12.5px;
        }
        .grid {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            vertical-align: top;
        }
        th {
            background: #eef2ff;
            color: #1f2937;
            text-align: left;
        }
        tfoot th {
            background: #f3f4f6;
        }
        .text-end {
            text-align: right;
        }
        .muted {
            color: #6b7280;
        }
        .summary-table th,
        .summary-table td {
            width: 33.333%;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-top: 28px;
        }
        .footer-date {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }
        .footer-signature {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: right;
        }
        .pdf-authorized-signature {
            display: inline-block;
            width: 220px;
            text-align: center;
        }
        .pdf-authorized-signature img {
            display: block;
            max-width: 170px;
            max-height: 58px;
            margin: 0 auto 5px;
        }
    </style>
</head>
<body>
@php($logoDataUri = company_logo_data_uri())
<div class="page">
    @if($logoDataUri)
        <img class="watermark" src="{{ $logoDataUri }}" alt="FTC watermark">
    @else
        <div class="watermark-text">FTC</div>
    @endif

    <div class="content">
        <div class="header">
            <div class="header-logo">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="FTC logo">
                @endif
            </div>
            <div class="header-company">
                <h1>{{ company_setting('company_name', 'FTC') }}</h1>
                <div>{{ company_setting('company_address', '') }}</div>
                <div>{{ company_setting('company_phone', '') }} | {{ company_setting('company_email', 'contact@ftc.com') }}</div>
            </div>
        </div>

        <h2>Customer Ledger</h2>
        <div class="grid">
            <div class="col">
                <strong>{{ $customer->name }}</strong><br>
                Phone: {{ $customer->phone ?: '-' }}<br>
                CNIC: {{ $customer->cnic ?: '-' }}<br>
                Address: {{ $customer->address ?: '-' }}
            </div>
            <div class="col">
                Generated: {{ now()->format('d M Y h:i A') }}<br>
                Period: {{ $from ?: 'Start' }} to {{ $to ?: 'Today' }}<br>
                @if($sale)
                    Account: {{ $sale->account_number }}<br>
                    Product: {{ $sale->product_name }}
                @else
                    Account: All installment accounts
                @endif
            </div>
        </div>

        <table class="summary-table">
            <tbody>
            <tr>
                <th>Total Debit</th>
                <th>Total Credit</th>
                <th>Remaining Balance</th>
            </tr>
            <tr>
                <td>{{ money($totalDebit) }}</td>
                <td>{{ money($totalCredit) }}</td>
                <td>{{ money($balance) }}</td>
            </tr>
            </tbody>
        </table>

        <h3>Installment Account Details</h3>
        <table>
            <thead>
            <tr>
                <th>Account</th>
                <th>Product</th>
                <th class="text-end">Sale Value</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Pending</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($sale ? collect([$sale]) : $customer->sales as $account)
                <tr>
                    <td>{{ $account->account_number }}</td>
                    <td>{{ $account->product_name }}</td>
                    <td class="text-end">{{ money($account->installment_sale_price) }}</td>
                    <td class="text-end">{{ money($account->total_paid) }}</td>
                    <td class="text-end">{{ money($account->pending_balance) }}</td>
                    <td>{{ readable_status($account->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No installment accounts.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h3>Guarantor Details</h3>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>CNIC</th>
                <th>Phone</th>
                <th>Relationship</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customer->guarantors as $guarantor)
                <tr>
                    <td>{{ $guarantor->full_name ?: 'Guarantor '.$guarantor->position }}</td>
                    <td>{{ $guarantor->cnic ?: '-' }}</td>
                    <td>{{ $guarantor->phone ?: '-' }}</td>
                    <td>{{ $guarantor->relationship ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No guarantor added.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h3>Ledger Entries</h3>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
            </thead>
            <tbody>
            @forelse($ledgers as $ledger)
                <tr>
                    <td>{{ $ledger->entry_date?->format('d M Y') }}</td>
                    <td>{{ $ledger->description }}</td>
                    <td class="text-end">{{ money($ledger->debit) }}</td>
                    <td class="text-end">{{ money($ledger->credit) }}</td>
                    <td class="text-end">{{ money($ledger->balance) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No ledger entries found.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">Totals</th>
                <th class="text-end">{{ money($totalDebit) }}</th>
                <th class="text-end">{{ money($totalCredit) }}</th>
                <th class="text-end">{{ money($balance) }}</th>
            </tr>
            </tfoot>
        </table>

        <p class="muted">{{ company_setting('ledger_footer_text') }}</p>

        <div class="footer-row">
            <div class="footer-date">Printed Date: {{ now()->format('d M Y') }}</div>
            <div class="footer-signature">
                @include('partials.authorized-signature', ['context' => 'ledger', 'pdf' => true])
            </div>
        </div>
    </div>
</div>
</body>
</html>
