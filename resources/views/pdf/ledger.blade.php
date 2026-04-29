@extends('pdf.layout')

@section('title', 'Customer Ledger')

@section('content')
<h2>Customer Ledger</h2>
<div class="grid">
    <div class="col">
        <strong>{{ $customer->name }}</strong><br>
        Phone: {{ $customer->phone }}<br>
        CNIC: {{ $customer->cnic ?: '-' }}<br>
        Address: {{ $customer->address ?: '-' }}
    </div>
    <div class="col">
        Generated: {{ now()->format('d M Y h:i A') }}<br>
        Period: {{ $from ?: 'Start' }} to {{ $to ?: 'Today' }}<br>
        @if($sale)
            Installment Account: {{ $sale->account_number }}<br>
            Product: {{ $sale->product_name }}
        @endif
    </div>
</div>

<h3>Installment Accounts</h3>
<table>
    <thead><tr><th>Account</th><th>Product</th><th class="text-end">Sale</th><th class="text-end">Paid</th><th class="text-end">Pending</th><th>Status</th></tr></thead>
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
    <thead><tr><th>Name</th><th>CNIC</th><th>Phone</th><th>Relationship</th></tr></thead>
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

<h3>Ledger</h3>
<table>
    <thead><tr><th>Date</th><th>Description</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr></thead>
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

<div class="signature">
    <div class="signature-box">
        @if(signature_image_data_uri())
            <img src="{{ signature_image_data_uri() }}" alt="Signature">
        @endif
        Authorized Signature<br>
        <strong>{{ signature_name() }}</strong>
    </div>
</div>
@endsection
