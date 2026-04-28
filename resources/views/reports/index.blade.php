@extends('layouts.app')

@section('title', 'Reports')
@section('subtitle', 'Printable and CSV exportable business reports')

@section('content')
@php
    $reports = [
        ['title' => 'Monthly Received Payments', 'route' => route('reports.payments'), 'icon' => 'wallet'],
        ['title' => 'Monthly Pending Payments', 'route' => route('reports.pending'), 'icon' => 'hourglass'],
        ['title' => 'Overdue Customers', 'route' => route('reports.overdue'), 'icon' => 'triangle-alert'],
        ['title' => 'Customer Ledger Report', 'route' => route('reports.customer-ledgers'), 'icon' => 'book-open'],
        ['title' => 'Product Installment Sales', 'route' => route('reports.sales'), 'icon' => 'package-check'],
        ['title' => 'Total Investment', 'route' => route('reports.investment'), 'icon' => 'landmark'],
        ['title' => 'Profit Report', 'route' => route('reports.profit'), 'icon' => 'trending-up'],
        ['title' => 'Active Accounts', 'route' => route('reports.active'), 'icon' => 'activity'],
        ['title' => 'Completed Accounts', 'route' => route('reports.completed'), 'icon' => 'check-circle-2'],
        ['title' => 'Defaulter Customers', 'route' => route('reports.defaulters'), 'icon' => 'user-x'],
        ['title' => 'Daily Collection', 'route' => route('reports.daily'), 'icon' => 'calendar-check'],
    ];
@endphp

<div class="row g-3">
    @foreach($reports as $report)
        <div class="col-md-6 col-xl-4">
            <a class="card h-100 text-decoration-none text-dark" href="{{ $report['route'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="metric-icon"><i data-lucide="{{ $report['icon'] }}"></i></span>
                    <strong>{{ $report['title'] }}</strong>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
