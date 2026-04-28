@extends('layouts.app')

@section('title', 'Customer Ledger Report')
@section('subtitle', 'Customer-wise debit, credit, and current balance')

@section('content')
@include('reports._filters', ['statuses' => $statuses])

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Customer</th><th>CNIC</th><th>Phone</th><th>Sales</th><th>Total Debit</th><th>Total Credit</th><th>Balance</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->cnic ?: '-' }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->sales_count }}</td>
                    <td>{{ money($customer->totalDebit($from ?: null, $to ?: null)) }}</td>
                    <td>{{ money($customer->totalCredit($from ?: null, $to ?: null)) }}</td>
                    <td>{{ money($customer->currentBalance()) }}</td>
                    <td>@include('partials.status', ['status' => $customer->status])</td>
                    <td class="text-end no-print"><a class="btn btn-sm btn-outline-dark" href="{{ route('customers.ledger', $customer) }}">Ledger</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-5">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $customers->links() }}</div>
</div>
@endsection
