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

if (! function_exists('file_data_uri')) {
    function file_data_uri(?string $absolutePath): ?string
    {
        if (! $absolutePath || ! is_file($absolutePath)) {
            return null;
        }

        $mime = function_exists('mime_content_type') ? @mime_content_type($absolutePath) : null;

        $mime = match ($mime) {
            'image/x-png' => 'image/png',
            'image/pjpeg' => 'image/jpeg',
            default => $mime,
        };

        $mime ??= match (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => null,
        };

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            return null;
        }

        $contents = @file_get_contents($absolutePath);

        return $contents === false ? null : 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}

if (! function_exists('company_logo_data_uri')) {
    function company_logo_data_uri(): ?string
    {
        $logo = company_setting('company_logo');

        if ($logo && ! str_starts_with($logo, 'assets/')) {
            return file_data_uri(storage_path('app/public/'.$logo));
        }

        return file_data_uri(public_path($logo ?: 'assets/images/ftc-logo.png'));
    }
}

if (! function_exists('signature_name')) {
    function signature_name(): string
    {
        return authorized_signature_name();
    }
}

if (! function_exists('signature_image_data_uri')) {
    function signature_image_data_uri(): ?string
    {
        return authorized_signature_data_uri();
    }
}

if (! function_exists('authorized_signature_name')) {
    function authorized_signature_name(): string
    {
        return trim((string) company_setting('authorized_person_name', ''));
    }
}

if (! function_exists('authorized_signature_data_uri')) {
    function authorized_signature_data_uri(): ?string
    {
        $signature = company_setting('digital_signature_image');

        return $signature ? file_data_uri(storage_path('app/public/'.$signature)) : null;
    }
}

if (! function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false): bool
    {
        $value = company_setting($key, $default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('show_signature_on_ledger')) {
    function show_signature_on_ledger(): bool
    {
        return setting_bool('show_signature_on_ledger', true);
    }
}

if (! function_exists('show_signature_on_receipt')) {
    function show_signature_on_receipt(): bool
    {
        return setting_bool('show_signature_on_receipt', true);
    }
}

if (! function_exists('can_view_financials')) {
    function can_view_financials(mixed $user = null): bool
    {
        $user ??= auth()->user();

        return (bool) ($user?->isAdmin());
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
