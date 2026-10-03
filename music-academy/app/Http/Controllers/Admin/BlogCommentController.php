<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    /**
     * Display a listing of blog comments for moderation.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = BlogComment::with(['user', 'post'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('post', fn ($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $comments = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => BlogComment::count(),
            'pending' => BlogComment::pending()->count(),
            'approved' => BlogComment::approved()->count(),
            'rejected' => BlogComment::rejected()->count(),
        ];

        return view('admin.blogs.comments', compact('comments', 'counts', 'status'));
    }

    /**
     * Approve a blog comment.
     */
    public function approve(BlogComment $comment)
    {
        $comment->approve();

        return redirect()->back()->with('success', 'Comment approved successfully and is now visible on the public journal article!');
    }

    /**
     * Reject a blog comment.
     */
    public function reject(BlogComment $comment)
    {
        $comment->reject();

        return redirect()->back()->with('info', 'Comment has been marked as rejected and hidden from public display.');
    }

    /**
     * Remove the comment permanently.
     */
    public function destroy(BlogComment $comment)
    {
        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted permanently.');
    }
}
