<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use RuntimeException;

class PdfService
{
    public function storeLedger(Customer $customer, ?InstallmentSale $sale = null, array $filters = []): string
    {
        $customer->loadMissing([
            'guarantors',
            'sales' => fn ($query) => $query->latest(),
            'ledgers' => fn ($query) => $query->orderBy('entry_date')->orderBy('id'),
        ]);

        $ledgers = $customer->ledgers()
            ->with('sale')
            ->betweenDates($filters['from'] ?? null, $filters['to'] ?? null)
            ->when($sale, fn ($query) => $query->where('installment_sale_id', $sale->id))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $data = [
            'customer' => $customer,
            'sale' => $sale,
            'ledgers' => $ledgers,
            'totalDebit' => (float) $ledgers->sum('debit'),
            'totalCredit' => (float) $ledgers->sum('credit'),
            'balance' => (float) ($ledgers->last()?->balance ?? $customer->currentBalance()),
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ];

        return $this->storeView('pdf.ledger', $data, $this->ledgerFilename($customer, $sale));
    }

    public function storeReceipt(Payment $payment): string
    {
        $payment->loadMissing(['customer.guarantors', 'sale', 'schedule', 'user']);

        return $this->storeView('pdf.receipt', compact('payment'), $this->receiptFilename($payment));
    }

    public function absolutePath(string $relativePath): string
    {
        return storage_path('app/'.$relativePath);
    }

    public function receiptFilename(Payment $payment): string
    {
        return 'receipt-'.$this->safeFilename($payment->receipt_number ?: (string) $payment->id).'.pdf';
    }

    public function ledgerFilename(Customer $customer, ?InstallmentSale $sale = null): string
    {
        $suffix = $sale?->account_number ?: 'customer-'.$customer->id;

        return 'ledger-'.$this->safeFilename($suffix).'.pdf';
    }

    private function storeView(string $view, array $data, string $filename): string
    {
        $relativeDirectory = 'generated-pdfs/'.now()->format('Y/m');
        $absoluteDirectory = storage_path('app/'.$relativeDirectory);
        File::ensureDirectoryExists($absoluteDirectory);

        $relativePath = $relativeDirectory.'/'.$this->safeFilename(pathinfo($filename, PATHINFO_FILENAME)).'.pdf';
        $absolutePath = storage_path('app/'.$relativePath);

        try {
            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

            File::put($absolutePath, $pdf->output());
        } catch (\Throwable $exception) {
            report($exception);
            throw new RuntimeException('Unable to generate PDF. Please check PDF package installation.', previous: $exception);
        }

        return $relativePath;
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', trim($value));
        $safe = trim((string) $safe, '-._');

        return $safe !== '' ? $safe : now()->format('YmdHis');
    }
}
