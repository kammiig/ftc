@csrf
@isset($method)
    @method($method)
@endisset

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Product Record</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product name</label>
                        <input class="form-control" name="name" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input class="form-control" name="category" value="{{ old('category', $product->category) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Brand / model</label>
                        <input class="form-control" name="brand_model" value="{{ old('brand_model', $product->brand_model) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SKU / code</label>
                        <input class="form-control" name="sku" value="{{ old('sku', $product->sku) }}">
                    </div>
                    @if(can_view_financials())
                        <div class="col-md-3">
                            <label class="form-label">Cost price</label>
                            <input type="number" step="0.01" class="form-control" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" required>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <label class="form-label">Cash sale price</label>
                        <input type="number" step="0.01" class="form-control" name="cash_sale_price" value="{{ old('cash_sale_price', $product->cash_sale_price) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Installment sale price</label>
                        <input type="number" step="0.01" class="form-control" name="installment_sale_price" value="{{ old('installment_sale_price', $product->installment_sale_price) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock quantity</label>
                        <input type="number" class="form-control" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $product->status) === $status)>{{ readable_status($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="form-section-title">Image</div>
                @if($product->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" class="img-fluid rounded mb-3" alt="{{ $product->name }}">
                @endif
                <input class="form-control mb-3" type="file" name="image" accept="image/*">
                <button class="btn btn-primary w-100"><i data-lucide="save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
