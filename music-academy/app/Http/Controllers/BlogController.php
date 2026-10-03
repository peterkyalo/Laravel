<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display public listing of blog articles and academy publications.
     */
    public function index(Request $request)
    {
        $query = BlogPost::published()->with('author')->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tag')) {
            $tag = trim($request->tag);
            $query->where('tags', 'like', "%{$tag}%");
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $featured = (clone $query)->first();

        $posts = $query->paginate(9)->withQueryString();

        $categories = BlogPost::CATEGORIES;

        // Fetch popular unique tags across published articles
        $popularTags = BlogPost::published()
            ->whereNotNull('tags')
            ->where('tags', '!=', '')
            ->pluck('tags')
            ->flatMap(fn ($t) => explode(',', $t))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(12)
            ->values();

        return view('public.blog.index', compact('posts', 'featured', 'categories', 'popularTags'));
    }

    /**
     * Display single blog post article.
     */
    public function show(BlogPost $blog)
    {
        abort_if(! $blog->is_published && ! (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isInstructor())), 404);

        $related = BlogPost::published()
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->take(3)
            ->get();

        if ($related->isEmpty()) {
            $related = BlogPost::published()->where('id', '!=', $blog->id)->take(3)->get();
        }

        // Comments loading based on viewer permissions
        $user = auth()->user();
        $commentsQuery = $blog->comments()->with('user')->latest();

        if ($user && $user->isAdmin()) {
            // Admins see all comments for moderation
            $comments = $commentsQuery->get();
        } elseif ($user) {
            // Logged-in users see approved comments + their own pending comments
            $comments = $commentsQuery->where(function ($q) use ($user) {
                $q->where('status', 'approved')
                  ->orWhere('user_id', $user->id);
            })->get();
        } else {
            // Guests only see approved comments
            $comments = $commentsQuery->where('status', 'approved')->get();
        }

        return view('public.blog.show', compact('blog', 'related', 'comments'));
    }
}
