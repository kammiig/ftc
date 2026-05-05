@extends('pdf.layout')

@section('title', 'Payment Receipt')

@section('content')
<style>
    .receipt-pdf-page { position: relative; min-height: 760px; }
    .receipt-pdf-watermark {
        position: absolute;
        left: 120px;
        top: 230px;
        width: 360px;
        opacity: .07;
        z-index: 0;
    }
    .receipt-pdf-watermark-text {
        position: absolute;
        left: 120px;
        top: 280px;
        width: 360px;
        text-align: center;
        font-size: 76px;
        font-weight: bold;
        color: #082c9d;
        opacity: .07;
        z-index: 0;
    }
    .receipt-pdf-content { position: relative; z-index: 1; }
</style>

<div class="receipt-pdf-page">
    @if(company_logo_data_uri())
        <img class="receipt-pdf-watermark" src="{{ company_logo_data_uri() }}" alt="FTC watermark">
    @else
        <div class="receipt-pdf-watermark-text">FTC</div>
    @endif
    <div class="receipt-pdf-content">
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

    </div>
</div>
@endsection
