<?php

namespace App\Http\Controllers;

use App\Models\InstallmentSale;
use App\Models\Payment;
use App\Services\PdfService;
use App\Services\InstallmentService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['customer', 'sale'])
            ->search($request->string('search')->toString())
            ->when($request->filled('from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date('to')))
            ->when($request->filled('method'), fn ($query) => $query->where('payment_method', $request->string('method')))
            ->latest('payment_date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'paymentMethods' => payment_methods(),
        ]);
    }

    public function create(Request $request): View
    {
        $sales = InstallmentSale::query()
            ->with(['customer', 'schedules' => fn ($query) => $query->open()->orderBy('due_date')])
            ->whereIn('status', ['active', 'defaulter'])
            ->orderBy('account_number')
            ->get();

        $selectedSale = $request->integer('sale_id') ? $sales->firstWhere('id', $request->integer('sale_id')) : null;
        $selectedScheduleId = $request->integer('schedule_id') ?: null;

        return view('payments.create', [
            'sales' => $sales,
            'selectedSale' => $selectedSale,
            'selectedScheduleId' => $selectedScheduleId,
            'paymentMethods' => payment_methods(),
        ]);
    }

    public function store(Request $request, InstallmentService $installments, WhatsAppService $whatsApp, PdfService $pdfService): RedirectResponse
    {
        $data = $request->validate([
            'installment_sale_id' => ['required', 'exists:installment_sales,id'],
            'installment_schedule_id' => ['nullable', 'exists:installment_schedules,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:191'],
            'received_by' => ['nullable', 'string', 'max:191'],
            'remarks' => ['nullable', 'string'],
            'send_receipt_whatsapp' => ['nullable', 'boolean'],
            'send_ledger_whatsapp' => ['nullable', 'boolean'],
        ]);

        $payment = $installments->recordPayment($data, Auth::user());

        try {
            $payment->forceFill(['receipt_pdf_path' => $pdfService->storeReceipt($payment)])->save();
        } catch (\Throwable $exception) {
            report($exception);
        }

        if ($request->boolean('send_receipt_whatsapp')) {
            $this->flashWhatsAppResult($whatsApp->sendReceipt($payment, Auth::user()));
        }

        if ($request->boolean('send_ledger_whatsapp')) {
            $this->flashWhatsAppResult($whatsApp->sendLedger($payment->customer, Auth::user(), $payment->sale));
        }

        return redirect()->route('payments.receipt', $payment)->with('success', 'Payment recorded and ledger updated.');
    }

    public function show(Payment $payment): View
    {
        return $this->receipt($payment);
    }

    public function receipt(Payment $payment): View
    {
        $payment->load(['customer', 'sale', 'schedule', 'user']);

        return view('payments.receipt', compact('payment'));
    }

    public function print(Payment $payment): View
    {
        $payment->load(['customer', 'sale', 'schedule', 'user']);

        return view('payments.print', compact('payment'));
    }

    public function pdf(Payment $payment, PdfService $pdfService): BinaryFileResponse
    {
        $path = $payment->receipt_pdf_path;

        if (! $path || ! file_exists($pdfService->absolutePath($path))) {
            $path = $pdfService->storeReceipt($payment);
            $payment->forceFill(['receipt_pdf_path' => $path])->save();
        }

        return response()->download($pdfService->absolutePath($path), 'FTC-Receipt-'.$payment->receipt_number.'.pdf');
    }

    private function flashWhatsAppResult(array $result): void
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
