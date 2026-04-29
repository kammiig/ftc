<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\Payment;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class WhatsAppService
{
    public function __construct(private readonly PdfService $pdfService)
    {
    }

    public function sendLedger(Customer $customer, ?User $user = null, ?InstallmentSale $sale = null, array $filters = []): array
    {
        $pdfPath = $this->pdfService->storeLedger($customer, $sale, $filters);

        return $this->sendDocument(
            customer: $customer,
            pdfPath: $pdfPath,
            messageType: 'ledger',
            caption: 'FTC customer ledger for '.$customer->name,
            filename: 'FTC-Ledger-'.$customer->id.'.pdf',
            user: $user,
            sale: $sale
        );
    }

    public function sendReceipt(Payment $payment, ?User $user = null, string $messageType = 'receipt'): array
    {
        $payment->loadMissing(['customer', 'sale']);
        $pdfPath = $payment->receipt_pdf_path;

        if (! $pdfPath || ! is_file($this->pdfService->absolutePath($pdfPath))) {
            $pdfPath = $this->pdfService->storeReceipt($payment);
            $payment->forceFill(['receipt_pdf_path' => $pdfPath])->save();
        }

        return $this->sendDocument(
            customer: $payment->customer,
            pdfPath: $pdfPath,
            messageType: $messageType,
            caption: 'FTC payment receipt '.$payment->receipt_number.' for '.money($payment->amount),
            filename: 'FTC-Receipt-'.$payment->receipt_number.'.pdf',
            user: $user,
            payment: $payment,
            sale: $payment->sale
        );
    }

    public function sendPaymentConfirmation(Payment $payment, ?User $user = null): array
    {
        return $this->sendReceipt($payment, $user, 'payment_confirmation');
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

    public function isConfigured(): bool
    {
        return filled($this->token()) && filled($this->phoneNumberId());
    }

    private function sendDocument(
        Customer $customer,
        string $pdfPath,
        string $messageType,
        string $caption,
        string $filename,
        ?User $user = null,
        ?Payment $payment = null,
        ?InstallmentSale $sale = null
    ): array {
        $number = $this->formatNumber($customer->whatsapp_number ?: $customer->phone);
        $log = WhatsAppMessageLog::query()->create([
            'customer_id' => $customer->id,
            'payment_id' => $payment?->id,
            'installment_sale_id' => $sale?->id,
            'whatsapp_number' => $number ?: (string) ($customer->whatsapp_number ?: $customer->phone),
            'message_type' => $messageType,
            'pdf_file_path' => $pdfPath,
            'status' => 'pending',
            'sent_by' => $user?->id,
        ]);

        if (! $number) {
            return $this->fallback($log, 'Customer WhatsApp number is invalid.', 'failed');
        }

        if (! $this->isConfigured()) {
            return $this->fallback($log, 'WhatsApp Cloud API credentials are not configured.', 'pending');
        }

        try {
            $absolutePath = $this->pdfService->absolutePath($pdfPath);

            if (! File::exists($absolutePath)) {
                throw new RuntimeException('Generated PDF file was not found.');
            }

            $mediaResponse = Http::withToken($this->token())
                ->attach('file', File::get($absolutePath), basename($filename))
                ->post($this->graphUrl($this->phoneNumberId().'/media'), [
                    'messaging_product' => 'whatsapp',
                    'type' => 'application/pdf',
                ]);

            if (! $mediaResponse->successful()) {
                throw new RuntimeException('WhatsApp media upload failed: '.$mediaResponse->body());
            }

            $mediaId = $mediaResponse->json('id');

            $messageResponse = Http::withToken($this->token())
                ->post($this->graphUrl($this->phoneNumberId().'/messages'), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $number,
                    'type' => 'document',
                    'document' => [
                        'id' => $mediaId,
                        'caption' => $caption,
                        'filename' => $filename,
                    ],
                ]);

            if (! $messageResponse->successful()) {
                throw new RuntimeException('WhatsApp document send failed: '.$messageResponse->body());
            }

            $log->forceFill([
                'status' => 'sent',
                'api_response' => $messageResponse->body(),
                'sent_at' => now(),
            ])->save();

            ActivityLog::record('whatsapp_sent', 'WhatsApp '.$messageType.' sent to '.$number, $log);

            return [
                'status' => 'sent',
                'message' => ucfirst(str_replace('_', ' ', $messageType)).' sent to WhatsApp.',
                'log' => $log->refresh(),
            ];
        } catch (\Throwable $exception) {
            return $this->fallback($log, $exception->getMessage(), 'failed');
        }
    }

    private function fallback(WhatsAppMessageLog $log, string $reason, string $status): array
    {
        $log->forceFill([
            'status' => $status,
            'error_message' => $reason,
        ])->save();

        ActivityLog::record('whatsapp_failed', 'WhatsApp send fallback: '.$reason, $log);

        $downloadUrl = URL::temporarySignedRoute(
            'whatsapp.files.download',
            now()->addDays(2),
            ['log' => $log->id]
        );

        $message = 'FTC document is ready. Please download and attach the PDF manually: '.$downloadUrl;

        return [
            'status' => 'fallback',
            'message' => $reason,
            'download_url' => $downloadUrl,
            'whatsapp_url' => 'https://wa.me/'.$log->whatsapp_number.'?text='.rawurlencode($message),
            'log' => $log->refresh(),
        ];
    }

    private function token(): ?string
    {
        return company_setting('whatsapp_api_token') ?: env('WHATSAPP_API_TOKEN');
    }

    private function phoneNumberId(): ?string
    {
        return company_setting('whatsapp_phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');
    }

    private function graphUrl(string $path): string
    {
        $version = company_setting('whatsapp_graph_version') ?: env('WHATSAPP_GRAPH_VERSION', 'v24.0');

        return 'https://graph.facebook.com/'.$version.'/'.trim($path, '/');
    }
}
