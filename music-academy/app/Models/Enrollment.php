<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    protected $fillable = ['user_id', 'course_id', 'status', 'progress', 'enrolled_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function balance(): float
    {
        return max(0, round((float) $this->course->fee - $this->amountPaid(), 2));
    }

    /** Free courses are always "paid"; paid courses require the full fee to be settled. */
    public function isFullyPaid(): bool
    {
        return $this->course->isFree() || $this->balance() <= 0;
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->where('status', 'pending')->exists();
    }

    public function latestPendingPayment(): ?Payment
    {
        return $this->payments()->where('status', 'pending')->latest()->first();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'text-bg-primary',
            'completed' => 'text-bg-success',
            'pending' => 'text-bg-warning',
            'cancelled' => 'text-bg-secondary',
            default => 'text-bg-light',
        };
    }

    /**
     * Activate a pending enrollment — only once the FULL tuition has been paid.
     * Returns true when the enrollment is (now) active.
     */
    public function activate(): bool
    {
        if ($this->status !== 'pending') {
            return in_array($this->status, ['active', 'completed'], true);
        }

        if (! $this->isFullyPaid()) {
            return false;
        }

        $this->update(['status' => 'active', 'enrolled_at' => $this->enrolled_at ?? now()]);

        return true;
    }

    /**
     * Recalculate lesson progress; mark the enrollment completed and issue a
     * certificate once all lessons are done and all required quizzes are passed.
     */
    public function recalculateProgress(): void
    {
        $course = $this->course;
        $lessonIds = $course->lessons()->pluck('id');
        $total = $lessonIds->count();

        $done = $total === 0 ? 0 : LessonProgress::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->count();

        $progress = $total === 0 ? 0 : (int) floor($done / $total * 100);
        $this->progress = $progress;

        if ($progress >= 100 && $this->requiredQuizzesPassed() && in_array($this->status, ['active', 'completed'])) {
            $this->status = 'completed';
            $this->completed_at ??= now();
            $this->save();
            $this->issueCertificate();

            return;
        }

        if ($this->status === 'completed' && $progress < 100) {
            $this->status = 'active';
            $this->completed_at = null;
        }

        $this->save();
    }

    public function requiredQuizzesPassed(): bool
    {
        $required = $this->course->quizzes()->where('is_published', true)->where('is_required', true)->pluck('id');

        if ($required->isEmpty()) {
            return true;
        }

        $passed = QuizAttempt::where('user_id', $this->user_id)
            ->whereIn('quiz_id', $required)
            ->where('passed', true)
            ->distinct()
            ->count('quiz_id');

        return $passed >= $required->count();
    }

    public function issueCertificate(): Certificate
    {
        return $this->certificate()->firstOrCreate([], [
            'code' => strtoupper('HMA-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4)),
            'issued_at' => now(),
        ]);
    }
}
