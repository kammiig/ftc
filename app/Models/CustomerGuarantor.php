<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerGuarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'position',
        'full_name',
        'guardian_name',
        'cnic',
        'phone',
        'alternate_phone',
        'address',
        'relationship',
        'photo_path',
        'cnic_front_path',
        'cnic_back_path',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function hasDetails(): bool
    {
        return collect($this->only([
            'full_name',
            'guardian_name',
            'cnic',
            'phone',
            'alternate_phone',
            'address',
            'relationship',
            'photo_path',
            'cnic_front_path',
            'cnic_back_path',
            'notes',
        ]))->filter(fn ($value) => filled($value))->isNotEmpty();
    }
}
