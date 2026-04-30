@extends('layouts.app')

@section('title', 'Customer Profile')
@section('subtitle', $customer->name)

@section('content')
<div class="toolbar mb-3">
    <div class="d-flex align-items-center gap-3">
        @if($customer->photo_path)
            <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($customer->photo_path) }}" alt="{{ $customer->name }}">
        @else
            <span class="avatar">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
        @endif
        <div>
            <div class="h5 mb-0">{{ $customer->name }}</div>
            <div class="text-muted">{{ $customer->phone }} {{ $customer->whatsapp_number ? '| WhatsApp '.$customer->whatsapp_number : '' }} {{ $customer->cnic ? '| '.$customer->cnic : '' }}</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.print', $customer) }}" class="btn btn-outline-secondary"><i data-lucide="printer"></i> Profile</a>
        <a href="{{ route('customers.ledger', $customer) }}" class="btn btn-outline-dark"><i data-lucide="book-open"></i> Ledger</a>
        <a href="{{ route('customers.ledger.pdf', $customer) }}" class="btn btn-outline-success"><i data-lucide="download"></i> PDF</a>
        <a href="{{ route('customers.ledger.whatsapp', $customer) }}" class="btn btn-success"><i data-lucide="send"></i> WhatsApp</a>
        <a href="{{ route('sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary"><i data-lucide="file-plus-2"></i> Sale</a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary"><i data-lucide="pencil"></i></a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total Sale</div><div class="h5 mb-0">{{ money($customer->sales->sum('installment_sale_price')) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Paid</div><div class="h5 mb-0">{{ money($customer->sales->sum('total_paid')) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Pending</div><div class="h5 mb-0">{{ money($customer->sales->sum('pending_balance')) }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Overdue</div><div class="h5 mb-0">{{ money($customer->sales->flatMap->schedules->where('status', 'overdue')->sum('remaining_amount')) }}</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Personal Details</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Father/Husband</dt><dd class="col-7">{{ $customer->guardian_name ?: '-' }}</dd>
                    <dt class="col-5">CNIC</dt><dd class="col-7">{{ $customer->cnic ?: '-' }}</dd>
                    <dt class="col-5">WhatsApp</dt><dd class="col-7">{{ $customer->whatsapp_number ?: $customer->phone }}</dd>
                    <dt class="col-5">Address</dt><dd class="col-7">{{ $customer->address ?: '-' }}</dd>
                    <dt class="col-5">City</dt><dd class="col-7">{{ $customer->city ?: '-' }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">@include('partials.status', ['status' => $customer->status])</dd>
                </dl>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Uploaded Documents</strong></div>
            <div class="card-body d-flex flex-wrap gap-2">
                @if($customer->photo_path)
                    <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($customer->photo_path) }}" alt="Customer photo">
                @endif
                @if($customer->cnic_front_path)
                    <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($customer->cnic_front_path) }}" alt="CNIC front">
                @endif
                @if($customer->cnic_back_path)
                    <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($customer->cnic_back_path) }}" alt="CNIC back">
                @endif
                @if(! $customer->photo_path && ! $customer->cnic_front_path && ! $customer->cnic_back_path)
                    <span class="text-muted">No documents uploaded.</span>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Guarantors</strong></div>
            <div class="card-body">
                @forelse($customer->guarantors as $guarantor)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex gap-2 align-items-start">
                            @if($guarantor->photo_path)
                                <img class="avatar" src="{{ \Illuminate\Support\Facades\Storage::url($guarantor->photo_path) }}" alt="{{ $guarantor->full_name }}">
                            @endif
                            <dl class="row mb-0 flex-grow-1">
                                <dt class="col-5">Name</dt><dd class="col-7">{{ $guarantor->full_name ?: '-' }}</dd>
                                <dt class="col-5">Father/Husband</dt><dd class="col-7">{{ $guarantor->guardian_name ?: '-' }}</dd>
                                <dt class="col-5">CNIC</dt><dd class="col-7">{{ $guarantor->cnic ?: '-' }}</dd>
                                <dt class="col-5">Phone</dt><dd class="col-7">{{ $guarantor->phone ?: '-' }}</dd>
                                <dt class="col-5">Alt Phone</dt><dd class="col-7">{{ $guarantor->alternate_phone ?: '-' }}</dd>
                                <dt class="col-5">Relationship</dt><dd class="col-7">{{ $guarantor->relationship ?: '-' }}</dd>
                                <dt class="col-5">Address</dt><dd class="col-7">{{ $guarantor->address ?: '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">No guarantor added</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Installment Accounts</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Account</th><th>Product</th><th>Sale</th><th>Paid</th><th>Pending</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($customer->sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->account_number }}</a></td>
                            <td>{{ $sale->product_name }}</td>
                            <td>{{ money($sale->installment_sale_price) }}</td>
                            <td>{{ money($sale->total_paid) }}</td>
                            <td>{{ money($sale->pending_balance) }}</td>
                            <td>@include('partials.status', ['status' => $sale->status])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No installment accounts.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>Ledger Preview</strong>
                <a href="{{ route('customers.ledger.print', $customer) }}" class="btn btn-sm btn-outline-secondary"><i data-lucide="printer"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead>
                    <tbody>
                    @forelse($customer->ledgers->take(-8) as $ledger)
                        <tr>
                            <td>{{ $ledger->entry_date?->format('d M Y') }}</td>
                            <td>{{ $ledger->description }}</td>
                            <td>{{ money($ledger->debit) }}</td>
                            <td>{{ money($ledger->credit) }}</td>
                            <td>{{ money($ledger->balance) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No ledger entries.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header bg-white"><strong>Payment History</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Receipt</th><th>Account</th><th>Amount</th><th>Method</th></tr></thead>
                    <tbody>
                    @forelse($customer->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                            <td><a href="{{ route('payments.receipt', $payment) }}">{{ $payment->receipt_number }}</a></td>
                            <td>{{ $payment->sale?->account_number }}</td>
                            <td>{{ money($payment->amount) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No payments found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
