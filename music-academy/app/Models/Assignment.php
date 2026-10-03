<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = ['course_id', 'lesson_id', 'title', 'instructions', 'due_at', 'max_score'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast();
    }

    public function submissionBy(User $user): ?Submission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }
}
