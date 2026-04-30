@extends('layouts.app')

@section('title', 'Reports')
@section('subtitle', 'Simple collection, ledger, and installment account reports')

@section('content')
@php
    $reports = [
        ['title' => 'Daily Collection Report', 'route' => route('reports.daily'), 'icon' => 'calendar-check'],
        ['title' => 'Monthly Collection Report', 'route' => route('reports.monthly-collection'), 'icon' => 'calendar-days'],
        ['title' => 'Pending Payments Report', 'route' => route('reports.pending'), 'icon' => 'hourglass'],
        ['title' => 'Overdue Customers Report', 'route' => route('reports.overdue'), 'icon' => 'triangle-alert'],
        ['title' => 'Customer Ledger Report', 'route' => route('reports.customer-ledgers'), 'icon' => 'book-open'],
        ['title' => 'Active Installment Accounts Report', 'route' => route('reports.active'), 'icon' => 'activity'],
        ['title' => 'Completed Installment Accounts Report', 'route' => route('reports.completed'), 'icon' => 'check-circle-2'],
    ];

    if (can_view_financials()) {
        $reports[] = ['title' => 'Investment Report', 'route' => route('reports.investment'), 'icon' => 'landmark'];
        $reports[] = ['title' => 'Profit Report', 'route' => route('reports.profit'), 'icon' => 'trending-up'];
    }
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
