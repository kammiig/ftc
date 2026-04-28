<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('company_setting')) {
    function company_setting(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("setting.{$key}", 300, fn () => Setting::query()->where('key', $key)->value('value')) ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }
}

if (! function_exists('company_logo_url')) {
    function company_logo_url(): string
    {
        $logo = company_setting('company_logo');

        if ($logo) {
            return str_starts_with($logo, 'assets/')
                ? asset($logo)
                : \Illuminate\Support\Facades\Storage::url($logo);
        }

        return asset('assets/images/ftc-logo.png');
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        $currency = company_setting('currency_symbol', 'PKR');

        return trim($currency.' '.number_format((float) $amount, 2));
    }
}

if (! function_exists('payment_methods')) {
    function payment_methods(): array
    {
        $value = company_setting('payment_methods', 'Cash,Bank Transfer,JazzCash,Easypaisa,Card,Other');

        return collect(explode(',', (string) $value))
            ->map(fn (string $method) => trim($method))
            ->filter()
            ->values()
            ->all();
    }
}

if (! function_exists('status_badge_class')) {
    function status_badge_class(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'completed', 'available' => 'success',
            'active' => 'primary',
            'partial', 'pending', 'running' => 'warning text-dark',
            'overdue', 'defaulter', 'blocked', 'out_of_stock', 'failed' => 'danger',
            'cancelled', 'inactive', 'sold' => 'secondary',
            default => 'primary',
        };
    }
}

if (! function_exists('readable_status')) {
    function readable_status(?string $status): string
    {
        return str_replace('_', ' ', ucfirst((string) $status));
    }
}
