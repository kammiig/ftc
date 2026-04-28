@extends('layouts.app')

@section('title', 'Customers')
@section('subtitle', 'Profiles, guarantors, ledgers, and installment history')

@section('content')
<div class="toolbar mb-3">
    <form class="d-flex flex-wrap gap-2" method="GET">
        <input class="form-control" style="width: 260px" name="search" value="{{ request('search') }}" placeholder="Name, phone, CNIC">
        <select class="form-select" style="width: 170px" name="status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ readable_status($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary"><i data-lucide="search"></i></button>
    </form>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i data-lucide="plus"></i> Customer</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>Customer</th>
                <th>Account</th>
                <th>CNIC</th>
                <th>Phone</th>
                <th>City</th>
                <th>Sales</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($customer->photo_path)
                                <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($customer->photo_path) }}" alt="{{ $customer->name }}">
                            @else
                                <span class="avatar">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                            @endif
                            <div>
                                <a class="fw-semibold" href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                                <div class="text-muted small">{{ $customer->guardian_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $customer->account_number }}</td>
                    <td>{{ $customer->cnic ?: '-' }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->city ?: '-' }}</td>
                    <td>{{ $customer->sales_count }}</td>
                    <td>@include('partials.status', ['status' => $customer->status])</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a class="btn btn-outline-secondary" href="{{ route('customers.ledger', $customer) }}" title="Ledger"><i data-lucide="book-open"></i></a>
                            <a class="btn btn-outline-primary" href="{{ route('customers.edit', $customer) }}" title="Edit"><i data-lucide="pencil"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $customers->links() }}
    </div>
</div>
@endsection
