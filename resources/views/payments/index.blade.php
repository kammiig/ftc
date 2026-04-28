@extends('layouts.app')

@section('title', 'Payments')
@section('subtitle', 'Received payments, receipts, and references')

@section('content')
<div class="toolbar mb-3">
    <form class="d-flex flex-wrap gap-2" method="GET">
        <input class="form-control" style="width: 230px" name="search" value="{{ request('search') }}" placeholder="Receipt, account, customer">
        <input type="date" class="form-control" style="width: 160px" name="from" value="{{ request('from') }}">
        <input type="date" class="form-control" style="width: 160px" name="to" value="{{ request('to') }}">
        <select class="form-select" style="width: 170px" name="method">
            <option value="">All methods</option>
            @foreach($paymentMethods as $method)
                <option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary"><i data-lucide="search"></i></button>
    </form>
    <a href="{{ route('payments.create') }}" class="btn btn-success"><i data-lucide="plus"></i> Payment</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Receipt</th><th>Date</th><th>Customer</th><th>Account</th><th>Amount</th><th>Method</th><th>Reference</th><th></th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><a class="fw-semibold" href="{{ route('payments.receipt', $payment) }}">{{ $payment->receipt_number }}</a></td>
                    <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                    <td>{{ $payment->customer?->name }}<br><small class="text-muted">{{ $payment->customer?->phone }}</small></td>
                    <td>{{ $payment->sale?->account_number }}</td>
                    <td>{{ money($payment->amount) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->reference_number ?: '-' }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-dark" href="{{ route('payments.print', $payment) }}"><i data-lucide="printer"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $payments->links() }}</div>
</div>
@endsection
