@extends('layouts.app')

@section('title', 'Customer Ledger')
@section('subtitle', $customer->name)

@section('content')
<div class="toolbar mb-3">
    <div class="d-flex align-items-center gap-2">
        <img src="{{ company_logo_url() }}" alt="FTC logo" style="height: 42px; width: 42px; object-fit: contain">
        <div>
            <strong>{{ company_setting('company_name', 'FTC') }}</strong>
            <div class="text-muted small">{{ company_setting('company_email', 'contact@ftc.com') }}</div>
        </div>
    </div>
    <form method="GET" class="d-flex flex-wrap gap-2">
        <input type="date" class="form-control" name="from" value="{{ $from }}" style="width: 170px">
        <input type="date" class="form-control" name="to" value="{{ $to }}" style="width: 170px">
        <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.ledger.pdf', array_filter(['customer' => $customer->id, 'from' => $from, 'to' => $to])) }}" class="btn btn-outline-dark"><i data-lucide="printer"></i> Print / PDF</a>
        <a href="{{ route('customers.ledger.pdf', array_filter(['customer' => $customer->id, 'from' => $from, 'to' => $to])) }}" class="btn btn-outline-primary"><i data-lucide="file-down"></i> PDF</a>
        <a href="{{ route('customers.ledger.export', array_filter(['customer' => $customer->id, 'from' => $from, 'to' => $to])) }}" class="btn btn-outline-success"><i data-lucide="download"></i> CSV</a>
        <a href="{{ route('customers.ledger.whatsapp', array_filter(['customer' => $customer->id, 'from' => $from, 'to' => $to])) }}" class="btn btn-success"><i data-lucide="send"></i> WhatsApp</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Total Debit</div><div class="h5 mb-0">{{ money($totalDebit) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Total Credit</div><div class="h5 mb-0">{{ money($totalCredit) }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Current Balance</div><div class="h5 mb-0">{{ money($balance) }}</div></div></div></div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <strong>{{ $customer->name }}</strong>
        <span class="text-muted ms-2">{{ $customer->phone }} {{ $customer->cnic ? '| '.$customer->cnic : '' }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th><th>Method</th><th>Reference</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($ledgers as $ledger)
                <tr>
                    <td>{{ $ledger->entry_date?->format('d M Y') }}</td>
                    <td>{{ $ledger->description }}</td>
                    <td>{{ money($ledger->debit) }}</td>
                    <td>{{ money($ledger->credit) }}</td>
                    <td>{{ money($ledger->balance) }}</td>
                    <td>{{ $ledger->payment_method ?: '-' }}</td>
                    <td>{{ $ledger->reference_number ?: '-' }}</td>
                    <td>{{ $ledger->remarks ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">No ledger entries.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="fw-bold">
                <td colspan="2">Totals</td>
                <td>{{ money($totalDebit) }}</td>
                <td>{{ money($totalCredit) }}</td>
                <td>{{ money($balance) }}</td>
                <td colspan="3"></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
