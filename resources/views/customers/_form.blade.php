@csrf
@isset($method)
    @method($method)
@endisset

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-section-title">Customer Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer name</label>
                        <input class="form-control" name="name" value="{{ old('name', $customer->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Father / husband name</label>
                        <input class="form-control" name="guardian_name" value="{{ old('guardian_name', $customer->guardian_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CNIC / ID card</label>
                        <input class="form-control" name="cnic" value="{{ old('cnic', $customer->cnic) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alternative phone</label>
                        <input class="form-control" name="alternate_phone" value="{{ old('alternate_phone', $customer->alternate_phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input class="form-control" name="city" value="{{ old('city', $customer->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $customer->status) === $status)>{{ readable_status($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Guarantor Details</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Guarantor name</label>
                        <input class="form-control" name="guarantor_name" value="{{ old('guarantor_name', $customer->guarantor_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Guarantor CNIC</label>
                        <input class="form-control" name="guarantor_cnic" value="{{ old('guarantor_cnic', $customer->guarantor_cnic) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Guarantor phone</label>
                        <input class="form-control" name="guarantor_phone" value="{{ old('guarantor_phone', $customer->guarantor_phone) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Guarantor address</label>
                        <textarea class="form-control" name="guarantor_address" rows="2">{{ old('guarantor_address', $customer->guarantor_address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Documents</div>
                <div class="mb-3">
                    <label class="form-label">Customer photo</label>
                    <input class="form-control" type="file" name="photo" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">CNIC front image</label>
                    <input class="form-control" type="file" name="cnic_front" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">CNIC back image</label>
                    <input class="form-control" type="file" name="cnic_back" accept="image/*">
                </div>
                <button class="btn btn-primary w-100" type="submit"><i data-lucide="save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
