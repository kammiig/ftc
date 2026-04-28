<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = ['active', 'completed', 'defaulter', 'blocked'];

    protected $fillable = [
        'name',
        'guardian_name',
        'cnic',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'photo_path',
        'cnic_front_path',
        'cnic_back_path',
        'guarantor_name',
        'guarantor_cnic',
        'guarantor_phone',
        'guarantor_address',
        'notes',
        'status',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(InstallmentSale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query) use ($search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('alternate_phone', 'like', "%{$search}%")
                    ->orWhere('cnic', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $query) => $query->where('status', $status));
    }

    public function currentBalance(): float
    {
        return (float) ($this->ledgers()->latest('entry_date')->latest('id')->value('balance') ?? 0);
    }

    public function totalDebit(?string $from = null, ?string $to = null): float
    {
        return (float) $this->ledgers()
            ->when($from, fn (Builder $query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('entry_date', '<=', $to))
            ->sum('debit');
    }

    public function totalCredit(?string $from = null, ?string $to = null): float
    {
        return (float) $this->ledgers()
            ->when($from, fn (Builder $query) => $query->whereDate('entry_date', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('entry_date', '<=', $to))
            ->sum('credit');
    }
}
