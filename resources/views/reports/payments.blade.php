@extends('layouts.app')

@section('title', 'Payments Report')
@section('subtitle', 'Date-wise and monthly received payments')

@section('content')
@include('reports._filters', ['paymentMethods' => $paymentMethods])

<div class="card mb-3">
    <div class="card-body"><div class="text-muted small">Total Received</div><div class="h4 mb-0">{{ money($total) }}</div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Receipt</th><th>Customer</th><th>Account</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ $payment->customer?->name }}<br><small class="text-muted">{{ $payment->customer?->phone }}</small></td>
                    <td>{{ $payment->sale?->account_number }}</td>
                    <td>{{ money($payment->amount) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->reference_number ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No payments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $payments->links() }}</div>
</div>
@endsection
