@extends('layouts.app')

@section('title', 'Pending & Overdue')
@section('subtitle', 'Due installments, missed customers, and direct collection links')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Customer, account, product">
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select class="form-select" name="customer_id">
                    <option value="">All customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((int) request('customer_id') === $customer->id)>{{ $customer->name }} - {{ $customer->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select" name="product_id">
                    <option value="">All products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected((int) request('product_id') === $product->id)>{{ $product->name }} {{ $product->sku ? '- '.$product->sku : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    @foreach(['pending','partial','overdue'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ readable_status($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <input type="month" class="form-control" name="month" value="{{ request('month') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" class="form-control" name="from" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" class="form-control" name="to" value="{{ request('to') }}">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
            </div>
            <div class="col-12 d-flex flex-wrap gap-3 mt-2">
                <label class="form-check"><input type="checkbox" class="form-check-input" name="overdue_only" value="1" @checked(request('overdue_only'))> Overdue only</label>
                <label class="form-check"><input type="checkbox" class="form-check-input" name="due_today" value="1" @checked(request('due_today'))> Due today</label>
                <label class="form-check"><input type="checkbox" class="form-check-input" name="due_week" value="1" @checked(request('due_week'))> Due this week</label>
                <label class="form-check"><input type="checkbox" class="form-check-input" name="due_month" value="1" @checked(request('due_month'))> Due this month</label>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-white"><strong>Open Installments</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Due Date</th><th>Customer</th><th>Guarantor Phone</th><th>Product</th><th>Account</th><th>Due</th><th>Paid</th><th>Remaining</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->due_date?->format('d M Y') }}<br><small class="text-muted">{{ max(0, (int) $schedule->due_date?->diffInDays(now(), false)) }} days overdue</small></td>
                            <td>{{ $schedule->customer?->name }}<br><small class="text-muted">{{ $schedule->customer?->phone }}</small></td>
                            <td>{{ $schedule->customer?->guarantors->pluck('phone')->filter()->implode(', ') ?: '-' }}</td>
                            <td>{{ $schedule->sale?->product_name }}</td>
                            <td><a href="{{ route('sales.show', $schedule->sale) }}">{{ $schedule->sale?->account_number }}</a></td>
                            <td>{{ money($schedule->due_amount) }}</td>
                            <td>{{ money($schedule->paid_amount) }}</td>
                            <td>{{ money($schedule->remaining_amount) }}</td>
                            <td>@include('partials.status', ['status' => $schedule->status])</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-dark" href="{{ route('customers.ledger', $schedule->customer) }}">Ledger</a>
                                    <a class="btn btn-success" href="{{ route('payments.create', ['sale_id' => $schedule->installment_sale_id, 'schedule_id' => $schedule->id]) }}">Collect</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-5">No open installments found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">{{ $schedules->links() }}</div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header bg-white"><strong>No Payment This Month</strong></div>
            <div class="list-group list-group-flush">
                @forelse($missedCustomers as $customer)
                    <a class="list-group-item d-flex justify-content-between align-items-start" href="{{ route('customers.show', $customer) }}">
                        <span>{{ $customer->name }}<br><small class="text-muted">{{ $customer->phone }}</small></span>
                        @include('partials.status', ['status' => $customer->status])
                    </a>
                @empty
                    <div class="list-group-item text-muted">No missed customers for this month.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
