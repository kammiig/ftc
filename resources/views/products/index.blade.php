@extends('layouts.app')

@section('title', 'Products')
@section('subtitle', 'Inventory, prices, and installment sale values')

@section('content')
<div class="toolbar mb-3">
    <form class="d-flex flex-wrap gap-2" method="GET">
        <input class="form-control" style="width: 260px" name="search" value="{{ request('search') }}" placeholder="Name, SKU, category">
        <select class="form-select" style="width: 170px" name="status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ readable_status($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary"><i data-lucide="search"></i></button>
    </form>
    <a href="{{ route('products.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Product</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Product</th><th>SKU</th><th>Cost</th><th>Cash</th><th>Installment</th><th>Stock</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->image_path)
                                <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <span class="avatar"><i data-lucide="package"></i></span>
                            @endif
                            <div>
                                <a class="fw-semibold" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                <div class="text-muted small">{{ $product->category }} {{ $product->brand_model ? '| '.$product->brand_model : '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->sku ?: '-' }}</td>
                    <td>{{ money($product->cost_price) }}</td>
                    <td>{{ money($product->cash_sale_price) }}</td>
                    <td>{{ money($product->installment_sale_price) }}</td>
                    <td>{{ $product->stock_quantity }}</td>
                    <td>@include('partials.status', ['status' => $product->status])</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('products.edit', $product) }}" title="Edit"><i data-lucide="pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No products found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $products->links() }}</div>
</div>
@endsection
