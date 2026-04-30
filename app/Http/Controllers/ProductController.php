<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'statuses' => Product::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product(['status' => 'available', 'stock_quantity' => 1]),
            'statuses' => Product::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (! can_view_financials()) {
            $data['cost_price'] = 0;
        }
        $data['cash_sale_price'] = $data['cash_sale_price'] ?? 0;
        $this->handleUpload($request, $data);

        $product = Product::query()->create($data);
        ActivityLog::record('product_created', 'Product added: '.$product->name, $product);

        return redirect()->route('products.show', $product)->with('success', 'Product created.');
    }

    public function show(Product $product): View
    {
        $product->load(['sales.customer']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'statuses' => Product::STATUSES,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        if (! can_view_financials()) {
            $data['cost_price'] = $product->cost_price;
        }
        $data['cash_sale_price'] = $data['cash_sale_price'] ?? 0;
        $this->handleUpload($request, $data, $product);

        $product->update($data);
        ActivityLog::record('product_updated', 'Product updated: '.$product->name, $product);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->sales()->exists()) {
            return back()->with('error', 'This product is linked with installment sales and cannot be deleted.');
        }

        ActivityLog::record('product_deleted', 'Product deleted: '.$product->name, $product);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $productId = $product?->id ?? 'NULL';

        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand_model' => ['nullable', 'string', 'max:191'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,'.$productId],
            'cost_price' => [can_view_financials() ? 'required' : 'nullable', 'numeric', 'min:0'],
            'cash_sale_price' => ['nullable', 'numeric', 'min:0'],
            'installment_sale_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', Product::STATUSES)],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function handleUpload(Request $request, array &$data, ?Product $product = null): void
    {
        unset($data['image']);

        if (! $request->hasFile('image')) {
            return;
        }

        if ($product?->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $data['image_path'] = $request->file('image')->store('products', 'public');
    }
}
