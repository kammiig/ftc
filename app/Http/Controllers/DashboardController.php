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
            'metrics' => $dashboard->metrics(),
            'recentPayments' => $dashboard->recentPayments(),
            'recentSales' => $dashboard->recentSales(),
            'collectionsChart' => $dashboard->chartCollections(),
            'dueToday' => $dashboard->dueToday(),
            'overdue' => $dashboard->overdue(),
        ]);
    }
}
