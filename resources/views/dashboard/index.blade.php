@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Simple payment overview for daily office work')

@section('content')
@php
    $cards = [
        ['label' => 'Total Customers', 'value' => $metrics['total_customers'], 'icon' => 'users'],
        ['label' => 'Active Installment Accounts', 'value' => $metrics['active_accounts'], 'icon' => 'activity'],
        ['label' => 'Payments Received This Month', 'value' => money($metrics['amount_received_month']), 'icon' => 'calendar-check'],
        ['label' => 'Pending Amount', 'value' => money($metrics['pending_amount']), 'icon' => 'hourglass'],
        ['label' => 'Overdue Amount', 'value' => money($metrics['overdue_amount']), 'icon' => 'triangle-alert'],
        ['label' => 'Due Today', 'value' => $metrics['due_today'], 'icon' => 'calendar-clock'],
        ['label' => 'Due This Week', 'value' => $metrics['due_this_week'], 'icon' => 'calendar-days'],
        ['label' => 'Recent Payments', 'value' => $recentPayments->count(), 'icon' => 'wallet'],
    ];

    if (can_view_financials()) {
        $cards[] = ['label' => 'Total Investment', 'value' => money($metrics['total_investment'] ?? 0), 'icon' => 'landmark'];
        $cards[] = ['label' => 'Total Profit', 'value' => money($metrics['expected_profit'] ?? 0), 'icon' => 'trending-up'];
    }
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

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Due Today</strong>
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
                <strong>Overdue Payments</strong>
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
                        <tr><td colspan="4" class="text-center text-muted py-4">No overdue payments.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
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
</div>
@endsection
