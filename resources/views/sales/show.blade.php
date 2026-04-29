@extends('layouts.app')

@section('title', 'Installment Account')
@section('subtitle', $sale->account_number)

@section('content')
<div class="toolbar mb-3">
    <div>
        <h2 class="h5 mb-0">{{ $sale->account_number }}</h2>
        <div class="text-muted">{{ $sale->customer?->name }} | {{ $sale->product_name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-success"><i data-lucide="wallet"></i> Payment</a>
        <a href="{{ route('sales.schedule.print', $sale) }}" class="btn btn-outline-dark"><i data-lucide="printer"></i> Schedule</a>
        <form method="POST" action="{{ route('sales.ledger.whatsapp', $sale) }}">
            @csrf
            <button class="btn btn-outline-success" type="submit"><i data-lucide="send"></i> Ledger</button>
        </form>
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-outline-primary"><i data-lucide="pencil"></i></a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Sale Value</div><div class="h5 mb-0">{{ money($sale->installment_sale_price) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Paid</div><div class="h5 mb-0">{{ money($sale->total_paid) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Pending</div><div class="h5 mb-0">{{ money($sale->pending_balance) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Profit</div><div class="h5 mb-0">{{ money($sale->profit_amount) }}</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header bg-white"><strong>Payment Schedule</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Due Date</th><th>Due</th><th>Paid</th><th>Remaining</th><th>Paid Date</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @foreach($sale->schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->installment_number }}</td>
                            <td>{{ $schedule->due_date?->format('d M Y') }}</td>
                            <td>{{ money($schedule->due_amount) }}</td>
                            <td>{{ money($schedule->paid_amount) }}</td>
                            <td>{{ money($schedule->remaining_amount) }}</td>
                            <td>{{ $schedule->paid_at?->format('d M Y') ?: '-' }}</td>
                            <td>@include('partials.status', ['status' => $schedule->status])</td>
                            <td class="text-end">
                                @if($schedule->remaining_amount > 0)
                                    <a class="btn btn-sm btn-success" href="{{ route('payments.create', ['sale_id' => $sale->id, 'schedule_id' => $schedule->id]) }}">Collect</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Account Details</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6">Customer</dt><dd class="col-6"><a href="{{ route('customers.show', $sale->customer) }}">{{ $sale->customer?->name }}</a></dd>
                    <dt class="col-6">Phone</dt><dd class="col-6">{{ $sale->customer?->phone }}</dd>
                    <dt class="col-6">Cost</dt><dd class="col-6">{{ money($sale->product_cost_price) }}</dd>
                    <dt class="col-6">Advance</dt><dd class="col-6">{{ money($sale->advance_payment) }}</dd>
                    <dt class="col-6">Installments</dt><dd class="col-6">{{ $sale->installments_count }} x {{ money($sale->monthly_installment_amount) }}</dd>
                    <dt class="col-6">Due day</dt><dd class="col-6">{{ $sale->monthly_due_day }}</dd>
                    <dt class="col-6">Status</dt><dd class="col-6">@include('partials.status', ['status' => $sale->status])</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Payments</strong></div>
            <div class="list-group list-group-flush">
                @forelse($sale->payments as $payment)
                    <a class="list-group-item d-flex justify-content-between" href="{{ route('payments.receipt', $payment) }}">
                        <span>{{ $payment->receipt_number }}<br><small class="text-muted">{{ $payment->payment_date?->format('d M Y') }}</small></span>
                        <strong>{{ money($payment->amount) }}</strong>
                    </a>
                @empty
                    <div class="list-group-item text-muted">No payments recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
