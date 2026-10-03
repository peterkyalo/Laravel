<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of academy publications.
     */
    public function index(Request $request)
    {
        $posts = BlogPost::with('author')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = BlogPost::CATEGORIES;

        return view('admin.blogs.index', compact('posts', 'categories'));
    }

    /**
     * Show form for creating a new publication.
     */
    public function create()
    {
        $categories = BlogPost::CATEGORIES;
        return view('admin.blogs.create', compact('categories'));
    }

    /**
     * Store a newly created publication.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'read_time_minutes' => 'nullable|integer|min:1|max:60',
            'is_published' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $uploadDir = public_path('uploads/blogs');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $file = $request->file('cover_image');
            $filename = 'blog_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imagePath = 'uploads/blogs/' . $filename;
        }

        $post = BlogPost::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'tags' => $validated['tags'] ?? null,
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160),
            'body' => $validated['body'],
            'cover_image' => $imagePath,
            'author_id' => auth()->id(),
            'read_time_minutes' => $validated['read_time_minutes'] ?? max(1, (int) (str_word_count(strip_tags($validated['body'])) / 200)),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return redirect()->route('admin.blogs.index')->with('success', 'Academy article published successfully!');
    }

    /**
     * Show form for editing an existing publication.
     */
    public function edit(BlogPost $blog)
    {
        $categories = BlogPost::CATEGORIES;
        return view('admin.blogs.edit', compact('blog', 'categories'));
    }

    /**
     * Update an existing publication.
     */
    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'read_time_minutes' => 'nullable|integer|min:1|max:60',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $uploadDir = public_path('uploads/blogs');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $file = $request->file('cover_image');
            $filename = 'blog_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $blog->cover_image = 'uploads/blogs/' . $filename;
        } elseif ($request->boolean('remove_cover_image')) {
            $blog->cover_image = null;
        }

        $blog->title = $validated['title'];
        $blog->category = $validated['category'];
        $blog->tags = $validated['tags'] ?? null;
        $blog->excerpt = $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160);
        $blog->body = $validated['body'];
        $blog->read_time_minutes = $validated['read_time_minutes'] ?? max(1, (int) (str_word_count(strip_tags($validated['body'])) / 200));

        $wasPublished = $blog->is_published;
        $blog->is_published = $request->boolean('is_published');
        if (! $wasPublished && $blog->is_published && ! $blog->published_at) {
            $blog->published_at = now();
        }

        $blog->save();

        return redirect()->route('admin.blogs.index')->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified publication.
     */
    public function destroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Article deleted successfully.');
    }
}
