@extends('layouts.app')

@section('title', 'Create Installment Sale')
@section('subtitle', 'Generate account, ledger debit, advance receipt, and payment schedule')

@section('content')
@php($canViewFinancials = can_view_financials())
<form method="POST" action="{{ route('sales.store') }}" id="saleForm">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-section-title">Customer and Product</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((int) request('customer_id') === $customer->id)>{{ $customer->name }} - {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" id="productSelect" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @if($canViewFinancials) data-cost="{{ $product->cost_price }}" @endif data-price="{{ $product->installment_sale_price }}">
                                        {{ $product->name }} - {{ $product->sku ?: 'No SKU' }} (Stock: {{ $product->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="form-section-title">Installment Plan</div>
                    <div class="row g-3">
                        @if($canViewFinancials)
                            <div class="col-md-4">
                                <label class="form-label">Product cost / investment</label>
                                <input type="number" step="0.01" class="form-control" name="product_cost_price" id="costPrice" value="{{ old('product_cost_price') }}" required>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label">Installment sale price</label>
                            <input type="number" step="0.01" class="form-control" name="installment_sale_price" id="salePrice" value="{{ old('installment_sale_price') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Advance / down payment</label>
                            <input type="number" step="0.01" class="form-control" name="advance_payment" id="advancePayment" value="{{ old('advance_payment', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Number of installments</label>
                            <input type="number" min="1" max="120" class="form-control" name="installments_count" id="installmentsCount" value="{{ old('installments_count', 12) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start date</label>
                            <input type="date" class="form-control" name="installment_start_date" value="{{ old('installment_start_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly due day</label>
                            <input type="number" min="1" max="28" class="form-control" name="monthly_due_day" value="{{ old('monthly_due_day', $defaultDueDay) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly installment</label>
                            <input type="text" class="form-control" id="monthlyAmount" readonly>
                        </div>
                        @if($canViewFinancials)
                            <div class="col-md-4">
                                <label class="form-label">Expected profit</label>
                                <input type="text" class="form-control" id="profitAmount" readonly>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label">Advance method</label>
                            <select class="form-select" name="advance_payment_method">
                                @foreach(payment_methods() as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Advance reference</label>
                            <input class="form-control" name="advance_reference_number" value="{{ old('advance_reference_number') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Advance date</label>
                            <input type="date" class="form-control" name="advance_payment_date" value="{{ old('advance_payment_date', now()->toDateString()) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="send_ledger_whatsapp" value="1" @checked(old('send_ledger_whatsapp'))>
                                Send ledger to customer WhatsApp after creating sale
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card position-sticky" style="top: 90px">
                <div class="card-body">
                    <div class="form-section-title">Summary</div>
                    <div class="d-flex justify-content-between mb-2"><span>Sale value</span><strong id="summarySale">PKR 0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Advance</span><strong id="summaryAdvance">PKR 0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Remaining</span><strong id="summaryRemaining">PKR 0.00</strong></div>
                    @if($canViewFinancials)
                        <div class="d-flex justify-content-between mb-3"><span>Profit</span><strong id="summaryProfit">PKR 0.00</strong></div>
                    @endif
                    <button class="btn btn-primary w-100"><i data-lucide="save"></i> Create Sale</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const currency = @json(company_setting('currency_symbol', 'PKR'));
    const canViewFinancials = @json($canViewFinancials);
    const moneyText = value => `${currency} ${Number(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    const productSelect = document.getElementById('productSelect');
    const costPrice = document.getElementById('costPrice');
    const salePrice = document.getElementById('salePrice');
    const advancePayment = document.getElementById('advancePayment');
    const installmentsCount = document.getElementById('installmentsCount');

    function recalc() {
        const sale = Number(salePrice.value || 0);
        const cost = canViewFinancials ? Number(costPrice.value || 0) : 0;
        const advance = Math.min(Number(advancePayment.value || 0), sale);
        const count = Math.max(Number(installmentsCount.value || 1), 1);
        const remaining = Math.max(sale - advance, 0);
        const monthly = remaining / count;
        const profit = sale - cost;
        document.getElementById('monthlyAmount').value = moneyText(monthly);
        if (canViewFinancials) document.getElementById('profitAmount').value = moneyText(profit);
        document.getElementById('summarySale').textContent = moneyText(sale);
        document.getElementById('summaryAdvance').textContent = moneyText(advance);
        document.getElementById('summaryRemaining').textContent = moneyText(remaining);
        if (canViewFinancials) document.getElementById('summaryProfit').textContent = moneyText(profit);
    }
    productSelect.addEventListener('change', () => {
        const selected = productSelect.selectedOptions[0];
        if (!selected) return;
        if (canViewFinancials) costPrice.value = selected.dataset.cost || '';
        salePrice.value = selected.dataset.price || '';
        recalc();
    });
    [costPrice, salePrice, advancePayment, installmentsCount].filter(Boolean).forEach(input => input.addEventListener('input', recalc));
    recalc();
</script>
@endpush
