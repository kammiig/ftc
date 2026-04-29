<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerGuarantor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
            'guarantors' => $this->guarantorSlots(),
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $customer = DB::transaction(function () use ($request, $data): Customer {
            $customerData = Arr::except($data, ['guarantors']);
            $this->handleUploads($request, $customerData);

            $customer = Customer::query()->create($customerData);
            $this->syncGuarantors($request, $customer, $data['guarantors'] ?? []);

            ActivityLog::record('customer_created', 'Customer added: '.$customer->name, $customer);

            return $customer;
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer profile created.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($query) => $query->latest(),
            'sales.schedules',
            'payments' => fn ($query) => $query->with('sale')->latest('payment_date')->limit(10),
            'ledgers' => fn ($query) => $query->orderBy('entry_date')->orderBy('id'),
            'guarantors',
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'activeSales' => $customer->sales->whereIn('status', ['active', 'defaulter']),
            'completedSales' => $customer->sales->where('status', 'completed'),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $customer->load('guarantors');

        return view('customers.edit', [
            'customer' => $customer,
            'guarantors' => $this->guarantorSlots($customer),
            'statuses' => Customer::STATUSES,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer);

        DB::transaction(function () use ($request, $data, $customer): void {
            $customerData = Arr::except($data, ['guarantors']);
            $this->handleUploads($request, $customerData, $customer);

            $customer->update($customerData);
            $this->syncGuarantors($request, $customer, $data['guarantors'] ?? []);

            ActivityLog::record('customer_updated', 'Customer updated: '.$customer->name, $customer);
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer profile updated.');
    }

    public function print(Customer $customer): View
    {
        $customer->load([
            'guarantors',
            'sales' => fn ($query) => $query->latest(),
            'sales.schedules',
            'payments' => fn ($query) => $query->with('sale')->latest('payment_date'),
        ]);

        return view('customers.print', compact('customer'));
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
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'alternate_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', Customer::STATUSES)],
            'photo' => ['nullable', 'image', 'max:2048'],
            'cnic_front' => ['nullable', 'image', 'max:4096'],
            'cnic_back' => ['nullable', 'image', 'max:4096'],
            'guarantors' => ['nullable', 'array', 'max:2'],
            'guarantors.*.full_name' => ['nullable', 'string', 'max:191'],
            'guarantors.*.guardian_name' => ['nullable', 'string', 'max:191'],
            'guarantors.*.cnic' => ['nullable', 'string', 'max:50'],
            'guarantors.*.phone' => ['nullable', 'string', 'max:50'],
            'guarantors.*.alternate_phone' => ['nullable', 'string', 'max:50'],
            'guarantors.*.address' => ['nullable', 'string'],
            'guarantors.*.relationship' => ['nullable', 'string', 'max:100'],
            'guarantors.*.photo' => ['nullable', 'image', 'max:2048'],
            'guarantors.*.cnic_front' => ['nullable', 'image', 'max:4096'],
            'guarantors.*.cnic_back' => ['nullable', 'image', 'max:4096'],
            'guarantors.*.notes' => ['nullable', 'string'],
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

    private function guarantorSlots(?Customer $customer = null): array
    {
        $existing = $customer?->guarantors?->keyBy('position') ?? collect();

        return [
            1 => $existing->get(1, new CustomerGuarantor(['position' => 1])),
            2 => $existing->get(2, new CustomerGuarantor(['position' => 2])),
        ];
    }

    private function syncGuarantors(Request $request, Customer $customer, array $guarantors): void
    {
        foreach ([1, 2] as $position) {
            $payload = $guarantors[$position] ?? [];
            $existing = $customer->guarantors()->where('position', $position)->first();

            $hasFiles = $request->hasFile("guarantors.{$position}.photo")
                || $request->hasFile("guarantors.{$position}.cnic_front")
                || $request->hasFile("guarantors.{$position}.cnic_back");

            $hasDetails = collect($payload)
                ->except(['photo', 'cnic_front', 'cnic_back'])
                ->filter(fn ($value) => filled($value))
                ->isNotEmpty();

            if (! $hasDetails && ! $hasFiles) {
                if ($existing) {
                    $this->deleteGuarantorFiles($existing);
                    $existing->delete();
                    ActivityLog::record('guarantor_deleted', 'Guarantor '.$position.' removed for '.$customer->name, $customer);
                }

                continue;
            }

            $data = Arr::only($payload, [
                'full_name',
                'guardian_name',
                'cnic',
                'phone',
                'alternate_phone',
                'address',
                'relationship',
                'notes',
            ]);
            $data['position'] = $position;

            $this->handleGuarantorUploads($request, $data, $existing, $position, $customer);

            $guarantor = $customer->guarantors()->updateOrCreate(
                ['position' => $position],
                $data
            );

            ActivityLog::record($existing ? 'guarantor_updated' : 'guarantor_created', 'Guarantor '.$position.' saved for '.$customer->name, $guarantor);
        }
    }

    private function handleGuarantorUploads(Request $request, array &$data, ?CustomerGuarantor $guarantor, int $position, Customer $customer): void
    {
        foreach ([
            'photo' => 'photo_path',
            'cnic_front' => 'cnic_front_path',
            'cnic_back' => 'cnic_back_path',
        ] as $field => $column) {
            if (! $request->hasFile("guarantors.{$position}.{$field}")) {
                continue;
            }

            if ($guarantor?->{$column}) {
                Storage::disk('public')->delete($guarantor->{$column});
            }

            $data[$column] = $request->file("guarantors.{$position}.{$field}")->store('guarantors/'.$customer->id, 'public');
        }
    }

    private function deleteGuarantorFiles(CustomerGuarantor $guarantor): void
    {
        foreach (['photo_path', 'cnic_front_path', 'cnic_back_path'] as $column) {
            if ($guarantor->{$column}) {
                Storage::disk('public')->delete($guarantor->{$column});
            }
        }
    }

}
