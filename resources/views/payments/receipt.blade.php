@extends('layouts.app')

@section('title', 'Payment Receipt')
@section('subtitle', $payment->receipt_number)

@section('content')
<div class="toolbar mb-3">
    <div class="d-flex align-items-center gap-2">
        <img src="{{ company_logo_url() }}" alt="FTC logo" style="height: 46px; width: 46px; object-fit: contain">
        <div>
            <h2 class="h5 mb-0">{{ $payment->receipt_number }}</h2>
            <div class="text-muted">{{ company_setting('company_email', 'contact@ftc.com') }} | {{ $payment->payment_date?->format('d M Y') }}</div>
        </div>
    </div>
    <a href="{{ route('payments.print', $payment) }}" class="btn btn-outline-dark"><i data-lucide="printer"></i> Print / PDF</a>
</div>

<div class="card" style="max-width: 860px">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Customer</div>
                <div class="fw-semibold">{{ $payment->customer?->name }}</div>
                <div>{{ $payment->customer?->phone }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Installment Account</div>
                <div class="fw-semibold">{{ $payment->sale?->account_number }}</div>
                <div>{{ $payment->sale?->product_name }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Amount</div>
                <div class="h5">{{ money($payment->amount) }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Method</div>
                <div>{{ $payment->payment_method }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Reference</div>
                <div>{{ $payment->reference_number ?: '-' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Remaining Balance</div>
                <div>{{ money($payment->sale?->pending_balance) }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Received By</div>
                <div>{{ $payment->received_by ?: $payment->user?->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Remarks</div>
                <div>{{ $payment->remarks ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
