@extends('layouts.app')

@section('title', 'Add Product')
@section('subtitle', 'Create product pricing and stock details')

@section('content')
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
    @include('products._form')
</form>
@endsection
