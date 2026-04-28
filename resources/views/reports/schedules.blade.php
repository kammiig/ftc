@extends('layouts.app')

@section('title', $title)
@section('subtitle', 'Pending amounts, due dates, and customer contact details')

@section('content')
@include('reports._filters')

<div class="card mb-3">
    <div class="card-body"><div class="text-muted small">Total Remaining</div><div class="h4 mb-0">{{ money($total) }}</div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Due Date</th><th>Customer</th><th>Account</th><th>Product</th><th>Installment</th><th>Due</th><th>Paid</th><th>Remaining</th><th>Paid Date</th><th>Status</th><th>Days</th></tr></thead>
            <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->due_date?->format('d M Y') }}</td>
                    <td>{{ $schedule->customer?->name }}<br><small class="text-muted">{{ $schedule->customer?->phone }}</small></td>
                    <td>{{ $schedule->sale?->account_number }}</td>
                    <td>{{ $schedule->sale?->product_name }}</td>
                    <td>{{ $schedule->installment_number }}</td>
                    <td>{{ money($schedule->due_amount) }}</td>
                    <td>{{ money($schedule->paid_amount) }}</td>
                    <td>{{ money($schedule->remaining_amount) }}</td>
                    <td>{{ $schedule->paid_at?->format('d M Y') ?: '-' }}</td>
                    <td>@include('partials.status', ['status' => $schedule->status])</td>
                    <td>{{ max(0, (int) $schedule->due_date?->diffInDays(now(), false)) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-5">No schedule entries found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white no-print">{{ $schedules->links() }}</div>
</div>
@endsection
