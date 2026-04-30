@extends('layouts.app')

@section('title', 'Product Details')
@section('subtitle', $product->name)

@section('content')
<div class="toolbar mb-3">
    <div>
        <h2 class="h5 mb-0">{{ $product->name }}</h2>
        <div class="text-muted">{{ $product->category }} {{ $product->sku ? '| '.$product->sku : '' }}</div>
    </div>
    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary"><i data-lucide="pencil"></i> Edit</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                @if($product->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" class="img-fluid rounded mb-3" alt="{{ $product->name }}">
                @endif
                <dl class="row mb-0">
                    <dt class="col-5">Brand/model</dt><dd class="col-7">{{ $product->brand_model ?: '-' }}</dd>
                    <dt class="col-5">Stock</dt><dd class="col-7">{{ $product->stock_quantity }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">@include('partials.status', ['status' => $product->status])</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            @if(can_view_financials())
                <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Cost</div><div class="h5 mb-0">{{ money($product->cost_price) }}</div></div></div></div>
            @endif
            <div class="{{ can_view_financials() ? 'col-md-4' : 'col-md-6' }}"><div class="card"><div class="card-body"><div class="text-muted small">Cash Price</div><div class="h5 mb-0">{{ money($product->cash_sale_price) }}</div></div></div></div>
            <div class="{{ can_view_financials() ? 'col-md-4' : 'col-md-6' }}"><div class="card"><div class="card-body"><div class="text-muted small">Installment Price</div><div class="h5 mb-0">{{ money($product->installment_sale_price) }}</div></div></div></div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Installment Sales</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Account</th><th>Customer</th><th>Sale Value</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($product->sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->account_number }}</a></td>
                            <td>{{ $sale->customer?->name }}</td>
                            <td>{{ money($sale->installment_sale_price) }}</td>
                            <td>@include('partials.status', ['status' => $sale->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No sales for this product.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
