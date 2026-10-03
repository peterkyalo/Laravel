<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'pass_mark', 'time_limit_minutes', 'is_required', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_published' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function bestAttemptBy(User $user): ?QuizAttempt
    {
        return $this->attempts()->where('user_id', $user->id)->orderByDesc('score')->first();
    }

    /**
     * Grade a set of answers ([question_id => option_id]) and store an attempt.
     */
    public function grade(User $user, array $answers): QuizAttempt
    {
        $questions = $this->questions()->with('options')->get();
        $totalPoints = max(1, $questions->sum('points'));
        $earned = 0;

        foreach ($questions as $question) {
            $chosen = (int) ($answers[$question->id] ?? 0);
            $correct = $question->options->firstWhere('is_correct', true);
            if ($correct && $correct->id === $chosen) {
                $earned += $question->points;
            }
        }

        $score = round($earned / $totalPoints * 100, 2);

        return $this->attempts()->create([
            'user_id' => $user->id,
            'score' => $score,
            'passed' => $score >= $this->pass_mark,
            'answers' => array_map('intval', $answers),
            'completed_at' => now(),
        ]);
    }
}
