@extends('layouts.print')

@section('title', 'Customer Profile')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="h5 mb-1">Customer Profile</h2>
        <strong>{{ $customer->name }}</strong><br>
        CNIC: {{ $customer->cnic ?: '-' }}<br>
        Phone: {{ $customer->phone }} {{ $customer->alternate_phone ? '| '.$customer->alternate_phone : '' }}<br>
        WhatsApp: {{ $customer->whatsapp_number ?: $customer->phone }}<br>
        Address: {{ $customer->address ?: '-' }}
    </div>
    @if($customer->photo_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($customer->photo_path) }}" alt="{{ $customer->name }}" style="height: 92px; width: 92px; object-fit: cover; border: 1px solid #e5e7eb; border-radius: 8px">
    @endif
</div>

<div class="row g-2 mb-3">
    <div class="col-3"><strong>Status</strong><br>{{ readable_status($customer->status) }}</div>
    <div class="col-3"><strong>Total Sale</strong><br>{{ money($customer->sales->sum('installment_sale_price')) }}</div>
    <div class="col-3"><strong>Total Paid</strong><br>{{ money($customer->sales->sum('total_paid')) }}</div>
    <div class="col-3"><strong>Total Pending</strong><br>{{ money($customer->sales->sum('pending_balance')) }}</div>
</div>

<h3 class="h6">Guarantors</h3>
@forelse($customer->guarantors as $guarantor)
    <div class="border rounded p-2 mb-2">
        <strong>Guarantor {{ $guarantor->position }}: {{ $guarantor->full_name ?: '-' }}</strong><br>
        Father/Husband: {{ $guarantor->guardian_name ?: '-' }} |
        CNIC: {{ $guarantor->cnic ?: '-' }} |
        Phone: {{ $guarantor->phone ?: '-' }} |
        Relationship: {{ $guarantor->relationship ?: '-' }}<br>
        Address: {{ $guarantor->address ?: '-' }}
    </div>
@empty
    <p class="text-muted">No guarantor added</p>
@endforelse

<h3 class="h6 mt-3">Installment Accounts</h3>
<table class="table table-bordered table-sm">
    <thead><tr><th>Account</th><th>Product</th><th class="text-end">Sale</th><th class="text-end">Paid</th><th class="text-end">Pending</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($customer->sales as $sale)
        <tr>
            <td>{{ $sale->account_number }}</td>
            <td>{{ $sale->product_name }}</td>
            <td class="text-end">{{ money($sale->installment_sale_price) }}</td>
            <td class="text-end">{{ money($sale->total_paid) }}</td>
            <td class="text-end">{{ money($sale->pending_balance) }}</td>
            <td>{{ readable_status($sale->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3 class="h6 mt-3">Recent Payments</h3>
<table class="table table-bordered table-sm">
    <thead><tr><th>Date</th><th>Receipt</th><th>Account</th><th class="text-end">Amount</th><th>Method</th></tr></thead>
    <tbody>
    @forelse($customer->payments as $payment)
        <tr>
            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
            <td>{{ $payment->receipt_number }}</td>
            <td>{{ $payment->sale?->account_number }}</td>
            <td class="text-end">{{ money($payment->amount) }}</td>
            <td>{{ $payment->payment_method }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">No payments found.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-between mt-5">
    <div>Printed: {{ now()->format('d M Y h:i A') }}</div>
    <div class="signature-line">Authorized Signature</div>
</div>
@endsection
