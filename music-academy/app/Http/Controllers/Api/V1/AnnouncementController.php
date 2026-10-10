<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    /**
     * List announcements relevant to authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Announcement::query()->with(['author', 'course']);

        if (! $user->isAdmin()) {
            $enrolledCourseIds = $user->enrollments()->pluck('course_id');
            $query->where(function ($q) use ($user, $enrolledCourseIds) {
                $q->whereNull('target_role')
                  ->orWhere('target_role', $user->role)
                  ->orWhereIn('course_id', $enrolledCourseIds);
            });
        }

        $announcements = $query->latest()->paginate(15);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Create announcement (instructor or admin).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isInstructor()) {
            abort(403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'target_role' => ['nullable', 'in:student,instructor,admin'],
            'course_id' => ['nullable', 'exists:courses,id'],
        ]);

        $announcement = Announcement::create([
            'author_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
            'priority' => $request->priority ?? 'normal',
            'target_role' => $request->target_role,
            'course_id' => $request->course_id,
        ]);

        return response()->json([
            'message' => 'Announcement created successfully.',
            'announcement' => new AnnouncementResource($announcement->load(['author', 'course'])),
        ], 201);
    }

    /**
     * Delete announcement.
     */
    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && $announcement->author_id !== $user->id) {
            abort(403);
        }

        $announcement->delete();

        return response()->json([
            'message' => 'Announcement deleted successfully.',
        ]);
    }
}
