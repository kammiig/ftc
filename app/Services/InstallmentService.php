<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\InstallmentSchedule;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentService
{
    public function __construct(private readonly LedgerService $ledgerService)
    {
    }

    public function createSale(array $data, User $user): InstallmentSale
    {
        return DB::transaction(function () use ($data, $user): InstallmentSale {
            $customer = Customer::query()->findOrFail($data['customer_id']);
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);

            if (! $product->isSellable()) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product is not available for installment sale.',
                ]);
            }

            $salePrice = round((float) $data['installment_sale_price'], 2);
            $advance = min(round((float) ($data['advance_payment'] ?? 0), 2), $salePrice);
            $installmentsCount = max(1, (int) $data['installments_count']);
            $remaining = max($salePrice - $advance, 0);
            $monthlyAmount = $remaining > 0 ? round($remaining / $installmentsCount, 2) : 0;
            $costPrice = round((float) ($data['product_cost_price'] ?? $product->cost_price), 2);
            $startDate = Carbon::parse($data['installment_start_date']);
            $dueDay = max(1, min(28, (int) ($data['monthly_due_day'] ?? company_setting('default_due_day', 1))));

            $sale = InstallmentSale::query()->create([
                'account_number' => $this->nextAccountNumber(),
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_cost_price' => $costPrice,
                'installment_sale_price' => $salePrice,
                'advance_payment' => $advance,
                'remaining_balance' => $remaining,
                'installments_count' => $installmentsCount,
                'monthly_installment_amount' => $monthlyAmount,
                'installment_start_date' => $startDate->toDateString(),
                'monthly_due_day' => $dueDay,
                'total_paid' => 0,
                'pending_balance' => $salePrice,
                'profit_amount' => $salePrice - $costPrice,
                'status' => $remaining > 0 ? 'active' : 'completed',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $user->id,
                'completed_at' => $remaining <= 0 ? now() : null,
            ]);

            $this->generateSchedule($sale, $remaining, $installmentsCount, $startDate, $dueDay);
            $this->ledgerService->debitSale($sale, $user->id);

            if ($advance > 0) {
                $payment = $this->createPaymentRecord($sale, [
                    'amount' => $advance,
                    'payment_date' => $data['advance_payment_date'] ?? now()->toDateString(),
                    'payment_method' => $data['advance_payment_method'] ?? 'Cash',
                    'reference_number' => $data['advance_reference_number'] ?? null,
                    'received_by' => $user->name,
                    'remarks' => 'Advance / down payment',
                ], $user, null);

                $this->ledgerService->creditPayment($payment, 'Advance payment for '.$sale->account_number, $user->id);
            }

            $this->refreshSaleTotals($sale);
            $this->decreaseProductStock($product);
            $customer->forceFill(['status' => 'active'])->save();

            ActivityLog::record('sale_created', 'Installment sale created: '.$sale->account_number, $sale);

            return $sale->refresh()->load(['customer', 'product', 'schedules', 'payments']);
        });
    }

    public function recordPayment(array $data, User $user): Payment
    {
        return DB::transaction(function () use ($data, $user): Payment {
            $sale = InstallmentSale::query()->lockForUpdate()->with('customer')->findOrFail($data['installment_sale_id']);

            if ($sale->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'installment_sale_id' => 'Payments cannot be recorded against a cancelled account.',
                ]);
            }

            $amount = round((float) $data['amount'], 2);
            $currentPending = max((float) $sale->installment_sale_price - (float) $sale->payments()->sum('amount'), 0);

            if ($amount <= 0 || $amount > $currentPending + 0.01) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero and not exceed the pending balance.',
                ]);
            }

            $scheduleId = $data['installment_schedule_id'] ?? null;
            if ($scheduleId) {
                $validSchedule = InstallmentSchedule::query()
                    ->where('installment_sale_id', $sale->id)
                    ->where('id', $scheduleId)
                    ->exists();

                if (! $validSchedule) {
                    throw ValidationException::withMessages([
                        'installment_schedule_id' => 'Selected installment does not belong to this account.',
                    ]);
                }
            }

            $payment = $this->createPaymentRecord($sale, $data, $user, $scheduleId);
            $this->allocatePaymentToSchedules($sale, $amount, $payment->payment_date->toDateString(), $scheduleId);
            $this->refreshSaleTotals($sale);
            $this->ledgerService->creditPayment($payment, 'Installment payment received - '.$payment->receipt_number, $user->id);
            $this->refreshCustomerStatus($sale->customer);

            ActivityLog::record('payment_received', 'Payment received: '.$payment->receipt_number, $payment);

            return $payment->refresh()->load(['customer', 'sale', 'schedule', 'user']);
        });
    }

    public function syncOverdues(): void
    {
        InstallmentSchedule::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        InstallmentSale::query()
            ->where('status', 'active')
            ->whereHas('schedules', fn ($query) => $query->where('status', 'overdue'))
            ->update(['status' => 'defaulter']);

        $defaulterCustomerIds = InstallmentSale::query()
            ->where('status', 'defaulter')
            ->pluck('customer_id')
            ->unique();

        if ($defaulterCustomerIds->isNotEmpty()) {
            Customer::query()->whereIn('id', $defaulterCustomerIds)->update(['status' => 'defaulter']);
        }
    }

    private function createPaymentRecord(InstallmentSale $sale, array $data, User $user, ?int $scheduleId): Payment
    {
        return Payment::query()->create([
            'receipt_number' => $this->nextReceiptNumber(),
            'customer_id' => $sale->customer_id,
            'installment_sale_id' => $sale->id,
            'installment_schedule_id' => $scheduleId,
            'amount' => round((float) $data['amount'], 2),
            'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'payment_method' => $data['payment_method'] ?? 'Cash',
            'reference_number' => $data['reference_number'] ?? null,
            'received_by' => $data['received_by'] ?? $user->name,
            'created_by' => $user->id,
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    private function generateSchedule(InstallmentSale $sale, float $remaining, int $count, Carbon $startDate, int $dueDay): void
    {
        if ($remaining <= 0) {
            return;
        }

        $firstDue = $startDate->copy()->startOfMonth();
        $firstDue->day(min($dueDay, $firstDue->daysInMonth));

        if ($firstDue->lt($startDate)) {
            $firstDue->addMonthNoOverflow();
            $firstDue->day(min($dueDay, $firstDue->daysInMonth));
        }

        $monthlyAmount = round($remaining / $count, 2);
        $left = $remaining;

        for ($i = 1; $i <= $count; $i++) {
            $dueDate = $firstDue->copy()->addMonthsNoOverflow($i - 1);
            $dueDate->day(min($dueDay, $dueDate->daysInMonth));
            $amount = $i === $count ? round($left, 2) : min($monthlyAmount, round($left, 2));
            $left = round($left - $amount, 2);

            InstallmentSchedule::query()->create([
                'installment_sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'installment_number' => $i,
                'due_date' => $dueDate->toDateString(),
                'due_amount' => $amount,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'status' => $dueDate->isPast() && ! $dueDate->isToday() ? 'overdue' : 'pending',
            ]);
        }
    }

    private function allocatePaymentToSchedules(InstallmentSale $sale, float $amount, string $paymentDate, ?int $scheduleId = null): void
    {
        $remaining = $amount;
        $query = InstallmentSchedule::query()
            ->where('installment_sale_id', $sale->id)
            ->where('remaining_amount', '>', 0);

        $schedules = collect();

        if ($scheduleId) {
            $selected = (clone $query)->where('id', $scheduleId)->first();
            if ($selected) {
                $schedules->push($selected);
            }
        }

        $query->whereNotIn('id', $schedules->pluck('id')->all())
            ->orderBy('due_date')
            ->orderBy('installment_number')
            ->get()
            ->each(fn (InstallmentSchedule $schedule) => $schedules->push($schedule));

        foreach ($schedules as $schedule) {
            if ($remaining <= 0) {
                break;
            }

            $portion = min($remaining, (float) $schedule->remaining_amount);
            $paid = round((float) $schedule->paid_amount + $portion, 2);
            $left = max(round((float) $schedule->due_amount - $paid, 2), 0);

            $schedule->forceFill([
                'paid_amount' => $paid,
                'remaining_amount' => $left,
                'status' => $left <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                'paid_at' => $left <= 0 ? $paymentDate : $schedule->paid_at,
            ])->save();

            if ($left > 0 && Carbon::parse($schedule->due_date)->lt(now()->startOfDay())) {
                $schedule->forceFill(['status' => 'overdue'])->save();
            }

            $remaining = round($remaining - $portion, 2);
        }
    }

    private function refreshSaleTotals(InstallmentSale $sale): void
    {
        $paid = round((float) $sale->payments()->sum('amount'), 2);
        $pending = max(round((float) $sale->installment_sale_price - $paid, 2), 0);
        $hasOverdue = $sale->schedules()->where('status', 'overdue')->exists();

        $sale->forceFill([
            'total_paid' => $paid,
            'pending_balance' => $pending,
            'remaining_balance' => $pending,
            'status' => $pending <= 0 ? 'completed' : ($hasOverdue ? 'defaulter' : 'active'),
            'completed_at' => $pending <= 0 ? now() : null,
        ])->save();
    }

    private function decreaseProductStock(Product $product): void
    {
        $stock = max($product->stock_quantity - 1, 0);

        $product->forceFill([
            'stock_quantity' => $stock,
            'status' => $stock <= 0 ? 'out_of_stock' : 'available',
        ])->save();
    }

    private function refreshCustomerStatus(Customer $customer): void
    {
        if ($customer->sales()->where('status', 'defaulter')->exists()) {
            $customer->forceFill(['status' => 'defaulter'])->save();
            return;
        }

        if ($customer->sales()->whereIn('status', ['active'])->exists()) {
            $customer->forceFill(['status' => 'active'])->save();
            return;
        }

        if ($customer->sales()->exists()) {
            $customer->forceFill(['status' => 'completed'])->save();
        }
    }

    private function nextAccountNumber(): string
    {
        $next = ((int) InstallmentSale::query()->max('id')) + 1;

        do {
            $accountNumber = 'FTC-'.now()->format('Ym').'-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (InstallmentSale::query()->where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    private function nextReceiptNumber(): string
    {
        $prefix = 'RCPT-'.now()->format('Ymd').'-';
        $next = Payment::query()->where('receipt_number', 'like', $prefix.'%')->count() + 1;

        do {
            $receipt = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Payment::query()->where('receipt_number', $receipt)->exists());

        return $receipt;
    }
}
