<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = ['available', 'sold', 'out_of_stock', 'inactive'];

    protected $fillable = [
        'name',
        'category',
        'brand_model',
        'sku',
        'cost_price',
        'cash_sale_price',
        'installment_sale_price',
        'stock_quantity',
        'description',
        'image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'cash_sale_price' => 'decimal:2',
            'installment_sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(InstallmentSale::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query) use ($search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('brand_model', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $query) => $query->where('status', $status));
    }

    public function isSellable(): bool
    {
        return $this->status === 'available' && $this->stock_quantity > 0;
    }
}
