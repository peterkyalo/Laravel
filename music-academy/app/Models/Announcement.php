<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['user_id', 'course_id', 'title', 'body', 'audience', 'is_pinned'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Announcements visible to the given user. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $courseIds = $user->isInstructor()
            ? $user->taughtCourses()->pluck('id')
            : $user->enrollments()->whereIn('status', ['active', 'completed'])->pluck('course_id');

        $audience = $user->isInstructor() ? 'instructors' : 'students';

        return $query->where(function (Builder $q) use ($courseIds, $audience, $user) {
            $q->where(fn ($g) => $g->whereNull('course_id')->whereIn('audience', ['all', $audience]))
                ->orWhereIn('course_id', $courseIds)
                ->orWhere('user_id', $user->id);
        });
    }
}
