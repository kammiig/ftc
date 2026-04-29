<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstallmentSaleController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PendingPaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('whatsapp/files/{log}/download', [WhatsAppController::class, 'download'])
    ->middleware('signed')
    ->name('whatsapp.files.download');

Route::middleware('auth')->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::get('customers/{customer}/print', [CustomerController::class, 'print'])->name('customers.print');
    Route::get('customers/{customer}/ledger', [LedgerController::class, 'show'])->name('customers.ledger');
    Route::get('customers/{customer}/ledger/print', [LedgerController::class, 'print'])->name('customers.ledger.print');
    Route::get('customers/{customer}/ledger/pdf', [LedgerController::class, 'pdf'])->name('customers.ledger.pdf');
    Route::get('customers/{customer}/ledger/export', [LedgerController::class, 'export'])->name('customers.ledger.export');
    Route::post('customers/{customer}/ledger/whatsapp', [WhatsAppController::class, 'sendCustomerLedger'])->name('customers.ledger.whatsapp');

    Route::resource('products', ProductController::class);

    Route::resource('sales', InstallmentSaleController::class)->except(['destroy']);
    Route::get('sales/{sale}/schedule/print', [InstallmentSaleController::class, 'printSchedule'])->name('sales.schedule.print');
    Route::post('sales/{sale}/ledger/whatsapp', [WhatsAppController::class, 'sendSaleLedger'])->name('sales.ledger.whatsapp');

    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
    Route::get('payments/{payment}/pdf', [PaymentController::class, 'pdf'])->name('payments.pdf');
    Route::post('payments/{payment}/whatsapp', [WhatsAppController::class, 'sendReceipt'])->name('payments.whatsapp');
    Route::post('payments/{payment}/confirmation/whatsapp', [WhatsAppController::class, 'sendPaymentConfirmation'])->name('payments.confirmation.whatsapp');

    Route::get('pending-payments', [PendingPaymentController::class, 'index'])->name('pending.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/date-wise-payments', [ReportController::class, 'payments'])->name('reports.date-wise-payments');
        Route::get('reports/monthly-collection', [ReportController::class, 'monthlyCollection'])->name('reports.monthly-collection');
        Route::get('reports/pending', [ReportController::class, 'pending'])->name('reports.pending');
        Route::get('reports/overdue', [ReportController::class, 'overdue'])->name('reports.overdue');
        Route::get('reports/customer-ledgers', [ReportController::class, 'customerLedgers'])->name('reports.customer-ledgers');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/product-wise-sales', [ReportController::class, 'productWiseSales'])->name('reports.product-wise-sales');
        Route::get('reports/investment', [ReportController::class, 'investment'])->name('reports.investment');
        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('reports/active-accounts', [ReportController::class, 'activeAccounts'])->name('reports.active');
        Route::get('reports/completed-accounts', [ReportController::class, 'completedAccounts'])->name('reports.completed');
        Route::get('reports/defaulters', [ReportController::class, 'defaulters'])->name('reports.defaulters');
        Route::get('reports/daily-collection', [ReportController::class, 'dailyCollection'])->name('reports.daily');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::resource('users', UserController::class)->except(['show']);
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});
