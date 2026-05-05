@extends('layouts.print')

@section('title', 'Customer Ledger')

@push('styles')
<style>
    .ledger-print-page {
        position: relative;
        overflow: hidden;
        min-height: 860px;
    }
    .ledger-print-watermark {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 430px;
        max-width: 68%;
        opacity: .07;
        transform: translate(-50%, -50%);
        z-index: 0;
        pointer-events: none;
    }
    .ledger-print-content {
        position: relative;
        z-index: 1;
    }
    @media print {
        .ledger-print-watermark {
            position: fixed;
            top: 52%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endpush

@section('content')
<div class="ledger-print-page">
<img class="ledger-print-watermark" src="{{ company_logo_url() }}" alt="FTC watermark">
<div class="ledger-print-content">
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

<div class="mb-3">
    <strong>Guarantor Details</strong>
    @forelse($customer->guarantors as $guarantor)
        <div class="border rounded p-2 mt-2">
            {{ $guarantor->full_name ?: 'Guarantor '.$guarantor->position }}
            | CNIC: {{ $guarantor->cnic ?: '-' }}
            | Phone: {{ $guarantor->phone ?: '-' }}
            @if($guarantor->relationship)
                | Relationship: {{ $guarantor->relationship }}
            @endif
        </div>
    @empty
        <div class="text-muted mt-1">No guarantor added</div>
    @endforelse
</div>

<div class="mb-3">
    <strong>Product / Installment Account Details</strong>
    <table class="table table-bordered table-sm mt-2">
        <thead><tr><th>Account</th><th>Product</th><th class="text-end">Sale Value</th><th class="text-end">Paid</th><th class="text-end">Pending</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($customer->sales as $sale)
            <tr>
                <td>{{ $sale->account_number }}</td>
                <td>{{ $sale->product_name }}</td>
                <td class="text-end">{{ money($sale->installment_sale_price) }}</td>
                <td class="text-end">{{ money($sale->total_paid) }}</td>
                <td class="text-end">{{ money($sale->pending_balance) }}</td>
                <td>{{ readable_status($sale->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No installment accounts.</td></tr>
        @endforelse
        </tbody>
    </table>
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
<div class="mt-5">Printed Date: {{ now()->format('d M Y') }}</div>
</div>
</div>
@endsection
