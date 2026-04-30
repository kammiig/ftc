<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\InstallmentService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard, InstallmentService $installments): View
    {
        $installments->syncOverdues();

        return view('dashboard.index', [
            'metrics' => $dashboard->metrics(can_view_financials()),
            'recentPayments' => $dashboard->recentPayments(),
            'dueToday' => $dashboard->dueToday(),
            'overdue' => $dashboard->overdue(),
        ]);
    }
}
