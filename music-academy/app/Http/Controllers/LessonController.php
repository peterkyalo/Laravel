<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        $this->authorizeCourse($course);
        $nextPosition = ($course->lessons()->max('position') ?? 0) + 1;

        return view('lessons.create', compact('course', 'nextPosition'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:1'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['boolean'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'sheet_music_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'],
        ]);

        $validated['is_preview'] = $request->boolean('is_preview');

        if ($request->hasFile('video_file')) {
            $validated['video_path'] = $request->file('video_file')->store('courses/lessons/video', 'public');
        }
        if ($request->hasFile('sheet_music_file')) {
            $validated['sheet_music_path'] = $request->file('sheet_music_file')->store('courses/lessons/sheets', 'public');
        }
        if ($request->hasFile('audio_file')) {
            $validated['audio_path'] = $request->file('audio_file')->store('courses/lessons/audio', 'public');
        }

        $course->lessons()->create($validated);

        return redirect()->route('courses.show.manage', $course)->with('success', 'Lesson added successfully.');
    }

    public function edit(Course $course, Lesson $lesson)
    {
        $this->authorizeCourse($course);
        return view('lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:1'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['boolean'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'sheet_music_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'],
        ]);

        $validated['is_preview'] = $request->boolean('is_preview');

        if ($request->hasFile('video_file')) {
            if ($lesson->video_path) {
                Storage::disk('public')->delete($lesson->video_path);
            }
            $validated['video_path'] = $request->file('video_file')->store('courses/lessons/video', 'public');
        }
        if ($request->hasFile('sheet_music_file')) {
            if ($lesson->sheet_music_path) {
                Storage::disk('public')->delete($lesson->sheet_music_path);
            }
            $validated['sheet_music_path'] = $request->file('sheet_music_file')->store('courses/lessons/sheets', 'public');
        }
        if ($request->hasFile('audio_file')) {
            if ($lesson->audio_path) {
                Storage::disk('public')->delete($lesson->audio_path);
            }
            $validated['audio_path'] = $request->file('audio_file')->store('courses/lessons/audio', 'public');
        }

        $lesson->update($validated);

        return redirect()->route('courses.show.manage', $course)->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->authorizeCourse($course);

        if ($lesson->video_path) Storage::disk('public')->delete($lesson->video_path);
        if ($lesson->sheet_music_path) Storage::disk('public')->delete($lesson->sheet_music_path);
        if ($lesson->audio_path) Storage::disk('public')->delete($lesson->audio_path);

        $lesson->delete();

        return redirect()->route('courses.show.manage', $course)->with('success', 'Lesson deleted.');
    }
}
