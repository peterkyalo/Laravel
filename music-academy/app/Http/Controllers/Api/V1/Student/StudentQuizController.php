<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Quiz\SubmitQuizRequest;
use App\Http\Resources\V1\QuizAttemptResource;
use App\Http\Resources\V1\QuizResource;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentQuizController extends Controller
{
    /**
     * List quizzes available in student enrolled courses.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        $quizzes = Quiz::whereIn('course_id', $enrolledCourseIds)
            ->where('is_published', true)
            ->with(['course', 'attempts' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->withCount('questions')
            ->paginate(15);

        return QuizResource::collection($quizzes);
    }

    /**
     * Show quiz overview.
     */
    public function show(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $quiz->load(['course', 'attempts' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }]);

        return response()->json([
            'quiz' => new QuizResource($quiz),
        ]);
    }

    /**
     * Get quiz questions to take the quiz.
     */
    public function take(Request $request, Quiz $quiz): JsonResponse
    {
        $quiz->load(['questions.options' => function ($q) {
            $q->select(['id', 'question_id', 'option_text']); // Exclude correct answers
        }]);

        return response()->json([
            'quiz' => new QuizResource($quiz),
        ]);
    }

    /**
     * Submit quiz responses and calculate score.
     */
    public function submit(SubmitQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $submittedAnswers = $request->answers; // array of question_id => selected_option_id

        $questions = $quiz->questions()->with('options')->get();
        $totalQuestions = $questions->count();
        $correctCount = 0;

        foreach ($questions as $question) {
            $selectedOptionId = $submittedAnswers[$question->id] ?? null;
            if ($selectedOptionId) {
                $isCorrect = $question->options->where('id', $selectedOptionId)->where('is_correct', true)->isNotEmpty();
                if ($isCorrect) {
                    $correctCount++;
                }
            }
        }

        $percentage = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
        $passed = $percentage >= $quiz->pass_score;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $percentage,
            'passed' => $passed,
            'answers' => $submittedAnswers,
        ]);

        return response()->json([
            'message' => $passed ? 'Congratulations! You passed the quiz.' : 'Quiz completed. Keep practicing!',
            'attempt' => new QuizAttemptResource($attempt),
        ], 201);
    }

    /**
     * View attempt results.
     */
    public function results(Quiz $quiz, QuizAttempt $attempt): JsonResponse
    {
        return response()->json([
            'quiz' => new QuizResource($quiz),
            'attempt' => new QuizAttemptResource($attempt->load('user')),
        ]);
    }
}
