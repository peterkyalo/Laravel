<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    protected $fillable = [
        'instrument_id', 'instructor_id', 'title', 'slug', 'level', 'short_description',
        'description', 'cover_image', 'fee', 'duration_weeks', 'status',
    ];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if (blank($course->slug) || $course->isDirty('title')) {
                $base = Str::slug($course->title);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $course->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $course->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['id', 'status', 'progress', 'enrolled_at', 'completed_at'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class)->orderBy('starts_at');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function isFree(): bool
    {
        return (float) $this->fee <= 0;
    }

    public function coverUrl(): string
    {
        $path = $this->cover_image;

        return match (true) {
            blank($path) => asset('images/course-default.svg'),
            str_starts_with($path, 'http') => $path,
            str_starts_with($path, 'images/') => asset($path), // bundled demo images in /public
            default => asset('storage/'.$path),                // uploaded files
        };
    }

    public function levelBadgeClass(): string
    {
        return match ($this->level) {
            'beginner' => 'badge-level-beginner',
            'intermediate' => 'badge-level-intermediate',
            'advanced' => 'badge-level-advanced',
            default => 'bg-secondary',
        };
    }

    public function totalMinutes(): int
    {
        return (int) $this->lessons->sum('duration_minutes');
    }
}
