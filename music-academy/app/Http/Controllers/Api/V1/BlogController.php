<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Blog\StoreCommentRequest;
use App\Http\Resources\V1\BlogCommentResource;
use App\Http\Resources\V1\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogController extends Controller
{
    /**
     * List all published blog posts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = BlogPost::where('is_published', true)
            ->with('author')
            ->withCount(['comments' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->latest('published_at')
            ->paginate($request->get('per_page', 9));

        return BlogPostResource::collection($posts);
    }

    /**
     * Show a single blog post with approved comments.
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'author',
                'comments' => function ($q) {
                    $q->where('status', 'approved')->with('user')->latest();
                }
            ])
            ->firstOrFail();

        return response()->json([
            'post' => new BlogPostResource($post),
        ]);
    }

    /**
     * Add a comment to a blog post.
     */
    public function storeComment(StoreCommentRequest $request, string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Comment submitted for approval.',
            'comment' => new BlogCommentResource($comment->load('user')),
        ], 201);
    }
}
