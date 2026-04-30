@extends('layouts.app')

@section('title', 'Installment Sales')
@section('subtitle', 'Accounts, schedules, balances, and sale status')

@section('content')
<div class="toolbar mb-3">
    <form class="d-flex flex-wrap gap-2" method="GET">
        <input class="form-control" style="width: 250px" name="search" value="{{ request('search') }}" placeholder="Account, customer, product">
        <select class="form-select" style="width: 160px" name="status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ readable_status($status) }}</option>
            @endforeach
        </select>
        <input type="date" class="form-control" style="width: 160px" name="from" value="{{ request('from') }}">
        <input type="date" class="form-control" style="width: 160px" name="to" value="{{ request('to') }}">
        <button class="btn btn-outline-primary"><i data-lucide="search"></i></button>
    </form>
    <a href="{{ route('sales.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Sale</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Account</th><th>Customer</th><th>Product</th><th>Sale</th><th>Paid</th><th>Pending</th>@if(can_view_financials())<th>Profit</th>@endif<th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td><a class="fw-semibold" href="{{ route('sales.show', $sale) }}">{{ $sale->account_number }}</a><br><small class="text-muted">{{ $sale->installment_start_date?->format('d M Y') }}</small></td>
                    <td>{{ $sale->customer?->name }}<br><small class="text-muted">{{ $sale->customer?->phone }}</small></td>
                    <td>{{ $sale->product_name }}</td>
                    <td>{{ money($sale->installment_sale_price) }}</td>
                    <td>{{ money($sale->total_paid) }}</td>
                    <td>{{ money($sale->pending_balance) }}</td>
                    @if(can_view_financials())<td>{{ money($sale->profit_amount) }}</td>@endif
                    <td>@include('partials.status', ['status' => $sale->status])</td>
                    <td class="text-end">
                        <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-sm btn-success"><i data-lucide="wallet"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ can_view_financials() ? 9 : 8 }}" class="text-center text-muted py-5">No installment sales found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $sales->links() }}</div>
</div>
@endsection
