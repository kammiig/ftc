@extends('layouts.app')

@section('title', 'Record Payment')
@section('subtitle', 'Updates schedule, ledger, sale balance, and receipt')

@section('content')
<form method="POST" action="{{ route('payments.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="form-section-title">Payment Details</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Installment account</label>
                            <select class="form-select" name="installment_sale_id" id="saleSelect" required>
                                <option value="">Select account</option>
                                @foreach($sales as $sale)
                                    <option value="{{ $sale->id }}" data-pending="{{ $sale->pending_balance }}" @selected($selectedSale?->id === $sale->id)>
                                        {{ $sale->account_number }} - {{ $sale->customer?->name }} - {{ $sale->product_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Installment item</label>
                            <select class="form-select" name="installment_schedule_id" id="scheduleSelect">
                                <option value="">Auto allocate to oldest due</option>
                                @foreach($sales as $sale)
                                    @foreach($sale->schedules as $schedule)
                                        <option value="{{ $schedule->id }}" data-sale="{{ $sale->id }}" data-amount="{{ $schedule->remaining_amount }}" @selected($selectedScheduleId === $schedule->id)>
                                            #{{ $schedule->installment_number }} | {{ $schedule->due_date?->format('d M Y') }} | {{ money($schedule->remaining_amount) }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="1" class="form-control" name="amount" id="amountInput" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment date</label>
                            <input type="date" class="form-control" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment method</label>
                            <select class="form-select" name="payment_method" required>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receipt/reference number</label>
                            <input class="form-control" name="reference_number" value="{{ old('reference_number') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Received by</label>
                            <input class="form-control" name="received_by" value="{{ old('received_by', auth()->user()->name) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="3">{{ old('remarks') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="form-section-title">Balance</div>
                    <div class="d-flex justify-content-between mb-2"><span>Account pending</span><strong id="pendingText">PKR 0.00</strong></div>
                    <div class="d-flex justify-content-between mb-3"><span>Selected due</span><strong id="scheduleText">PKR 0.00</strong></div>
                    <button class="btn btn-success w-100"><i data-lucide="save"></i> Record Payment</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const currency = @json(company_setting('currency_symbol', 'PKR'));
    const moneyText = value => `${currency} ${Number(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    const saleSelect = document.getElementById('saleSelect');
    const scheduleSelect = document.getElementById('scheduleSelect');
    const amountInput = document.getElementById('amountInput');

    function refreshSchedules() {
        const saleId = saleSelect.value;
        let firstVisible = null;
        scheduleSelect.querySelectorAll('option').forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = option.dataset.sale !== saleId;
            if (!option.hidden && !firstVisible) firstVisible = option;
        });
        if (scheduleSelect.selectedOptions[0] && scheduleSelect.selectedOptions[0].hidden) {
            scheduleSelect.value = '';
        }
        document.getElementById('pendingText').textContent = moneyText(saleSelect.selectedOptions[0]?.dataset.pending || 0);
        refreshAmount();
    }

    function refreshAmount() {
        const selected = scheduleSelect.selectedOptions[0];
        const dueAmount = selected && selected.value ? Number(selected.dataset.amount || 0) : 0;
        document.getElementById('scheduleText').textContent = moneyText(dueAmount);
        if (dueAmount > 0 && !amountInput.value) amountInput.value = dueAmount.toFixed(2);
    }

    saleSelect.addEventListener('change', refreshSchedules);
    scheduleSelect.addEventListener('change', refreshAmount);
    refreshSchedules();
</script>
@endpush
