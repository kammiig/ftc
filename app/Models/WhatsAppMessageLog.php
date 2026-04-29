<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'payment_id',
        'installment_sale_id',
        'whatsapp_number',
        'message_type',
        'pdf_file_path',
        'status',
        'api_response',
        'error_message',
        'sent_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(InstallmentSale::class, 'installment_sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
