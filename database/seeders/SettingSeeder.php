<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'FTC',
            'company_logo' => 'assets/images/ftc-logo.png',
            'currency_symbol' => 'PKR',
            'company_phone' => '',
            'company_email' => 'contact@ftc.com',
            'company_address' => '',
            'receipt_footer_text' => 'Thank you for your payment.',
            'ledger_footer_text' => 'This ledger is system generated and subject to verification.',
            'payment_methods' => 'Cash,Bank Transfer,JazzCash,Easypaisa,Card,Other',
            'default_due_day' => '1',
            'authorized_person_name' => '',
            'digital_signature_image' => '',
            'show_signature_on_ledger' => '1',
            'show_signature_on_receipt' => '1',
            'signature_name' => '',
            'signature_image' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'text']
            );
        }
    }
}
