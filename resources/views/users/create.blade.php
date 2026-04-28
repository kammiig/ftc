@extends('layouts.app')

@section('title', 'Add User')
@section('subtitle', 'Create admin or staff access')

@section('content')
<form method="POST" action="{{ route('users.store') }}">
    @include('users._form')
</form>
@endsection
