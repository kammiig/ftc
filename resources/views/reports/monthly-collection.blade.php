@extends('layouts.app')

@section('title', 'Monthly Collection Report')
@section('subtitle', 'Month-wise received payment totals')

@section('content')
<form method="GET" class="d-flex flex-wrap gap-2 mb-3 no-print">
    <input type="date" class="form-control" style="width: 170px" name="from" value="{{ $from }}">
    <input type="date" class="form-control" style="width: 170px" name="to" value="{{ $to }}">
    <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
    <a class="btn btn-outline-success" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"><i data-lucide="download"></i> CSV</a>
    <button class="btn btn-outline-dark" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print / PDF</button>
</form>

<div class="card mb-3"><div class="card-body"><div class="text-muted small">Total Collection</div><div class="h4 mb-0">{{ money($total) }}</div></div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Month</th><th>Payments</th><th class="text-end">Total Received</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::create((int) $row->year, (int) $row->month)->format('M Y') }}</td>
                    <td>{{ $row->payments_count }}</td>
                    <td class="text-end">{{ money($row->total_amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-5">No collections found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
