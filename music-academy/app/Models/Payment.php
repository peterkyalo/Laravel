<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** Gateways students can choose at checkout. */
    public const METHODS = [
        'stripe' => 'Stripe (Card)',
        'paypal' => 'PayPal',
        'mpesa' => 'M-Pesa',
        'cash' => 'Cash',
    ];

    /** Methods that are confirmed automatically by a provider. */
    public const ONLINE_METHODS = ['stripe', 'paypal', 'mpesa'];

    /** Labels for historical records created before the gateway integration. */
    public const LEGACY_METHODS = [
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'mobile_money' => 'Mobile Money',
    ];

    public const STATUSES = ['pending', 'paid', 'failed', 'cancelled', 'rejected'];

    protected $fillable = [
        'enrollment_id', 'amount', 'currency', 'method', 'reference', 'gateway_reference',
        'phone', 'status', 'paid_at', 'recorded_by', 'notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'pending_payment'], true);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOnline(): bool
    {
        return in_array($this->method, self::ONLINE_METHODS, true);
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method]
            ?? self::LEGACY_METHODS[$this->method]
            ?? ucfirst(str_replace('_', ' ', (string) $this->method));
    }

    public function methodIcon(): string
    {
        return match ($this->method) {
            'stripe', 'card' => 'bi-credit-card-2-front-fill',
            'paypal' => 'bi-paypal',
            'mpesa', 'mobile_money' => 'bi-phone-fill',
            'cash' => 'bi-cash-coin',
            'bank_transfer' => 'bi-bank',
            default => 'bi-wallet2',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'text-bg-success',
            'pending', 'pending_payment' => 'text-bg-warning',
            'rejected', 'failed' => 'text-bg-danger',
            'cancelled' => 'text-bg-secondary',
            default => 'text-bg-secondary',
        };
    }

    /** Merge extra gateway data into the meta column. */
    public function mergeMeta(array $data): void
    {
        $this->meta = array_merge($this->meta ?? [], $data);
    }
}
