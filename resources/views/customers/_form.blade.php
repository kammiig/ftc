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

        @foreach([1, 2] as $position)
            @php($guarantor = $guarantors[$position])
            <div class="card mb-3">
                <div class="card-body">
                    <div class="form-section-title">Guarantor {{ $position }} Details</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full name</label>
                            <input class="form-control" name="guarantors[{{ $position }}][full_name]" value="{{ old("guarantors.$position.full_name", $guarantor->full_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father / husband name</label>
                            <input class="form-control" name="guarantors[{{ $position }}][guardian_name]" value="{{ old("guarantors.$position.guardian_name", $guarantor->guardian_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CNIC / ID card</label>
                            <input class="form-control" name="guarantors[{{ $position }}][cnic]" value="{{ old("guarantors.$position.cnic", $guarantor->cnic) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input class="form-control" name="guarantors[{{ $position }}][phone]" value="{{ old("guarantors.$position.phone", $guarantor->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alternative phone</label>
                            <input class="form-control" name="guarantors[{{ $position }}][alternate_phone]" value="{{ old("guarantors.$position.alternate_phone", $guarantor->alternate_phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Relationship</label>
                            <input class="form-control" name="guarantors[{{ $position }}][relationship]" value="{{ old("guarantors.$position.relationship", $guarantor->relationship) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Photo</label>
                            <input class="form-control js-image-input" type="file" name="guarantors[{{ $position }}][photo]" accept="image/*" data-preview="guarantor-photo-{{ $position }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC front image</label>
                            <input class="form-control js-image-input" type="file" name="guarantors[{{ $position }}][cnic_front]" accept="image/*" data-preview="guarantor-front-{{ $position }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC back image</label>
                            <input class="form-control js-image-input" type="file" name="guarantors[{{ $position }}][cnic_back]" accept="image/*" data-preview="guarantor-back-{{ $position }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            @if($guarantor->photo_path)
                                <img id="guarantor-photo-{{ $position }}" src="{{ \Illuminate\Support\Facades\Storage::url($guarantor->photo_path) }}" alt="Guarantor photo" class="avatar">
                            @else
                                <img id="guarantor-photo-{{ $position }}" alt="" class="avatar d-none">
                            @endif
                            @if($guarantor->cnic_front_path)
                                <img id="guarantor-front-{{ $position }}" src="{{ \Illuminate\Support\Facades\Storage::url($guarantor->cnic_front_path) }}" alt="Guarantor CNIC front" class="avatar">
                            @else
                                <img id="guarantor-front-{{ $position }}" alt="" class="avatar d-none">
                            @endif
                            @if($guarantor->cnic_back_path)
                                <img id="guarantor-back-{{ $position }}" src="{{ \Illuminate\Support\Facades\Storage::url($guarantor->cnic_back_path) }}" alt="Guarantor CNIC back" class="avatar">
                            @else
                                <img id="guarantor-back-{{ $position }}" alt="" class="avatar d-none">
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="guarantors[{{ $position }}][address]" rows="2">{{ old("guarantors.$position.address", $guarantor->address) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="guarantors[{{ $position }}][notes]" rows="2">{{ old("guarantors.$position.notes", $guarantor->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
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

@push('scripts')
<script>
    document.querySelectorAll('.js-image-input').forEach(input => {
        input.addEventListener('change', event => {
            const file = event.target.files[0];
            const target = document.getElementById(event.target.dataset.preview);
            if (!file || !target) return;
            target.src = URL.createObjectURL(file);
            target.classList.remove('d-none');
        });
    });
</script>
@endpush
