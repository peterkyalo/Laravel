<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id', 'user_id', 'file_path', 'original_name', 'notes',
        'score', 'feedback', 'graded_at', 'graded_by',
    ];

    protected function casts(): array
    {
        return ['graded_at' => 'datetime'];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isGraded(): bool
    {
        return $this->graded_at !== null;
    }

    public function fileUrl(): string
    {
        return asset('storage/'.$this->file_path);
    }

    /** 'audio', 'video' or 'file' — used to pick the right HTML5 player. */
    public function mediaType(): string
    {
        $ext = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));

        return match (true) {
            in_array($ext, ['mp3', 'wav', 'm4a', 'ogg', 'aac']) => 'audio',
            in_array($ext, ['mp4', 'webm', 'mov']) => 'video',
            default => 'file',
        };
    }
}
