<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

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

        return $this->storeView('customers.ledger-pdf', $data, $this->ledgerFilename($customer, $sale));
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
        $suffix = $customer->name ?: 'customer-'.$customer->id;

        if ($sale?->account_number) {
            $suffix .= '-'.$sale->account_number;
        }

        return 'ledger-'.$this->safeFilename($suffix).'-'.now()->format('Ymd').'.pdf';
    }

    private function storeView(string $view, array $data, string $filename): string
    {
        $relativeDirectory = 'private/pdfs/'.now()->format('Y/m');
        $absoluteDirectory = storage_path('app/'.$relativeDirectory);
        File::ensureDirectoryExists($absoluteDirectory);

        $relativePath = $relativeDirectory.'/'.$this->safeFilename(pathinfo($filename, PATHINFO_FILENAME)).'.pdf';
        $absolutePath = storage_path('app/'.$relativePath);

        try {
            if (! class_exists(Pdf::class)) {
                throw new RuntimeException('barryvdh/laravel-dompdf is not installed. Run composer install, composer dump-autoload, and clear Laravel caches.');
            }

            $this->prepareDompdfStorage();

            $pdf = Pdf::setOption($this->dompdfOptions())
                ->loadView($view, $data)
                ->setPaper('a4', 'portrait');

            $bytes = File::put($absolutePath, $pdf->output());

            if ($bytes === false || ! File::exists($absolutePath)) {
                throw new RuntimeException('PDF file could not be written. Check storage and bootstrap/cache permissions.');
            }
        } catch (Throwable $exception) {
            $root = $this->rootException($exception);
            $this->logFailure($exception, $root, $view, $absolutePath);

            throw new RuntimeException('Unable to generate PDF: '.$root->getMessage(), 0, $exception);
        }

        return $relativePath;
    }

    private function prepareDompdfStorage(): void
    {
        File::ensureDirectoryExists(storage_path('app/dompdf-temp'));
        File::ensureDirectoryExists(storage_path('app/dompdf-fonts'));
        File::ensureDirectoryExists(storage_path('logs'));
    }

    private function dompdfOptions(): array
    {
        return [
            'defaultFont' => 'DejaVu Sans',
            'tempDir' => storage_path('app/dompdf-temp'),
            'fontDir' => storage_path('app/dompdf-fonts'),
            'fontCache' => storage_path('app/dompdf-fonts'),
            'logOutputFile' => storage_path('logs/dompdf.htm'),
            'chroot' => base_path(),
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ];
    }

    private function rootException(Throwable $exception): Throwable
    {
        $root = $exception;

        while ($root->getPrevious()) {
            $root = $root->getPrevious();
        }

        return $root;
    }

    private function logFailure(Throwable $exception, Throwable $root, string $view, string $absolutePath): void
    {
        Log::error('PDF generation failed', [
            'message' => $root->getMessage(),
            'file' => $root->getFile(),
            'line' => $root->getLine(),
            'view' => $view,
            'target_path' => $absolutePath,
            'package' => 'barryvdh/laravel-dompdf',
        ]);

        report($exception);
    }

    private function safeFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', trim($value));
        $safe = trim((string) $safe, '-._');

        return $safe !== '' ? $safe : now()->format('YmdHis');
    }
}
