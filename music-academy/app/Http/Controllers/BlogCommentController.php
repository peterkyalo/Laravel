<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    /**
     * Store a new comment on a blog post.
     */
    public function store(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:3|max:1500',
        ]);

        $user = auth()->user();

        // Admins can be auto-approved, while students and visitors await review
        $status = $user->isAdmin() ? 'approved' : 'pending';

        $comment = BlogComment::create([
            'blog_post_id' => $blog->id,
            'user_id' => $user->id,
            'content' => trim($validated['content']),
            'status' => $status,
        ]);

        if ($status === 'approved') {
            return redirect()->to(url()->previous() . '#comments')
                ->with('success', 'Your comment has been posted successfully!');
        }

        return redirect()->to(url()->previous() . '#comments')
            ->with('info', 'Thank you! Your comment has been submitted and is awaiting administrator approval.');
    }
}
