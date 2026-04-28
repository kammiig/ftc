<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use HasFactory;

    public const TYPES = ['database', 'full'];
    public const STATUSES = ['running', 'completed', 'failed'];

    protected $fillable = [
        'filename',
        'path',
        'type',
        'status',
        'size_bytes',
        'message',
        'created_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sizeForHumans(): string
    {
        $bytes = max((int) $this->size_bytes, 0);
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return number_format($bytes, $index === 0 ? 0 : 2).' '.$units[$index];
    }
}
