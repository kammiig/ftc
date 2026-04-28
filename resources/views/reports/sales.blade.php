@extends('layouts.app')

@section('title', 'Installment Sales Report')
@section('subtitle', 'Sale value, investment, paid amount, pending balance, and profit')

@section('content')
@include('reports._filters', ['statuses' => \App\Models\InstallmentSale::STATUSES])

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Investment</div><div class="h5 mb-0">{{ money($investment) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Sale Value</div><div class="h5 mb-0">{{ money($saleValue) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Expected Profit</div><div class="h5 mb-0">{{ money($profit) }}</div></div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Account</th><th>Customer</th><th>Product</th><th>Cost</th><th>Sale</th><th>Paid</th><th>Pending</th><th>Profit</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->installment_start_date?->format('d M Y') }}</td>
                    <td>{{ $sale->account_number }}</td>
                    <td>{{ $sale->customer?->name }}</td>
                    <td>{{ $sale->product_name }}</td>
                    <td>{{ money($sale->product_cost_price) }}</td>
                    <td>{{ money($sale->installment_sale_price) }}</td>
                    <td>{{ money($sale->total_paid) }}</td>
                    <td>{{ money($sale->pending_balance) }}</td>
                    <td>{{ money($sale->profit_amount) }}</td>
                    <td>@include('partials.status', ['status' => $sale->status])</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-5">No sales found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $sales->links() }}</div>
</div>
@endsection
