@extends('layouts.app')

@section('title', 'Add Customer')
@section('subtitle', 'Create a customer profile and guarantor record')

@section('content')
<form method="POST" action="{{ route('customers.store') }}" enctype="multipart/form-data">
    @include('customers._form')
</form>
@endsection
