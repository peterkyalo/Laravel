<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Quiz\AddQuestionRequest;
use App\Http\Requests\Api\V1\Quiz\StoreQuizRequest;
use App\Http\Resources\V1\QuestionResource;
use App\Http\Resources\V1\QuizResource;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorQuizController extends Controller
{
    /**
     * Create a new quiz under a course.
     */
    public function store(StoreQuizRequest $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $quiz = $course->quizzes()->create($request->validated());

        return response()->json([
            'message' => 'Quiz created successfully.',
            'quiz' => new QuizResource($quiz),
        ], 201);
    }

    /**
     * Add question and options to a quiz.
     */
    public function addQuestion(AddQuestionRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorizeCourse($quiz->course);

        $data = [
            'question_text' => $request->question_text,
            'points' => $request->points,
        ];

        if ($request->hasFile('audio_file')) {
            $data['audio_path'] = $request->file('audio_file')->store('quizzes/audio', 'public');
        }

        $question = $quiz->questions()->create($data);

        foreach ($request->options as $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) $option['is_correct'],
            ]);
        }

        return response()->json([
            'message' => 'Question added successfully.',
            'question' => new QuestionResource($question->load('options')),
        ], 201);
    }

    /**
     * Delete question from quiz.
     */
    public function deleteQuestion(Request $request, Quiz $quiz, Question $question): JsonResponse
    {
        $this->authorizeCourse($quiz->course);

        if ($question->quiz_id !== $quiz->id) {
            return response()->json(['message' => 'Question does not belong to this quiz.'], 404);
        }

        if ($question->audio_path) {
            Storage::disk('public')->delete($question->audio_path);
        }

        $question->options()->delete();
        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }

    /**
     * Toggle publish status of a quiz.
     */
    public function togglePublish(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorizeCourse($quiz->course);

        $quiz->update([
            'is_published' => ! $quiz->is_published,
        ]);

        return response()->json([
            'message' => $quiz->is_published ? 'Quiz published.' : 'Quiz unpublished.',
            'is_published' => (bool) $quiz->is_published,
        ]);
    }

    /**
     * Delete quiz.
     */
    public function destroy(Request $request, Quiz $quiz): JsonResponse
    {
        $this->authorizeCourse($quiz->course);

        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully.',
        ]);
    }
}
