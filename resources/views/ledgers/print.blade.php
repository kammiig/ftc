@extends('layouts.print')

@section('title', 'Customer Ledger')

@section('content')
<div class="mb-3">
    <h2 class="h5 mb-2">Customer Ledger</h2>
    <div class="row">
        <div class="col-md-6">
            <strong>{{ $customer->name }}</strong><br>
            CNIC: {{ $customer->cnic ?: '-' }}<br>
            Phone: {{ $customer->phone }}<br>
            Address: {{ $customer->address ?: '-' }}
        </div>
        <div class="col-md-6 text-md-end">
            Printed: {{ now()->format('d M Y h:i A') }}<br>
            Period: {{ $from ?: 'Start' }} to {{ $to ?: 'Today' }}
        </div>
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead><tr><th>Date</th><th>Description</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th><th>Method</th><th>Reference</th></tr></thead>
    <tbody>
    @foreach($ledgers as $ledger)
        <tr>
            <td>{{ $ledger->entry_date?->format('d M Y') }}</td>
            <td>{{ $ledger->description }}</td>
            <td class="text-end">{{ money($ledger->debit) }}</td>
            <td class="text-end">{{ money($ledger->credit) }}</td>
            <td class="text-end">{{ money($ledger->balance) }}</td>
            <td>{{ $ledger->payment_method ?: '-' }}</td>
            <td>{{ $ledger->reference_number ?: '-' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr class="fw-bold">
        <td colspan="2">Totals</td>
        <td class="text-end">{{ money($totalDebit) }}</td>
        <td class="text-end">{{ money($totalCredit) }}</td>
        <td class="text-end">{{ money($balance) }}</td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
</table>

<p class="text-muted">{{ company_setting('ledger_footer_text') }}</p>
<div class="d-flex justify-content-between mt-5">
    <div>Printed Date: {{ now()->format('d M Y') }}</div>
    <div class="signature-line">Authorized Signature</div>
</div>
@endsection
