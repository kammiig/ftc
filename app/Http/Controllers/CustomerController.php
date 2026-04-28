<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->search($request->string('search')->toString())
            ->status($request->string('status')->toString() ?: null)
            ->withCount(['sales', 'payments'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('customers.create', [
            'customer' => new Customer(['status' => 'active']),
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->handleUploads($request, $data);

        $customer = Customer::query()->create($data);
        ActivityLog::record('customer_created', 'Customer added: '.$customer->name, $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer profile created.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($query) => $query->latest(),
            'sales.schedules',
            'payments' => fn ($query) => $query->latest('payment_date')->limit(10),
            'ledgers' => fn ($query) => $query->orderBy('entry_date')->orderBy('id'),
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'activeSales' => $customer->sales->whereIn('status', ['active', 'defaulter']),
            'completedSales' => $customer->sales->where('status', 'completed'),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', [
            'customer' => $customer,
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer);
        $this->handleUploads($request, $data, $customer);

        $customer->update($data);
        ActivityLog::record('customer_updated', 'Customer updated: '.$customer->name, $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer profile updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->whereIn('status', ['active', 'defaulter'])->exists()) {
            return back()->with('error', 'Customer has active installment accounts and cannot be deleted.');
        }

        ActivityLog::record('customer_deleted', 'Customer deleted: '.$customer->name, $customer);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $customerId = $customer?->id ?? 'NULL';

        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'guardian_name' => ['nullable', 'string', 'max:191'],
            'cnic' => ['nullable', 'string', 'max:50', 'unique:customers,cnic,'.$customerId],
            'phone' => ['required', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'guarantor_name' => ['nullable', 'string', 'max:191'],
            'guarantor_cnic' => ['nullable', 'string', 'max:50'],
            'guarantor_phone' => ['nullable', 'string', 'max:50'],
            'guarantor_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', Customer::STATUSES)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'cnic_front' => ['nullable', 'image', 'max:4096'],
            'cnic_back' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function handleUploads(Request $request, array &$data, ?Customer $customer = null): void
    {
        foreach (['photo' => 'photo_path', 'cnic_front' => 'cnic_front_path', 'cnic_back' => 'cnic_back_path'] as $field => $column) {
            unset($data[$field]);

            if (! $request->hasFile($field)) {
                continue;
            }

            if ($customer?->{$column}) {
                Storage::disk('public')->delete($customer->{$column});
            }

            $data[$column] = $request->file($field)->store('customers', 'public');
        }
    }
}
