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

    /** Check if submission file is a MIDI file */
    public function isMidi(): bool
    {
        $extPath = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        $extOrig = strtolower(pathinfo($this->original_name ?? '', PATHINFO_EXTENSION));

        return in_array($extPath, ['mid', 'midi']) || in_array($extOrig, ['mid', 'midi']);
    }

    /** 'audio', 'video' or 'file' — used to pick the right HTML5 / Web component player. */
    public function mediaType(): string
    {
        $extPath = strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
        $extOrig = strtolower(pathinfo($this->original_name ?? '', PATHINFO_EXTENSION));

        if ($this->isMidi()) {
            return 'audio';
        }

        return match (true) {
            in_array($extPath, ['mp3', 'wav', 'm4a', 'ogg', 'aac']) || in_array($extOrig, ['mp3', 'wav', 'm4a', 'ogg', 'aac']) => 'audio',
            in_array($extPath, ['mp4', 'webm', 'mov']) || in_array($extOrig, ['mp4', 'webm', 'mov']) => 'video',
            default => 'file',
        };
    }
}
