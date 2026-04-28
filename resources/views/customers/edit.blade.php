@extends('layouts.app')

@section('title', 'Edit Customer')
@section('subtitle', $customer->name)

@section('content')
<form method="POST" action="{{ route('customers.update', $customer) }}" enctype="multipart/form-data">
    @include('customers._form', ['method' => 'PUT'])
</form>

<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="mt-3" onsubmit="return confirm('Delete this customer?')">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline-danger"><i data-lucide="trash-2"></i> Delete Customer</button>
</form>
@endsection
