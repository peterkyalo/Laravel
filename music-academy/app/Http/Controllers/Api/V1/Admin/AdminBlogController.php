<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BlogCommentResource;
use App\Http\Resources\V1\BlogPostResource;
use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    /**
     * List all blog posts (published and drafts).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = BlogPost::with('author')->withCount('comments')->latest()->paginate(15);
        return BlogPostResource::collection($posts);
    }

    /**
     * Store new blog post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['boolean'],
        ]);

        $validated['author_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['published_at'] = ! empty($validated['is_published']) ? now() : null;

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blogs/images', 'public');
        }

        $post = BlogPost::create($validated);

        return response()->json([
            'message' => 'Blog post created successfully.',
            'post' => new BlogPostResource($post->load('author')),
        ], 201);
    }

    /**
     * Update blog post.
     */
    public function update(Request $request, BlogPost $blog): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'is_published' => ['boolean'],
        ]);

        if (! empty($validated['is_published']) && ! $blog->published_at) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('blogs/images', 'public');
        }

        $blog->update($validated);

        return response()->json([
            'message' => 'Blog post updated successfully.',
            'post' => new BlogPostResource($blog->load('author')),
        ]);
    }

    /**
     * Delete blog post.
     */
    public function destroy(BlogPost $blog): JsonResponse
    {
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return response()->json([
            'message' => 'Blog post deleted successfully.',
        ]);
    }

    /**
     * List comments pending or for moderation.
     */
    public function comments(Request $request): AnonymousResourceCollection
    {
        $query = BlogComment::with(['user', 'post']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $comments = $query->latest()->paginate(20);

        return BlogCommentResource::collection($comments);
    }

    /**
     * Approve a comment.
     */
    public function approveComment(BlogComment $comment): JsonResponse
    {
        $comment->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Comment approved.',
            'comment' => new BlogCommentResource($comment),
        ]);
    }

    /**
     * Reject a comment.
     */
    public function rejectComment(BlogComment $comment): JsonResponse
    {
        $comment->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Comment rejected.',
            'comment' => new BlogCommentResource($comment),
        ]);
    }

    /**
     * Delete comment.
     */
    public function destroyComment(BlogComment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
