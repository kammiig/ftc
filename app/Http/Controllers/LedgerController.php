<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    private function ledgerData(Request $request, Customer $customer): array
    {
        $ledgers = $customer->ledgers()
            ->with('sale')
            ->betweenDates($request->string('from')->toString() ?: null, $request->string('to')->toString() ?: null)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return [
            'customer' => $customer->load('sales'),
            'ledgers' => $ledgers,
            'totalDebit' => (float) $ledgers->sum('debit'),
            'totalCredit' => (float) $ledgers->sum('credit'),
            'balance' => (float) ($ledgers->last()?->balance ?? $customer->currentBalance()),
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
        ];
    }
}
