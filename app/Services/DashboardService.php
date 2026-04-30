<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InstallmentSale;
use App\Models\InstallmentSchedule;
use App\Models\Payment;

class DashboardService
{
    public function metrics(bool $includeFinancials = false): array
    {
        $today = now()->toDateString();
        $weekEnd = now()->copy()->endOfWeek()->toDateString();

        $openSchedules = InstallmentSchedule::query()->whereIn('status', ['pending', 'partial', 'overdue']);

        $metrics = [
            'total_customers' => Customer::query()->count(),
            'active_accounts' => InstallmentSale::query()->whereIn('status', ['active', 'defaulter'])->count(),
            'completed_accounts' => InstallmentSale::query()->where('status', 'completed')->count(),
            'amount_received' => Payment::query()->sum('amount'),
            'amount_received_month' => Payment::query()
                ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
            'pending_amount' => InstallmentSale::query()->whereIn('status', ['active', 'defaulter'])->sum('pending_balance'),
            'overdue_amount' => (clone $openSchedules)->whereDate('due_date', '<', $today)->sum('remaining_amount'),
            'due_today' => InstallmentSchedule::query()->open()->whereDate('due_date', $today)->count(),
            'due_this_week' => InstallmentSchedule::query()->open()->whereBetween('due_date', [$today, $weekEnd])->count(),
            'overdue_installments' => InstallmentSchedule::query()->overdue()->count(),
        ];

        if ($includeFinancials) {
            $metrics['total_investment'] = InstallmentSale::query()->sum('product_cost_price');
            $metrics['expected_profit'] = InstallmentSale::query()->sum('profit_amount');
        }

        return $metrics;
    }

    public function recentPayments()
    {
        return Payment::query()->with(['customer', 'sale'])->latest('payment_date')->latest()->limit(8)->get();
    }

    public function recentSales()
    {
        return InstallmentSale::query()->with('customer')->latest()->limit(8)->get();
    }

    public function chartCollections(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = (float) Payment::query()
                ->whereBetween('payment_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->sum('amount');
        }

        return compact('labels', 'data');
    }

    public function dueToday()
    {
        return InstallmentSchedule::query()
            ->with(['customer', 'sale'])
            ->open()
            ->whereDate('due_date', now()->toDateString())
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    public function overdue()
    {
        return InstallmentSchedule::query()
            ->with(['customer', 'sale'])
            ->overdue()
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    private function missedThisMonthCount(): int
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        return InstallmentSale::query()
            ->whereIn('status', ['active', 'defaulter'])
            ->whereHas('schedules', fn ($query) => $query->whereBetween('due_date', [$start, $end])->whereIn('status', ['pending', 'partial', 'overdue']))
            ->whereDoesntHave('payments', fn ($query) => $query->whereBetween('payment_date', [$start, $end]))
            ->distinct('customer_id')
            ->count('customer_id');
    }
}
