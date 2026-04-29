@extends('layouts.print')

@section('title', 'Payment Schedule')

@section('content')
<h2 class="h5 mb-3">Payment Schedule</h2>
<div class="row mb-3">
    <div class="col-md-6">
        <strong>{{ $sale->customer?->name }}</strong><br>
        Phone: {{ $sale->customer?->phone }}<br>
        CNIC: {{ $sale->customer?->cnic ?: '-' }}
    </div>
    <div class="col-md-6 text-md-end">
        Account: <strong>{{ $sale->account_number }}</strong><br>
        Product: {{ $sale->product_name }}<br>
        Sale Value: {{ money($sale->installment_sale_price) }}
    </div>
</div>
<table class="table table-bordered table-sm">
    <thead><tr><th>#</th><th>Due Date</th><th class="text-end">Due</th><th class="text-end">Paid</th><th class="text-end">Remaining</th><th>Paid Date</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($sale->schedules as $schedule)
        <tr>
            <td>{{ $schedule->installment_number }}</td>
            <td>{{ $schedule->due_date?->format('d M Y') }}</td>
            <td class="text-end">{{ money($schedule->due_amount) }}</td>
            <td class="text-end">{{ money($schedule->paid_amount) }}</td>
            <td class="text-end">{{ money($schedule->remaining_amount) }}</td>
            <td>{{ $schedule->paid_at?->format('d M Y') ?: '-' }}</td>
            <td>{{ readable_status($schedule->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-between mt-5">
    <div>Printed: {{ now()->format('d M Y h:i A') }}</div>
    <div class="signature-line">Authorized Signature<br><strong>{{ signature_name() }}</strong></div>
</div>
@endsection
