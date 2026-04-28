@extends('layouts.app')

@section('title', 'Edit Product')
@section('subtitle', $product->name)

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
    @include('products._form', ['method' => 'PUT'])
</form>

<form method="POST" action="{{ route('products.destroy', $product) }}" class="mt-3" onsubmit="return confirm('Delete this product?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline-danger"><i data-lucide="trash-2"></i> Delete Product</button>
</form>
@endsection
