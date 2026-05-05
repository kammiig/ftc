<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsAppService
{
    public function __construct(private readonly PdfService $pdfService)
    {
    }

    public function ledgerFallback(Customer $customer, ?User $user = null, ?InstallmentSale $sale = null, array $filters = []): array
    {
        $pdfPath = $this->pdfService->storeLedger($customer, $sale, $filters);
        $accountText = $sale ? "\nAccount No: {$sale->account_number}\nProduct: {$sale->product_name}" : '';

        $message = "Assalam o Alaikum {$customer->name},\n\n"
            ."Your FTC customer ledger is ready.{$accountText}\n\n"
            ."Please find your ledger attached.\n\n"
            ."Thank you,\n"
            .company_setting('company_name', 'FTC');

        return $this->buildFallback(
            customer: $customer,
            pdfPath: $pdfPath,
            messageType: 'ledger',
            message: $message,
            user: $user,
            sale: $sale
        );
    }

    public function receiptFallback(Payment $payment, ?User $user = null): array
    {
        $payment->loadMissing(['customer', 'sale', 'user']);
        $pdfPath = $this->receiptPdfPath($payment);
        $paymentDate = $payment->payment_date?->format('d M Y') ?: '-';

        $message = "Assalam o Alaikum {$payment->customer?->name},\n\n"
            ."Your payment has been received by ".company_setting('company_name', 'FTC').".\n\n"
            ."Receipt No: {$payment->receipt_number}\n"
            .'Amount Paid: '.money($payment->amount)."\n"
            .'Payment Date: '.$paymentDate."\n"
            .'Remaining Balance: '.money($payment->sale?->pending_balance)."\n\n"
            ."Please find your receipt attached.\n\n"
            ."Thank you,\n"
            .company_setting('company_name', 'FTC');

        return $this->buildFallback(
            customer: $payment->customer,
            pdfPath: $pdfPath,
            messageType: 'receipt',
            message: $message,
            user: $user,
            payment: $payment,
            sale: $payment->sale
        );
    }

    public function paymentConfirmationFallback(Payment $payment, ?User $user = null): array
    {
        $payment->loadMissing(['customer', 'sale', 'user']);
        $pdfPath = null;
        try {
            $pdfPath = $this->receiptPdfPath($payment);
        } catch (\Throwable $exception) {
            Log::error('PDF generation failed', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'payment_id' => $payment->id,
                'message_type' => 'payment_confirmation',
            ]);
        }
        $paymentDate = $payment->payment_date?->format('d M Y') ?: '-';

        $message = company_setting('company_name', 'FTC')." Payment Confirmation\n\n"
            ."Customer: {$payment->customer?->name}\n"
            .'Payment Amount: '.money($payment->amount)."\n"
            .'Payment Date: '.$paymentDate."\n"
            ."Receipt No: {$payment->receipt_number}\n"
            .'Remaining Balance: '.money($payment->sale?->pending_balance)."\n\n"
            ."Thank you,\n"
            .company_setting('company_name', 'FTC');

        return $this->buildFallback(
            customer: $payment->customer,
            pdfPath: $pdfPath,
            messageType: 'payment_confirmation',
            message: $message,
            user: $user,
            payment: $payment,
            sale: $payment->sale
        );
    }

    public function formatNumber(?string $number): ?string
    {
        if (! $number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '03') && strlen($digits) === 11) {
            $digits = '92'.substr($digits, 1);
        } elseif (str_starts_with($digits, '3') && strlen($digits) === 10) {
            $digits = '92'.$digits;
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? $digits : null;
    }

    private function buildFallback(
        ?Customer $customer,
        ?string $pdfPath,
        string $messageType,
        string $message,
        ?User $user = null,
        ?Payment $payment = null,
        ?InstallmentSale $sale = null
    ): array {
        if (! $customer) {
            return [
                'status' => 'error',
                'message' => 'Customer record is missing.',
            ];
        }

        $number = $this->formatNumber($customer->whatsapp_number ?: $customer->phone);

        $log = WhatsAppMessageLog::query()->create([
            'customer_id' => $customer->id,
            'payment_id' => $payment?->id,
            'installment_sale_id' => $sale?->id,
            'whatsapp_number' => $number ?: (string) ($customer->whatsapp_number ?: $customer->phone),
            'message_type' => $messageType,
            'pdf_file_path' => $pdfPath,
            'status' => $number ? 'pending' : 'failed',
            'error_message' => $number ? null : 'Customer WhatsApp/phone number is missing.',
            'sent_by' => $user?->id,
        ]);

        if (! $number) {
            ActivityLog::record('whatsapp_failed', 'Customer WhatsApp/phone number is missing.', $log);

            return [
                'status' => 'error',
                'message' => 'Customer WhatsApp/phone number is missing.',
                'log' => $log,
            ];
        }

        $downloadUrl = $pdfPath ? URL::temporarySignedRoute(
            'whatsapp.files.download',
            now()->addDays(2),
            ['log' => $log->id]
        ) : null;

        $whatsAppUrl = 'https://wa.me/'.$number.'?text='.rawurlencode($message);

        ActivityLog::record('whatsapp_fallback_ready', 'WhatsApp Web fallback prepared for '.$number, $log);

        return [
            'status' => 'fallback',
            'message' => $pdfPath
                ? 'WhatsApp message is ready. Please attach the downloaded PDF manually.'
                : 'WhatsApp message is ready.',
            'download_url' => $downloadUrl,
            'whatsapp_url' => $whatsAppUrl,
            'whatsapp_number' => $number,
            'prepared_message' => $message,
            'pdf_filename' => $pdfPath ? basename($pdfPath) : null,
            'pdf_exists' => $pdfPath ? File::exists($this->pdfService->absolutePath($pdfPath)) : false,
            'log' => $log->refresh(),
        ];
    }

    private function receiptPdfPath(Payment $payment): string
    {
        $path = $payment->receipt_pdf_path;

        if (! $path || ! File::exists($this->pdfService->absolutePath($path))) {
            $path = $this->pdfService->storeReceipt($payment);
            $payment->forceFill(['receipt_pdf_path' => $path])->save();
        }

        return $path;
    }
}
