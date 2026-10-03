<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSession extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'starts_at', 'ends_at', 'location', 'meeting_url'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Event payload for FullCalendar. */
    public function toCalendarEvent(): array
    {
        $palette = ['#6366f1', '#f59e0b', '#10b981', '#ec4899', '#06b6d4', '#8b5cf6', '#ef4444'];

        return [
            'id' => $this->id,
            'title' => $this->course->title.' — '.$this->title,
            'start' => $this->starts_at->toIso8601String(),
            'end' => $this->ends_at->toIso8601String(),
            'color' => $palette[$this->course_id % count($palette)],
            'extendedProps' => [
                'course' => $this->course->title,
                'session' => $this->title,
                'location' => $this->location,
                'meeting_url' => $this->meeting_url,
                'description' => $this->description,
                'time' => $this->starts_at->format('D, M j · g:i A').' – '.$this->ends_at->format('g:i A'),
            ],
        ];
    }
}
