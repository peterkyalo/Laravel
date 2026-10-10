<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseResource;
use App\Http\Resources\V1\InstrumentResource;
use App\Models\Course;
use App\Models\Instrument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    /**
     * List all published courses with filtering and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Course::query()
            ->where('status', 'published')
            ->with(['instructor', 'instrument'])
            ->withCount(['lessons', 'enrollments']);

        if ($request->filled('instrument')) {
            $query->whereHas('instrument', function ($q) use ($request) {
                $q->where('slug', $request->instrument)->orWhere('id', $request->instrument);
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('pricing')) {
            if ($request->pricing === 'free') {
                $query->where('is_free', true);
            } elseif ($request->pricing === 'paid') {
                $query->where('is_free', false);
            }
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('tagline', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderByDesc('enrollments_count'),
            default => $query->latest(),
        };

        $courses = $query->paginate($request->get('per_page', 12));

        return CourseResource::collection($courses);
    }

    /**
     * Show single course details.
     */
    public function show(string $slug): JsonResponse
    {
        $course = Course::where('slug', $slug)
            ->where('status', 'published')
            ->with(['instructor', 'instrument', 'lessons' => function ($q) {
                $q->orderBy('order');
            }])
            ->withCount(['lessons', 'enrollments'])
            ->firstOrFail();

        return response()->json([
            'course' => new CourseResource($course),
        ]);
    }

    /**
     * List all available instruments.
     */
    public function instruments(): AnonymousResourceCollection
    {
        $instruments = Instrument::withCount(['courses' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        return InstrumentResource::collection($instruments);
    }
}
