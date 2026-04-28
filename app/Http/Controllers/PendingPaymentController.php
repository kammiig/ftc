<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InstallmentSchedule;
use App\Models\Product;
use App\Services\InstallmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingPaymentController extends Controller
{
    public function index(Request $request, InstallmentService $installments): View
    {
        $installments->syncOverdues();

        $query = InstallmentSchedule::query()
            ->with(['customer.guarantors', 'sale'])
            ->open()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('customer', fn ($subQuery) => $subQuery->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('cnic', 'like', "%{$search}%"))
                        ->orWhereHas('sale', fn ($subQuery) => $subQuery->where('account_number', 'like', "%{$search}%")->orWhere('product_name', 'like', "%{$search}%")->orWhere('product_sku', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('product_id'), fn ($query) => $query->whereHas('sale', fn ($subQuery) => $subQuery->where('product_id', $request->integer('product_id'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('month'), function ($query) use ($request): void {
                [$year, $month] = explode('-', $request->string('month')->toString());
                $query->whereYear('due_date', $year)->whereMonth('due_date', $month);
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('due_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('due_date', '<=', $request->date('to')))
            ->when($request->boolean('overdue_only'), fn ($query) => $query->whereDate('due_date', '<', now()->toDateString()))
            ->when($request->boolean('due_today'), fn ($query) => $query->whereDate('due_date', now()->toDateString()))
            ->when($request->boolean('due_week'), fn ($query) => $query->whereBetween('due_date', [now()->toDateString(), now()->endOfWeek()->toDateString()]))
            ->when($request->boolean('due_month'), fn ($query) => $query->whereBetween('due_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]))
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        $missedCustomers = Customer::query()
            ->whereHas('sales', function ($query): void {
                $query->whereIn('status', ['active', 'defaulter'])
                    ->whereHas('schedules', fn ($subQuery) => $subQuery->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()])->open());
            })
            ->whereDoesntHave('payments', fn ($query) => $query->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()]))
            ->orderBy('name')
            ->get();

        return view('pending.index', [
            'schedules' => $query,
            'customers' => Customer::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
            'missedCustomers' => $missedCustomers,
        ]);
    }
}
