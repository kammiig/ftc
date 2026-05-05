<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ExportService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LedgerController extends Controller
{
    public function show(Request $request, Customer $customer): View
    {
        return view('ledgers.show', $this->ledgerData($request, $customer));
    }

    public function print(Request $request, Customer $customer): View
    {
        return view('ledgers.print', $this->ledgerData($request, $customer));
    }

    public function export(Request $request, Customer $customer, ExportService $export): StreamedResponse
    {
        $data = $this->ledgerData($request, $customer);

        $rows = $data['ledgers']->map(fn ($ledger) => [
            $ledger->entry_date?->format('Y-m-d'),
            $ledger->description,
            $ledger->debit,
            $ledger->credit,
            $ledger->balance,
            $ledger->payment_method,
            $ledger->reference_number,
            $ledger->remarks,
        ]);

        return $export->csv('customer-ledger-'.$customer->id.'.csv', [
            'Date',
            'Description',
            'Debit',
            'Credit',
            'Balance',
            'Payment Method',
            'Reference',
            'Remarks',
        ], $rows);
    }

    public function pdf(Request $request, Customer $customer, PdfService $pdfService): BinaryFileResponse|RedirectResponse
    {
        try {
            $path = $pdfService->storeLedger($customer, null, [
                'from' => $request->string('from')->toString() ?: null,
                'to' => $request->string('to')->toString() ?: null,
            ]);

            return response()->download($pdfService->absolutePath($path), basename($path), [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Throwable $exception) {
            Log::error('PDF generation failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'customer_id' => $customer->id,
                'route' => 'customers.ledger.pdf',
            ]);

            return back()->with('error', 'Unable to generate PDF. Please check storage/logs/laravel.log for the exact PDF error.');
        }
    }

    private function ledgerData(Request $request, Customer $customer): array
    {
        $ledgers = $customer->ledgers()
            ->with('sale')
            ->betweenDates($request->string('from')->toString() ?: null, $request->string('to')->toString() ?: null)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return [
            'customer' => $customer->load(['sales', 'guarantors']),
            'ledgers' => $ledgers,
            'totalDebit' => (float) $ledgers->sum('debit'),
            'totalCredit' => (float) $ledgers->sum('credit'),
            'balance' => (float) ($ledgers->last()?->balance ?? $customer->currentBalance()),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ];
    }
}
