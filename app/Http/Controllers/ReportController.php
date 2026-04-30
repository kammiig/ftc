<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\InstallmentSchedule;
use App\Models\Payment;
use App\Models\Product;
use App\Services\ExportService;
use App\Services\InstallmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function payments(Request $request, ExportService $export): View|StreamedResponse
    {
        $payments = Payment::query()
            ->with(['customer', 'sale'])
            ->search($request->string('search')->toString())
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('payment_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('payment_date', '<=', $request->date('to')))
            ->when($request->filled('method'), fn (Builder $query) => $query->where('payment_method', $request->string('method')))
            ->latest('payment_date')
            ->latest();

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('payments-report.csv', [
                'Date', 'Receipt', 'Customer', 'Phone', 'Account', 'Amount', 'Method', 'Reference', 'Received By',
            ], $payments->get()->map(fn (Payment $payment) => [
                $payment->payment_date?->format('Y-m-d'),
                $payment->receipt_number,
                $payment->customer?->name,
                $payment->customer?->phone,
                $payment->sale?->account_number,
                $payment->amount,
                $payment->payment_method,
                $payment->reference_number,
                $payment->received_by,
            ]));
        }

        $total = (clone $payments)->sum('amount');

        return view('reports.payments', [
            'payments' => $payments->paginate(25)->withQueryString(),
            'total' => $total,
            'paymentMethods' => payment_methods(),
        ]);
    }

    public function pending(Request $request, InstallmentService $installments, ExportService $export): View|StreamedResponse
    {
        $installments->syncOverdues();
        $schedules = $this->scheduleReportQuery($request)->whereIn('status', ['pending', 'partial']);

        if ($request->string('export')->toString() === 'csv') {
            return $this->exportSchedules($export, 'pending-payments-report.csv', $schedules);
        }

        $total = (clone $schedules)->sum('remaining_amount');

        return view('reports.schedules', [
            'title' => 'Pending Payments Report',
            'schedules' => $schedules->paginate(25)->withQueryString(),
            'total' => $total,
        ]);
    }

    public function monthlyCollection(Request $request, ExportService $export): View|StreamedResponse
    {
        $from = $request->string('from')->toString() ?: now()->subMonths(11)->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: now()->endOfMonth()->toDateString();

        $rows = Payment::query()
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, COUNT(*) as payments_count, SUM(amount) as total_amount')
            ->whereBetween('payment_date', [$from, $to])
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('monthly-collection-report.csv', [
                'Month', 'Payments', 'Total Amount',
            ], $rows->map(fn ($row) => [
                \Carbon\Carbon::create((int) $row->year, (int) $row->month)->format('M Y'),
                $row->payments_count,
                $row->total_amount,
            ]));
        }

        return view('reports.monthly-collection', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'total' => (float) $rows->sum('total_amount'),
        ]);
    }

    public function overdue(Request $request, InstallmentService $installments, ExportService $export): View|StreamedResponse
    {
        $installments->syncOverdues();
        $schedules = $this->scheduleReportQuery($request)->whereDate('due_date', '<', now()->toDateString());

        if ($request->string('export')->toString() === 'csv') {
            return $this->exportSchedules($export, 'overdue-customers-report.csv', $schedules);
        }

        $total = (clone $schedules)->sum('remaining_amount');

        return view('reports.schedules', [
            'title' => 'Overdue Customers Report',
            'schedules' => $schedules->paginate(25)->withQueryString(),
            'total' => $total,
        ]);
    }

    public function sales(Request $request, ExportService $export, bool $showFinancials = false, string $title = 'Installment Accounts Report'): View|StreamedResponse
    {
        $showFinancials = $showFinancials && can_view_financials();
        $sales = InstallmentSale::query()
            ->with('customer')
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->when($request->filled('from'), fn ($query) => $query->whereDate('installment_start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('installment_start_date', '<=', $request->date('to')))
            ->latest();

        if ($request->string('export')->toString() === 'csv') {
            $headings = ['Date', 'Account', 'Customer', 'Product', 'Sale Value', 'Paid', 'Pending', 'Status'];
            if ($showFinancials) {
                $headings = ['Date', 'Account', 'Customer', 'Product', 'Cost', 'Sale Value', 'Profit', 'Paid', 'Pending', 'Status'];
            }

            return $export->csv('installment-accounts-report.csv', $headings, $sales->get()->map(function (InstallmentSale $sale) use ($showFinancials): array {
                $row = [
                    $sale->installment_start_date?->format('Y-m-d'),
                    $sale->account_number,
                    $sale->customer?->name,
                    $sale->product_name,
                    $sale->installment_sale_price,
                    $sale->total_paid,
                    $sale->pending_balance,
                    $sale->status,
                ];

                if ($showFinancials) {
                    array_splice($row, 4, 0, [$sale->product_cost_price]);
                    array_splice($row, 6, 0, [$sale->profit_amount]);
                }

                return $row;
            }));
        }

        $saleValue = (clone $sales)->sum('installment_sale_price');
        $investment = $showFinancials ? (clone $sales)->sum('product_cost_price') : null;
        $profit = $showFinancials ? (clone $sales)->sum('profit_amount') : null;

        return view('reports.sales', [
            'title' => $title,
            'sales' => $sales->paginate(25)->withQueryString(),
            'investment' => $investment,
            'saleValue' => $saleValue,
            'profit' => $profit,
            'showFinancials' => $showFinancials,
        ]);
    }

    public function customerLedgers(Request $request, ExportService $export): View|StreamedResponse
    {
        $customers = Customer::query()
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->withCount('sales')
            ->orderBy('name');

        if ($request->string('export')->toString() === 'csv') {
            $from = $request->string('from')->toString() ?: null;
            $to = $request->string('to')->toString() ?: null;

            return $export->csv('customer-ledger-report.csv', [
                'Customer', 'CNIC', 'Phone', 'Status', 'Sales', 'Total Debit', 'Total Credit', 'Balance',
            ], $customers->get()->map(fn (Customer $customer) => [
                $customer->name,
                $customer->cnic,
                $customer->phone,
                $customer->status,
                $customer->sales_count,
                $customer->totalDebit($from, $to),
                $customer->totalCredit($from, $to),
                $customer->currentBalance(),
            ]));
        }

        return view('reports.customer-ledgers', [
            'customers' => $customers->paginate(25)->withQueryString(),
            'statuses' => Customer::STATUSES,
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ]);
    }

    public function profit(Request $request, ExportService $export): View|StreamedResponse
    {
        $sales = InstallmentSale::query()
            ->with('customer')
            ->when($request->filled('from'), fn ($query) => $query->whereDate('installment_start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('installment_start_date', '<=', $request->date('to')))
            ->latest();

        $investment = (clone $sales)->sum('product_cost_price');
        $saleValue = (clone $sales)->sum('installment_sale_price');
        $profit = (clone $sales)->sum('profit_amount');

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('profit-report.csv', [
                'Date', 'Account', 'Customer', 'Cost', 'Sale Value', 'Profit', 'Status',
            ], $sales->get()->map(fn (InstallmentSale $sale) => [
                $sale->installment_start_date?->format('Y-m-d'),
                $sale->account_number,
                $sale->customer?->name,
                $sale->product_cost_price,
                $sale->installment_sale_price,
                $sale->profit_amount,
                $sale->status,
            ]));
        }

        return view('reports.profit', [
            'sales' => $sales->paginate(25)->withQueryString(),
            'investment' => $investment,
            'saleValue' => $saleValue,
            'profit' => $profit,
        ]);
    }

    public function productWiseSales(Request $request, ExportService $export): View|StreamedResponse
    {
        $rows = Product::query()
            ->withCount('sales')
            ->withSum('sales as sale_value', 'installment_sale_price')
            ->withSum('sales as investment', 'product_cost_price')
            ->withSum('sales as profit', 'profit_amount')
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->orderBy('name')
            ->get();

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('product-wise-sales-report.csv', [
                'Product', 'SKU', 'Status', 'Sales Count', 'Investment', 'Sale Value', 'Profit',
            ], $rows->map(fn (Product $product) => [
                $product->name,
                $product->sku,
                $product->status,
                $product->sales_count,
                $product->investment,
                $product->sale_value,
                $product->profit,
            ]));
        }

        return view('reports.product-wise-sales', [
            'products' => $rows,
            'statuses' => Product::STATUSES,
        ]);
    }

    public function investment(Request $request, ExportService $export): View|StreamedResponse
    {
        return $this->sales($request->merge(['status' => $request->string('status')->toString()]), $export, true, 'Investment Report');
    }

    public function activeAccounts(Request $request, ExportService $export): View|StreamedResponse
    {
        $request->merge(['status' => 'active']);

        return $this->sales($request, $export, false, 'Active Installment Accounts Report');
    }

    public function completedAccounts(Request $request, ExportService $export): View|StreamedResponse
    {
        $request->merge(['status' => 'completed']);

        return $this->sales($request, $export, false, 'Completed Installment Accounts Report');
    }

    public function defaulters(Request $request, InstallmentService $installments, ExportService $export): View|StreamedResponse
    {
        $installments->syncOverdues();

        $customers = Customer::query()
            ->where('status', 'defaulter')
            ->with(['sales' => fn ($query) => $query->where('status', 'defaulter')]);

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('defaulter-customers-report.csv', [
                'Customer', 'Phone', 'CNIC', 'Accounts', 'Pending',
            ], $customers->get()->map(fn (Customer $customer) => [
                $customer->name,
                $customer->phone,
                $customer->cnic,
                $customer->sales->pluck('account_number')->implode(', '),
                $customer->sales->sum('pending_balance'),
            ]));
        }

        $customers = $customers->paginate(25);

        return view('reports.defaulters', compact('customers'));
    }

    public function dailyCollection(Request $request, ExportService $export): View|StreamedResponse
    {
        $date = $request->string('date')->toString() ?: now()->toDateString();
        $payments = Payment::query()
            ->with(['customer', 'sale'])
            ->whereDate('payment_date', $date)
            ->latest();

        if ($request->string('export')->toString() === 'csv') {
            return $export->csv('daily-collection-'.$date.'.csv', [
                'Receipt', 'Customer', 'Account', 'Amount', 'Method', 'Received By',
            ], $payments->get()->map(fn (Payment $payment) => [
                $payment->receipt_number,
                $payment->customer?->name,
                $payment->sale?->account_number,
                $payment->amount,
                $payment->payment_method,
                $payment->received_by,
            ]));
        }

        return view('reports.daily-collection', [
            'date' => $date,
            'payments' => $payments->paginate(25)->withQueryString(),
            'total' => Payment::query()->whereDate('payment_date', $date)->sum('amount'),
        ]);
    }

    private function scheduleReportQuery(Request $request): Builder
    {
        return InstallmentSchedule::query()
            ->with(['customer', 'sale'])
            ->open()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('customer', fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('cnic', 'like', "%{$search}%"))
                        ->orWhereHas('sale', fn (Builder $query) => $query->where('account_number', 'like', "%{$search}%")->orWhere('product_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('due_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('due_date', '<=', $request->date('to')))
            ->orderBy('due_date');
    }

    private function exportSchedules(ExportService $export, string $filename, Builder $schedules): StreamedResponse
    {
        return $export->csv($filename, [
            'Due Date', 'Customer', 'Phone', 'Account', 'Product', 'Installment', 'Due', 'Paid', 'Remaining', 'Paid Date', 'Status', 'Days Overdue',
        ], $schedules->get()->map(fn (InstallmentSchedule $schedule) => [
            $schedule->due_date?->format('Y-m-d'),
            $schedule->customer?->name,
            $schedule->customer?->phone,
            $schedule->sale?->account_number,
            $schedule->sale?->product_name,
            $schedule->installment_number,
            $schedule->due_amount,
            $schedule->paid_amount,
            $schedule->remaining_amount,
            $schedule->paid_at?->format('Y-m-d'),
            $schedule->status,
            max(0, (int) $schedule->due_date?->diffInDays(now(), false)),
        ]));
    }
}
