<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('ftc:about', function () {
    $this->info('FTC Installment Management System is ready.');
})->purpose('Show FTC application status');
