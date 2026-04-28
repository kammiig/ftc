@extends('layouts.app')

@section('title', 'Daily Collection')
@section('subtitle', $date)

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3 no-print">
    <input type="date" class="form-control" style="width: 180px" name="date" value="{{ $date }}">
    <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
    <button class="btn btn-outline-dark" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print / PDF</button>
</form>
<div class="card mb-3"><div class="card-body"><div class="text-muted small">Total Collection</div><div class="h4 mb-0">{{ money($total) }}</div></div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Receipt</th><th>Customer</th><th>Account</th><th>Amount</th><th>Method</th><th>Received By</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ $payment->customer?->name }}</td>
                    <td>{{ $payment->sale?->account_number }}</td>
                    <td>{{ money($payment->amount) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->received_by }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No payments on this date.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $payments->links() }}</div>
</div>
@endsection
