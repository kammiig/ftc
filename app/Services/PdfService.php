<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

        $html = view('pdf.ledger', [
            'customer' => $customer,
            'sale' => $sale,
            'ledgers' => $ledgers,
            'totalDebit' => (float) $ledgers->sum('debit'),
            'totalCredit' => (float) $ledgers->sum('credit'),
            'balance' => (float) ($ledgers->last()?->balance ?? $customer->currentBalance()),
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ])->render();

        return $this->storeHtml($html, 'ledger-'.$customer->id.'-'.now()->format('YmdHis').'.pdf');
    }

    public function storeReceipt(Payment $payment): string
    {
        $payment->loadMissing(['customer.guarantors', 'sale', 'schedule', 'user']);

        $html = view('pdf.receipt', compact('payment'))->render();

        return $this->storeHtml($html, 'receipt-'.$payment->receipt_number.'-'.now()->format('YmdHis').'.pdf');
    }

    public function absolutePath(string $relativePath): string
    {
        return storage_path('app/'.$relativePath);
    }

    private function storeHtml(string $html, string $filename): string
    {
        $relativeDirectory = 'generated-pdfs/'.now()->format('Y/m');
        $absoluteDirectory = storage_path('app/'.$relativeDirectory);
        File::ensureDirectoryExists($absoluteDirectory);

        $relativePath = $relativeDirectory.'/'.Str::slug(pathinfo($filename, PATHINFO_FILENAME)).'.pdf';
        $absolutePath = storage_path('app/'.$relativePath);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        File::put($absolutePath, $dompdf->output());

        return $relativePath;
    }
}
