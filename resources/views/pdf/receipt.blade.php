@extends('pdf.layout')

@section('title', 'Payment Receipt')

@section('content')
<h2>Payment Receipt</h2>
<div class="grid">
    <div class="col">
        Receipt No: <strong>{{ $payment->receipt_number }}</strong><br>
        Payment Date: {{ $payment->payment_date?->format('d M Y') }}<br>
        Payment Method: {{ $payment->payment_method }}<br>
        Reference: {{ $payment->reference_number ?: '-' }}
    </div>
    <div class="col">
        Customer: <strong>{{ $payment->customer?->name }}</strong><br>
        Phone: {{ $payment->customer?->phone }}<br>
        Product: {{ $payment->sale?->product_name }}<br>
        Account: {{ $payment->sale?->account_number }}
    </div>
</div>

<table>
    <tbody>
    <tr><th>Payment Amount</th><td class="text-end">{{ money($payment->amount) }}</td></tr>
    <tr><th>Remaining Balance</th><td class="text-end">{{ money($payment->sale?->pending_balance) }}</td></tr>
    <tr><th>Received By</th><td>{{ $payment->received_by ?: $payment->user?->name }}</td></tr>
    <tr><th>Remarks</th><td>{{ $payment->remarks ?: '-' }}</td></tr>
    </tbody>
</table>

<p class="muted">{{ company_setting('receipt_footer_text') }}</p>

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
