<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use App\Models\WhatsAppMessageLog;
use App\Services\PdfService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WhatsAppController extends Controller
{
    public function customerLedger(Request $request, Customer $customer, WhatsAppService $whatsApp): View|RedirectResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return $this->fallbackView(fn () => $whatsApp->ledgerFallback($customer, Auth::user(), null, $filters), 'Customer Ledger WhatsApp');
    }

    public function saleLedger(InstallmentSale $sale, WhatsAppService $whatsApp): View|RedirectResponse
    {
        $sale->loadMissing('customer');

        return $this->fallbackView(fn () => $whatsApp->ledgerFallback($sale->customer, Auth::user(), $sale), 'Installment Ledger WhatsApp');
    }

    public function receipt(Payment $payment, WhatsAppService $whatsApp): View|RedirectResponse
    {
        return $this->fallbackView(fn () => $whatsApp->receiptFallback($payment, Auth::user()), 'Send Receipt to WhatsApp');
    }

    public function paymentConfirmation(Payment $payment, WhatsAppService $whatsApp): View|RedirectResponse
    {
        return $this->fallbackView(fn () => $whatsApp->paymentConfirmationFallback($payment, Auth::user()), 'Payment Confirmation WhatsApp');
    }

    public function download(WhatsAppMessageLog $log, PdfService $pdfService): BinaryFileResponse|Response
    {
        abort_unless(request()->hasValidSignature(), 403);

        $path = $log->pdf_file_path ? $pdfService->absolutePath($log->pdf_file_path) : null;
        if (! $path || ! File::exists($path)) {
            return response('PDF file is missing. Please generate the document again from the portal.', 404);
        }

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function fallbackView(callable $callback, string $title): View|RedirectResponse
    {
        try {
            $result = $callback();
        } catch (\Throwable $exception) {
            Log::error('PDF generation failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'route' => request()->route()?->getName(),
            ]);

            return back()->with('error', 'Unable to generate PDF. Please check storage/logs/laravel.log for the exact PDF error.');
        }

        if (($result['status'] ?? null) === 'error') {
            return back()->with('error', $result['message'] ?? 'Unable to prepare WhatsApp message.');
        }

        return view('whatsapp.fallback', [
            'title' => $title,
            'result' => $result,
        ])->with('success', 'WhatsApp message is ready. Please attach the downloaded PDF manually.');
    }
}
