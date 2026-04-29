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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WhatsAppController extends Controller
{
    public function sendCustomerLedger(Request $request, Customer $customer, WhatsAppService $whatsApp): RedirectResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $this->flashResult($whatsApp->sendLedger($customer, Auth::user(), null, $filters));

        return back();
    }

    public function sendSaleLedger(InstallmentSale $sale, WhatsAppService $whatsApp): RedirectResponse
    {
        $sale->loadMissing('customer');
        $this->flashResult($whatsApp->sendLedger($sale->customer, Auth::user(), $sale));

        return back();
    }

    public function sendReceipt(Payment $payment, WhatsAppService $whatsApp): RedirectResponse
    {
        $this->flashResult($whatsApp->sendReceipt($payment, Auth::user()));

        return back();
    }

    public function sendPaymentConfirmation(Payment $payment, WhatsAppService $whatsApp): RedirectResponse
    {
        $this->flashResult($whatsApp->sendPaymentConfirmation($payment, Auth::user()));

        return back();
    }

    public function download(WhatsAppMessageLog $log, PdfService $pdfService): BinaryFileResponse
    {
        abort_unless(request()->hasValidSignature(), 403);

        $path = $log->pdf_file_path ? $pdfService->absolutePath($log->pdf_file_path) : null;
        abort_unless($path && File::exists($path), 404);

        return response()->download($path);
    }

    private function flashResult(array $result): void
    {
        if (($result['status'] ?? null) === 'sent') {
            session()->flash('whatsapp_status', [
                'type' => 'success',
                'message' => $result['message'] ?? 'WhatsApp message sent.',
            ]);

            return;
        }

        session()->flash('whatsapp_fallback', [
            'message' => $result['message'] ?? 'WhatsApp API is unavailable.',
            'download_url' => $result['download_url'] ?? null,
            'whatsapp_url' => $result['whatsapp_url'] ?? null,
        ]);
    }
}
