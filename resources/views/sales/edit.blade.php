@extends('layouts.app')

@section('title', 'Edit Installment Account')
@section('subtitle', $sale->account_number)

@section('content')
<form method="POST" action="{{ route('sales.update', $sale) }}">
    @csrf
    @method('PUT')
    <div class="card" style="max-width: 720px">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $sale->status) === $status)>{{ readable_status($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" rows="4">{{ old('remarks', $sale->remarks) }}</textarea>
            </div>
            <button class="btn btn-primary"><i data-lucide="save"></i> Save</button>
        </div>
    </div>
</form>
@endsection
