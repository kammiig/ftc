@extends('layouts.app')

@section('title', 'Product-wise Sale Report')
@section('subtitle', 'Product performance across installment sales')

@section('content')
@include('reports._filters', ['statuses' => $statuses])

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Product</th><th>SKU</th><th>Sales</th><th>Investment</th><th>Sale Value</th><th>Profit</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?: '-' }}</td>
                    <td>{{ $product->sales_count }}</td>
                    <td>{{ money($product->investment) }}</td>
                    <td>{{ money($product->sale_value) }}</td>
                    <td>{{ money($product->profit) }}</td>
                    <td>@include('partials.status', ['status' => $product->status])</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No product sales found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
