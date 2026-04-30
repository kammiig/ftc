@extends('layouts.print')

@section('title', 'Payment Receipt')

@push('styles')
<style>
    .receipt-page {
        position: relative;
        overflow: hidden;
    }
    .receipt-watermark {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 0;
    }
    .receipt-watermark img {
        width: 420px;
        max-width: 70%;
        opacity: .07;
        object-fit: contain;
    }
    .receipt-content {
        position: relative;
        z-index: 1;
    }
    .receipt-signature {
        width: 220px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="receipt-page">
    <div class="receipt-watermark">
        <img src="{{ company_logo_url() }}" alt="FTC watermark">
    </div>
    <div class="receipt-content">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="h5 mb-1">Payment Receipt</h2>
                <div>Receipt No: <strong>{{ $payment->receipt_number }}</strong></div>
            </div>
            <div class="text-end">
                Date: {{ $payment->payment_date?->format('d M Y') }}<br>
                Printed: {{ now()->format('d M Y h:i A') }}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Customer</strong><br>
                {{ $payment->customer?->name }}<br>
                {{ $payment->customer?->phone }}<br>
                {{ $payment->customer?->address }}
            </div>
            <div class="col-md-6">
                <strong>Installment Account</strong><br>
                {{ $payment->sale?->account_number }}<br>
                {{ $payment->sale?->product_name }}<br>
                Remaining: {{ money($payment->sale?->pending_balance) }}
            </div>
        </div>

        <table class="table table-bordered">
            <tbody>
            <tr><th>Payment Amount</th><td>{{ money($payment->amount) }}</td></tr>
            <tr><th>Payment Method</th><td>{{ $payment->payment_method }}</td></tr>
            <tr><th>Reference Number</th><td>{{ $payment->reference_number ?: '-' }}</td></tr>
            <tr><th>Received By</th><td>{{ $payment->received_by ?: $payment->user?->name }}</td></tr>
            <tr><th>Remarks</th><td>{{ $payment->remarks ?: '-' }}</td></tr>
            </tbody>
        </table>

        <p class="text-muted">{{ company_setting('receipt_footer_text') }}</p>
        <div class="d-flex justify-content-between mt-5">
            <div>Customer Signature</div>
            <div class="receipt-signature">Authorized Signature</div>
        </div>
    </div>
</div>
@endsection
