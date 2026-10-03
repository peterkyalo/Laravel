<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'mobile_money' => 'Mobile Money',
    ];

    protected $fillable = ['enrollment_id', 'amount', 'method', 'reference', 'status', 'paid_at', 'recorded_by', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'text-bg-success',
            'pending' => 'text-bg-warning',
            'rejected' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}
