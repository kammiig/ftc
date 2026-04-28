<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Product;
use App\Services\InstallmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstallmentSaleController extends Controller
{
    public function index(Request $request, InstallmentService $installments): View
    {
        $installments->syncOverdues();

        $sales = InstallmentSale::query()
            ->with('customer')
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->when($request->filled('from'), fn ($query) => $query->whereDate('installment_start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('installment_start_date', '<=', $request->date('to')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'statuses' => InstallmentSale::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('sales.create', [
            'customers' => Customer::query()->whereNotIn('status', ['blocked'])->orderBy('name')->get(),
            'products' => Product::query()->where('status', 'available')->where('stock_quantity', '>', 0)->orderBy('name')->get(),
            'defaultDueDay' => (int) company_setting('default_due_day', 1),
        ]);
    }

    public function store(Request $request, InstallmentService $installments): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'product_cost_price' => ['required', 'numeric', 'min:0'],
            'installment_sale_price' => ['required', 'numeric', 'min:1'],
            'advance_payment' => ['nullable', 'numeric', 'min:0'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:120'],
            'installment_start_date' => ['required', 'date'],
            'monthly_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'advance_payment_method' => ['nullable', 'string', 'max:100'],
            'advance_reference_number' => ['nullable', 'string', 'max:191'],
            'advance_payment_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $sale = $installments->createSale($data, Auth::user());

        return redirect()->route('sales.show', $sale)->with('success', 'Installment sale created and schedule generated.');
    }

    public function show(InstallmentSale $sale): View
    {
        $sale->load([
            'customer',
            'product',
            'schedules' => fn ($query) => $query->orderBy('installment_number'),
            'payments' => fn ($query) => $query->latest('payment_date')->latest(),
        ]);

        return view('sales.show', compact('sale'));
    }

    public function edit(InstallmentSale $sale): View
    {
        return view('sales.edit', [
            'sale' => $sale,
            'statuses' => InstallmentSale::STATUSES,
        ]);
    }

    public function update(Request $request, InstallmentSale $sale): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', InstallmentSale::STATUSES)],
            'remarks' => ['nullable', 'string'],
        ]);

        $sale->update($data);
        ActivityLog::record('sale_updated', 'Installment sale updated: '.$sale->account_number, $sale);

        return redirect()->route('sales.show', $sale)->with('success', 'Installment account updated.');
    }

    public function printSchedule(InstallmentSale $sale): View
    {
        $sale->load(['customer', 'schedules' => fn ($query) => $query->orderBy('installment_number')]);

        return view('sales.print-schedule', compact('sale'));
    }
}
