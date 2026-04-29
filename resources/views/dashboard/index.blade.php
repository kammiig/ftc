@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Business performance and payment alerts')

@section('content')
@php
    $cards = [
        ['label' => 'Customers', 'value' => $metrics['total_customers'], 'icon' => 'users'],
        ['label' => 'Active Accounts', 'value' => $metrics['active_accounts'], 'icon' => 'activity'],
        ['label' => 'Completed Accounts', 'value' => $metrics['completed_accounts'], 'icon' => 'check-circle-2'],
        ['label' => 'Products Sold', 'value' => $metrics['products_sold'], 'icon' => 'package-check'],
        ['label' => 'Investment', 'value' => money($metrics['total_investment']), 'icon' => 'landmark'],
        ['label' => 'Sale Value', 'value' => money($metrics['total_sale_value']), 'icon' => 'receipt'],
        ['label' => 'Expected Profit', 'value' => money($metrics['expected_profit']), 'icon' => 'trending-up'],
        ['label' => 'Received', 'value' => money($metrics['amount_received']), 'icon' => 'wallet'],
        ['label' => 'Received This Month', 'value' => money($metrics['amount_received_month']), 'icon' => 'calendar-check'],
        ['label' => 'Pending', 'value' => money($metrics['pending_amount']), 'icon' => 'hourglass'],
        ['label' => 'Overdue', 'value' => money($metrics['overdue_amount']), 'icon' => 'triangle-alert'],
        ['label' => 'Missed This Month', 'value' => $metrics['missed_this_month'], 'icon' => 'calendar-x'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach($cards as $card)
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card">
                <div class="card-body d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">{{ $card['label'] }}</div>
                        <div class="h4 mb-0 mt-2">{{ $card['value'] }}</div>
                    </div>
                    <span class="metric-icon"><i data-lucide="{{ $card['icon'] }}"></i></span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <strong>Monthly Collection</strong>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('reports.payments') }}" class="btn btn-outline-secondary btn-sm">Report</a>
                @endif
            </div>
            <div class="card-body">
                <canvas id="collectionChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Alerts</strong></div>
            <div class="list-group list-group-flush">
                <a class="list-group-item d-flex justify-content-between" href="{{ route('pending.index', ['due_today' => 1]) }}">
                    Due today <span class="badge text-bg-primary">{{ $metrics['due_today'] }}</span>
                </a>
                <a class="list-group-item d-flex justify-content-between" href="{{ route('pending.index', ['due_week' => 1]) }}">
                    Due this week <span class="badge text-bg-info">{{ $metrics['due_this_week'] }}</span>
                </a>
                <a class="list-group-item d-flex justify-content-between" href="{{ route('pending.index', ['overdue_only' => 1]) }}">
                    Overdue installments <span class="badge text-bg-danger">{{ $metrics['overdue_installments'] }}</span>
                </a>
                <a class="list-group-item d-flex justify-content-between" href="{{ route('products.index', ['status' => 'available']) }}">
                    Low stock products <span class="badge text-bg-warning">{{ $metrics['low_stock'] }}</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Installments Due Today</strong>
                <a href="{{ route('pending.index', ['due_today' => 1]) }}" class="btn btn-sm btn-outline-secondary">View</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Customer</th><th>Account</th><th>Due</th><th></th></tr></thead>
                    <tbody>
                    @forelse($dueToday as $schedule)
                        <tr>
                            <td>{{ $schedule->customer?->name }}<br><small class="text-muted">{{ $schedule->customer?->phone }}</small></td>
                            <td>{{ $schedule->sale?->account_number }}</td>
                            <td>{{ money($schedule->remaining_amount) }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-success" href="{{ route('payments.create', ['sale_id' => $schedule->installment_sale_id, 'schedule_id' => $schedule->id]) }}">Collect</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No installments due today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Overdue Installments</strong>
                <a href="{{ route('pending.index', ['overdue_only' => 1]) }}" class="btn btn-sm btn-outline-secondary">View</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Customer</th><th>Due Date</th><th>Amount</th><th></th></tr></thead>
                    <tbody>
                    @forelse($overdue as $schedule)
                        <tr>
                            <td>{{ $schedule->customer?->name }}<br><small class="text-muted">{{ $schedule->customer?->phone }}</small></td>
                            <td>{{ $schedule->due_date?->format('d M Y') }}</td>
                            <td>{{ money($schedule->remaining_amount) }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-success" href="{{ route('payments.create', ['sale_id' => $schedule->installment_sale_id, 'schedule_id' => $schedule->id]) }}">Collect</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No overdue installments.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header bg-white"><strong>Recent Payments</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Receipt</th><th>Customer</th><th>Amount</th><th>Date</th></tr></thead>
                    <tbody>
                    @forelse($recentPayments as $payment)
                        <tr>
                            <td><a href="{{ route('payments.receipt', $payment) }}">{{ $payment->receipt_number }}</a></td>
                            <td>{{ $payment->customer?->name }}</td>
                            <td>{{ money($payment->amount) }}</td>
                            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header bg-white"><strong>Recent Installment Sales</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Account</th><th>Customer</th><th>Product</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->account_number }}</a></td>
                            <td>{{ $sale->customer?->name }}</td>
                            <td>{{ $sale->product_name }}</td>
                            <td>@include('partials.status', ['status' => $sale->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No sales created.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chart = @json($collectionsChart);
    new Chart(document.getElementById('collectionChart'), {
        type: 'bar',
        data: {
            labels: chart.labels,
            datasets: [{
                label: 'Received',
                data: chart.data,
                backgroundColor: '#082c9d',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {legend: {display: false}},
            scales: {y: {beginAtZero: true}}
        }
    });
</script>
@endpush
