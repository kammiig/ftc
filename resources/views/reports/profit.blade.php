@extends('layouts.app')

@section('title', 'Profit Report')
@section('subtitle', 'Expected profit from installment sale value minus product investment')

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3 no-print">
    <input type="date" class="form-control" style="width: 160px" name="from" value="{{ request('from') }}">
    <input type="date" class="form-control" style="width: 160px" name="to" value="{{ request('to') }}">
    <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
    <button class="btn btn-outline-dark" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print / PDF</button>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Investment</div><div class="h5 mb-0">{{ money($investment) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Sale Value</div><div class="h5 mb-0">{{ money($saleValue) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Expected Profit</div><div class="h5 mb-0">{{ money($profit) }}</div></div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>Account</th><th>Customer</th><th>Cost</th><th>Sale Value</th><th>Profit</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->installment_start_date?->format('d M Y') }}</td>
                    <td>{{ $sale->account_number }}</td>
                    <td>{{ $sale->customer?->name }}</td>
                    <td>{{ money($sale->product_cost_price) }}</td>
                    <td>{{ money($sale->installment_sale_price) }}</td>
                    <td>{{ money($sale->profit_amount) }}</td>
                    <td>@include('partials.status', ['status' => $sale->status])</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $sales->links() }}</div>
</div>
@endsection
