@extends('layouts.dashboard')

@section('title', 'Moderate Blog Comments — Conservatory Administration — Baritone Music Academy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-gold text-dark fw-bold"><i class="bi bi-chat-square-quote me-1"></i> Community</span>
            <span class="text-gold fw-bold small text-uppercase" style="letter-spacing: 0.08em;">Moderation Studio</span>
        </div>
        <h2 class="font-serif text-white fw-bold mb-0">Blog Comments & Discussion Moderation</h2>
        <span class="text-muted small">Review, approve, or reject comments submitted by students, faculty, and conservatory visitors.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary text-white btn-sm">
            <i class="bi bi-journal-richtext me-1"></i> Publications
        </a>
        <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Public Blog
        </a>
    </div>
</div>

<div class="card card-solid p-4">
    <!-- Filter & Search Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.blogs.comments.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-gold' : 'btn-outline-secondary text-white border-secondary' }} rounded-pill px-3">
                All Comments ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.blogs.comments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }} rounded-pill px-3 position-relative">
                <i class="bi bi-hourglass-split me-1"></i> Pending Approval ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('admin.blogs.comments.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-success text-white fw-bold' : 'btn-outline-success' }} rounded-pill px-3">
                <i class="bi bi-check2-circle me-1"></i> Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('admin.blogs.comments.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger text-white fw-bold' : 'btn-outline-danger' }} rounded-pill px-3">
                <i class="bi bi-x-circle me-1"></i> Rejected ({{ $counts['rejected'] }})
            </a>
        </div>

        <form action="{{ route('admin.blogs.comments.index') }}" method="GET" class="d-flex gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control bg-surface text-white border-secondary" placeholder="Search commenter or comment text..." value="{{ request('q') }}">
                <button type="submit" class="btn btn-gold">Search</button>
            </div>
        </form>
    </div>

    @if($comments->count() > 0)
        <div class="table-responsive rounded-3 border border-secondary">
            <table class="table table-dark table-hover align-middle mb-0" style="background-color: var(--bg-surface);">
                <thead style="background-color: var(--bg-surface-elevated);" class="text-muted small">
                    <tr>
                        <th class="ps-3" style="width: 22%;">Commenter</th>
                        <th style="width: 25%;">Article</th>
                        <th style="width: 33%;">Comment Content</th>
                        <th style="width: 10%;">Status</th>
                        <th class="text-end pe-3" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                        <tr>
                            <!-- Commenter Info -->
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->name }}" class="rounded-circle border border-secondary" width="36" height="36" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-white small">{{ $comment->user->name }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $comment->user->email }}</small>
                                        <span class="badge bg-surface-elevated text-gold border border-secondary" style="font-size: 0.65rem;">
                                            {{ ucfirst($comment->user->role) }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Article Info -->
                            <td>
                                @if($comment->post)
                                    <div class="fw-semibold text-white small mb-1">
                                        <a href="{{ route('blog.show', $comment->post) }}" target="_blank" class="text-white text-decoration-none hover-gold">
                                            {{ Str::limit($comment->post->title, 45) }}
                                        </a>
                                    </div>
                                    <span class="badge bg-gold text-dark" style="font-size: 0.65rem;">{{ $comment->post->category }}</span>
                                @else
                                    <span class="text-muted small">[Deleted Article]</span>
                                @endif
                                <div class="text-muted mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-clock me-1"></i> {{ $comment->created_at->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Comment Content -->
                            <td>
                                <div class="p-2 rounded-2 bg-surface-elevated border border-secondary text-white small" style="line-height: 1.5; white-space: pre-wrap;">{{ $comment->content }}</div>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                @if($comment->isApproved())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Approved
                                    </span>
                                @elseif($comment->isPending())
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-hourglass-split me-1"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-x-circle me-1"></i> Rejected
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1">
                                    @if(!$comment->isApproved())
                                        <form action="{{ route('admin.blogs.comments.approve', $comment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve Comment">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!$comment->isRejected())
                                        <form action="{{ route('admin.blogs.comments.reject', $comment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject Comment">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.blogs.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Delete this comment permanently?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Comment">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $comments->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <div class="display-6 text-muted mb-3"><i class="bi bi-chat-square-text"></i></div>
            <h5 class="text-white fw-bold">No comments found</h5>
            <p class="text-muted small">No reader comments matching the selected filter criteria.</p>
            @if($status !== 'all' || request('q'))
                <a href="{{ route('admin.blogs.comments.index') }}" class="btn btn-outline-gold btn-sm">Clear Filters</a>
            @endif
        </div>
    @endif
</div>
@endsection
