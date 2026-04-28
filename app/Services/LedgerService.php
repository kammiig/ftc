<?php

namespace App\Services;

use App\Models\InstallmentSale;
use App\Models\Ledger;
use App\Models\Payment;

class LedgerService
{
    public function debitSale(InstallmentSale $sale, ?int $userId = null): Ledger
    {
        $ledger = Ledger::query()->create([
            'customer_id' => $sale->customer_id,
            'installment_sale_id' => $sale->id,
            'entry_date' => $sale->installment_start_date,
            'description' => 'Installment sale '.$sale->account_number.' - '.$sale->product_name,
            'debit' => $sale->installment_sale_price,
            'credit' => 0,
            'balance' => 0,
            'remarks' => $sale->remarks,
            'created_by' => $userId,
        ]);

        $this->recalculateCustomerBalances($sale->customer_id);

        return $ledger->refresh();
    }

    public function creditPayment(Payment $payment, string $description, ?int $userId = null): Ledger
    {
        $ledger = Ledger::query()->create([
            'customer_id' => $payment->customer_id,
            'installment_sale_id' => $payment->installment_sale_id,
            'payment_id' => $payment->id,
            'entry_date' => $payment->payment_date,
            'description' => $description,
            'debit' => 0,
            'credit' => $payment->amount,
            'balance' => 0,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number ?: $payment->receipt_number,
            'remarks' => $payment->remarks,
            'created_by' => $userId,
        ]);

        $this->recalculateCustomerBalances($payment->customer_id);

        return $ledger->refresh();
    }

    public function recalculateCustomerBalances(int $customerId): void
    {
        $balance = 0;

        Ledger::query()
            ->where('customer_id', $customerId)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get()
            ->each(function (Ledger $ledger) use (&$balance): void {
                $balance += (float) $ledger->debit;
                $balance -= (float) $ledger->credit;
                $ledger->forceFill(['balance' => max($balance, 0)])->save();
            });
    }
}
