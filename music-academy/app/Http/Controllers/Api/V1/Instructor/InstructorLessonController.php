<?php

namespace App\Http\Controllers\Api\V1\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lesson\StoreLessonRequest;
use App\Http\Resources\V1\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorLessonController extends Controller
{
    /**
     * Store new lesson under a course.
     */
    public function store(StoreLessonRequest $request, Course $course): JsonResponse
    {
        $this->authorizeCourse($course);

        $data = $request->safe()->except(['audio_file', 'sheet_music_file', 'tab_file']);
        $data['course_id'] = $course->id;

        if ($request->hasFile('audio_file')) {
            $data['audio_url'] = $request->file('audio_file')->store('lessons/audio', 'public');
        }

        if ($request->hasFile('sheet_music_file')) {
            $data['sheet_music_url'] = $request->file('sheet_music_file')->store('lessons/sheet_music', 'public');
        }

        if ($request->hasFile('tab_file')) {
            $data['tab_url'] = $request->file('tab_file')->store('lessons/tabs', 'public');
        }

        $data['position'] = $data['position'] ?? $data['order'] ?? (($course->lessons()->max('position') ?? 0) + 1);
        if (isset($data['is_free_preview'])) {
            $data['is_preview'] = $data['is_free_preview'];
        }
        unset($data['order'], $data['is_free_preview']);

        $lesson = Lesson::create($data);

        return response()->json([
            'message' => 'Lesson created successfully.',
            'lesson' => new LessonResource($lesson),
        ], 201);
    }

    /**
     * Update lesson.
     */
    public function update(StoreLessonRequest $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorizeCourse($course);

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Lesson does not belong to this course.'], 404);
        }

        $data = $request->safe()->except(['audio_file', 'sheet_music_file', 'tab_file']);

        if ($request->hasFile('audio_file')) {
            if ($lesson->audio_url) {
                Storage::disk('public')->delete($lesson->audio_url);
            }
            $data['audio_url'] = $request->file('audio_file')->store('lessons/audio', 'public');
        }

        if ($request->hasFile('sheet_music_file')) {
            if ($lesson->sheet_music_url) {
                Storage::disk('public')->delete($lesson->sheet_music_url);
            }
            $data['sheet_music_url'] = $request->file('sheet_music_file')->store('lessons/sheet_music', 'public');
        }

        if ($request->hasFile('tab_file')) {
            if ($lesson->tab_url) {
                Storage::disk('public')->delete($lesson->tab_url);
            }
            $data['tab_url'] = $request->file('tab_file')->store('lessons/tabs', 'public');
        }

        $lesson->update($data);

        return response()->json([
            'message' => 'Lesson updated successfully.',
            'lesson' => new LessonResource($lesson),
        ]);
    }

    /**
     * Delete lesson.
     */
    public function destroy(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorizeCourse($course);

        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Lesson does not belong to this course.'], 404);
        }

        if ($lesson->audio_url) Storage::disk('public')->delete($lesson->audio_url);
        if ($lesson->sheet_music_url) Storage::disk('public')->delete($lesson->sheet_music_url);
        if ($lesson->tab_url) Storage::disk('public')->delete($lesson->tab_url);

        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully.',
        ]);
    }
}
