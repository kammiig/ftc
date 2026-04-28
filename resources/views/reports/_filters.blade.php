<form method="GET" class="d-flex flex-wrap gap-2 mb-3 no-print">
    <input class="form-control" style="width: 230px" name="search" value="{{ request('search') }}" placeholder="Search">
    <input type="date" class="form-control" style="width: 160px" name="from" value="{{ request('from') }}">
    <input type="date" class="form-control" style="width: 160px" name="to" value="{{ request('to') }}">
    @isset($statuses)
        <select class="form-select" style="width: 170px" name="status">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ readable_status($status) }}</option>
            @endforeach
        </select>
    @endisset
    @isset($paymentMethods)
        <select class="form-select" style="width: 170px" name="method">
            <option value="">All methods</option>
            @foreach($paymentMethods as $method)
                <option value="{{ $method }}" @selected(request('method') === $method)>{{ $method }}</option>
            @endforeach
        </select>
    @endisset
    <button class="btn btn-outline-primary"><i data-lucide="filter"></i></button>
    <a class="btn btn-outline-success" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"><i data-lucide="download"></i> CSV</a>
    <button class="btn btn-outline-dark" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print / PDF</button>
</form>
