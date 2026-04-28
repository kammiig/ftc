@extends('layouts.app')

@section('title', 'Defaulter Customers')
@section('subtitle', 'Customers with overdue installment accounts')

@section('content')
<button class="btn btn-outline-dark mb-3 no-print" onclick="window.print()"><i data-lucide="printer"></i> Print / PDF</button>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Customer</th><th>Phone</th><th>CNIC</th><th>Accounts</th><th>Pending</th><th></th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->cnic ?: '-' }}</td>
                    <td>{{ $customer->sales->pluck('account_number')->implode(', ') }}</td>
                    <td>{{ money($customer->sales->sum('pending_balance')) }}</td>
                    <td class="text-end no-print"><a class="btn btn-sm btn-outline-primary" href="{{ route('customers.show', $customer) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No defaulter customers.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $customers->links() }}</div>
</div>
@endsection
