@extends('layouts.app')

@section('title', 'Company Settings')
@section('subtitle', 'Company identity, payment methods, receipt and ledger text')

@section('content')
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="form-section-title">Company</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company name</label>
                            <input class="form-control" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'FTC') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency symbol</label>
                            <input class="form-control" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'PKR') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Default due day</label>
                            <input type="number" min="1" max="28" class="form-control" name="default_due_day" value="{{ old('default_due_day', $settings['default_due_day'] ?? 1) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input class="form-control" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="company_address" rows="2">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payment methods</label>
                            <input class="form-control" name="payment_methods" value="{{ old('payment_methods', $settings['payment_methods'] ?? 'Cash,Bank Transfer,JazzCash,Easypaisa,Card,Other') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Receipt footer text</label>
                            <textarea class="form-control" name="receipt_footer_text" rows="2">{{ old('receipt_footer_text', $settings['receipt_footer_text'] ?? '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ledger footer text</label>
                            <textarea class="form-control" name="ledger_footer_text" rows="2">{{ old('ledger_footer_text', $settings['ledger_footer_text'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="form-section-title">Logo</div>
                    @if(($settings['company_logo'] ?? null))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['company_logo']) }}" class="img-fluid rounded mb-3" alt="Company logo">
                    @endif
                    <input type="file" class="form-control mb-3" name="company_logo" accept="image/*">
                    <button class="btn btn-primary w-100"><i data-lucide="save"></i> Save Settings</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
