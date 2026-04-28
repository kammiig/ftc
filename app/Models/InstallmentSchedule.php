<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentSchedule extends Model
{
    use HasFactory;

    public const STATUSES = ['paid', 'partial', 'pending', 'overdue'];

    protected $fillable = [
        'installment_sale_id',
        'customer_id',
        'installment_number',
        'due_date',
        'due_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'paid_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'date',
            'due_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(InstallmentSale::class, 'installment_sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue'])
            ->whereDate('due_date', '<', now()->toDateString());
    }
}
