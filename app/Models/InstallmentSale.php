<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentSale extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'completed', 'cancelled', 'defaulter'];

    protected $fillable = [
        'account_number',
        'customer_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_cost_price',
        'installment_sale_price',
        'advance_payment',
        'remaining_balance',
        'installments_count',
        'monthly_installment_amount',
        'installment_start_date',
        'monthly_due_day',
        'total_paid',
        'pending_balance',
        'profit_amount',
        'status',
        'remarks',
        'created_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'product_cost_price' => 'decimal:2',
            'installment_sale_price' => 'decimal:2',
            'advance_payment' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'monthly_installment_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'pending_balance' => 'decimal:2',
            'profit_amount' => 'decimal:2',
            'installment_start_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(InstallmentSchedule::class);
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
                $query->where('account_number', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('product_sku', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $query) use ($search): void {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('cnic', 'like', "%{$search}%");
                    });
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $query) => $query->where('status', $status));
    }
}
