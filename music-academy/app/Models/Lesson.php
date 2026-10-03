<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'title', 'position', 'summary', 'content', 'video_url', 'video_path',
        'sheet_music_path', 'audio_path', 'duration_minutes', 'is_preview',
    ];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function isCompletedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->progress()->where('user_id', $user->id)->whereNotNull('completed_at')->exists();
    }

    /** Convert a YouTube / Vimeo URL into an embeddable URL. */
    public function embedUrl(): ?string
    {
        $url = $this->video_url;
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}
