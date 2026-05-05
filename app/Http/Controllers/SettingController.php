<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::query()->pluck('value', 'key')->all();

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:191'],
            'company_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:4096'],
            'currency_symbol' => ['required', 'string', 'max:20'],
            'company_phone' => ['nullable', 'string', 'max:100'],
            'company_email' => ['nullable', 'email', 'max:191'],
            'company_address' => ['nullable', 'string'],
            'receipt_footer_text' => ['nullable', 'string'],
            'ledger_footer_text' => ['nullable', 'string'],
            'payment_methods' => ['required', 'string'],
            'default_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'authorized_person_name' => ['nullable', 'string', 'max:191'],
            'digital_signature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'show_signature_on_ledger' => ['nullable', 'boolean'],
            'show_signature_on_receipt' => ['nullable', 'boolean'],
        ]);

        $data['show_signature_on_ledger'] = $request->boolean('show_signature_on_ledger') ? '1' : '0';
        $data['show_signature_on_receipt'] = $request->boolean('show_signature_on_receipt') ? '1' : '0';

        if ($request->hasFile('company_logo')) {
            $oldLogo = company_setting('company_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $data['company_logo'] = $request->file('company_logo')->store('settings', 'public');
        } else {
            unset($data['company_logo']);
        }

        if ($request->hasFile('digital_signature_image')) {
            $oldSignature = company_setting('digital_signature_image') ?: company_setting('signature_image');
            if ($oldSignature) {
                Storage::disk('public')->delete($oldSignature);
            }
            $data['digital_signature_image'] = $request->file('digital_signature_image')->store('settings', 'public');
        } else {
            unset($data['digital_signature_image']);
        }

        $data['signature_name'] = $data['authorized_person_name'] ?? '';
        if (isset($data['digital_signature_image'])) {
            $data['signature_image'] = $data['digital_signature_image'];
        }

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        Cache::flush();
        ActivityLog::record('settings_updated', 'Company settings updated.');

        return back()->with('success', 'Settings saved.');
    }
}
