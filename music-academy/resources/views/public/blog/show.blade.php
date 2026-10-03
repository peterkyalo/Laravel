@extends('layouts.app')

@section('title', $blog->title . ' — ' . setting('site_name', 'Harmonia Music Academy'))

@section('content')

<article class="py-5" style="background-color: var(--bg-dark);">
    <div class="container" style="max-width: 860px;">

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-gold text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-gold text-decoration-none">Journal</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">{{ Str::limit($blog->title, 40) }}</li>
            </ol>
        </nav>

        <!-- Article Header -->
        <div class="mb-4 text-center text-md-start">
            <div class="d-inline-flex align-items-center gap-2 mb-3">
                <span class="badge bg-gold text-dark text-uppercase fw-bold">{{ $blog->category }}</span>
                <span class="text-muted small"><i class="bi bi-clock me-1 text-gold"></i> {{ $blog->read_time_minutes }} min read</span>
            </div>

            <h1 class="display-5 font-serif fw-bold text-white mb-3" style="line-height: 1.25;">
                {{ $blog->title }}
            </h1>

            @if($blog->excerpt)
                <p class="lead text-secondary mb-4" style="line-height: 1.6; font-size: 1.15rem;">
                    {{ $blog->excerpt }}
                </p>
            @endif

            <!-- Author & Metadata Bar -->
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 bg-surface border border-secondary flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $blog->author->avatarUrl() }}" alt="{{ $blog->author->name }}" class="rounded-circle border border-gold" width="46" height="46" style="object-fit: cover;">
                    <div>
                        <div class="fw-bold text-white">{{ $blog->author->name }}</div>
                        <small class="text-gold text-capitalize">{{ $blog->author->role === 'instructor' ? 'Conservatory Professor' : 'Academy Scholar' }}</small>
                    </div>
                </div>

                <div class="text-muted small">
                    <i class="bi bi-calendar-check me-1 text-gold"></i> Published:
                    <span class="text-secondary fw-semibold">
                        {{ $blog->published_at ? $blog->published_at->format('F d, Y') : $blog->created_at->format('F d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Featured Cover Artwork -->
        @if($blog->cover_image)
            <div class="mb-5 rounded-3 overflow-hidden shadow-lg border border-secondary">
                <img src="{{ $blog->coverUrl() }}" alt="{{ $blog->title }}" class="w-100" style="max-height: 480px; object-fit: cover;">
            </div>
        @endif

        <!-- Article Rich Content Body -->
        <div class="card card-solid p-4 p-md-5 mb-5 border-secondary">
            <div class="rich-content text-white" style="font-size: 1.05rem; line-height: 1.85;">
                {!! $blog->body !!}
            </div>
        </div>

        <!-- Tags Section -->
        @if(count($blog->tags_list) > 0)
            <div class="d-flex align-items-center gap-2 flex-wrap mb-4 p-3 rounded-3 bg-surface border border-secondary">
                <span class="text-gold small fw-bold"><i class="bi bi-tags-fill me-1"></i> Topics & Tags:</span>
                @foreach($blog->tags_list as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" class="badge bg-surface-elevated text-gold border border-secondary text-decoration-none py-2 px-3 hover-gold" style="font-size: 0.75rem;">
                        #{{ $tag }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Author Biography Card -->
        <div class="card card-glass p-4 mb-5 border border-secondary">
            <div class="d-flex align-items-start gap-4 flex-column flex-sm-row">
                <img src="{{ $blog->author->avatarUrl() }}" alt="{{ $blog->author->name }}" class="rounded-circle border border-2 border-gold shadow" width="70" height="70" style="object-fit: cover;">
                <div>
                    <h5 class="font-serif text-white fw-bold mb-1">About {{ $blog->author->name }}</h5>
                    <span class="text-gold small d-block mb-2">Senior Conservatory Faculty</span>
                    <p class="text-muted small mb-0" style="line-height: 1.6;">
                        {{ $blog->author->bio ?? 'Concert recitalist and academic pedagogue dedicated to classical performance and structural musical analysis.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Reader Comments & Discussion Section -->
        <section class="mb-5 pt-4 border-top border-secondary" id="comments">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="font-serif text-white fw-bold mb-0">
                        <i class="bi bi-chat-square-quote-fill text-gold me-2"></i> Conservatory Discussion
                    </h3>
                    <span class="badge bg-gold text-dark fw-bold rounded-pill ms-2">
                        {{ $blog->approvedComments()->count() }}
                    </span>
                </div>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.blogs.comments.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-shield-check me-1"></i> Comments Moderation Dashboard
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Comment Submission Form -->
            <div class="card card-solid p-4 mb-4 border-secondary">
                @auth
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="rounded-circle border border-gold" width="38" height="38" style="object-fit: cover;">
                        <div>
                            <span class="text-white fw-bold small d-block">{{ auth()->user()->name }}</span>
                            <span class="badge bg-surface-elevated text-gold border border-secondary" style="font-size: 0.65rem;">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('blog.comments.store', $blog) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="commentContent" class="form-label text-white small fw-bold">Leave your thought or analysis on this essay</label>
                            <textarea id="commentContent" name="content" rows="4" class="form-control bg-surface text-white border-secondary no-rich" placeholder="Share your interpretation, practice observations, or harmonic reflections..." required minlength="3" maxlength="1500">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <small class="text-muted">
                                <i class="bi bi-shield-lock text-gold me-1"></i>
                                @if(auth()->user()->isAdmin())
                                    <span class="text-success">Admin posting: your comment will be published immediately.</span>
                                @else
                                    Comments are reviewed and approved by faculty before public display.
                                @endif
                            </small>
                            <button type="submit" class="btn btn-gold btn-sm px-4 fw-bold">
                                <i class="bi bi-send-fill me-1"></i> Post Comment
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-chat-dots text-gold fs-2 mb-2 d-block"></i>
                        <h5 class="text-white fw-bold">Join the Conversation</h5>
                        <p class="text-muted small mx-auto mb-3" style="max-width: 500px;">
                            Sign in to share your musical interpretations, technical inquiries, or thoughts with our conservatory faculty and fellow students.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('login') }}" class="btn btn-gold btn-sm px-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Log In to Comment
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm border-secondary px-3">
                                Register
                            </a>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- List of Comments -->
            <div class="comments-list">
                @if(isset($comments) && $comments->count() > 0)
                    @foreach($comments as $comment)
                        <div class="card card-glass p-3 mb-3 border-secondary position-relative {{ $comment->isPending() ? 'border-warning' : ($comment->isRejected() ? 'border-danger' : '') }}">
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->name }}" class="rounded-circle border border-secondary" width="34" height="34" style="object-fit: cover;">
                                    <div>
                                        <span class="fw-bold text-white small">{{ $comment->user->name }}</span>
                                        <span class="badge bg-surface-elevated text-gold border border-secondary ms-1" style="font-size: 0.65rem;">
                                            {{ ucfirst($comment->user->role) }}
                                        </span>
                                        @if($comment->isPending())
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" style="font-size: 0.65rem;">
                                                <i class="bi bi-hourglass-split me-1"></i> Awaiting Admin Approval
                                            </span>
                                        @elseif($comment->isRejected())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">
                                                <i class="bi bi-x-circle me-1"></i> Rejected
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i> {{ $comment->created_at->diffForHumans() }}
                                </small>
                            </div>

                            <p class="text-white small mb-2 ps-1" style="line-height: 1.6; white-space: pre-wrap;">{{ $comment->content }}</p>

                            <!-- Inline Moderation Controls for Admins -->
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <div class="pt-2 border-top border-secondary d-flex justify-content-end align-items-center gap-2">
                                        <span class="text-muted small me-auto" style="font-size: 0.7rem;">
                                            <i class="bi bi-shield-check text-gold"></i> Admin Moderation:
                                        </span>
                                        @if(!$comment->isApproved())
                                            <form action="{{ route('admin.blogs.comments.approve', $comment) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.72rem;">
                                                    <i class="bi bi-check-lg me-1"></i> Approve
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$comment->isRejected())
                                            <form action="{{ route('admin.blogs.comments.reject', $comment) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2" style="font-size: 0.72rem;">
                                                    <i class="bi bi-slash-circle me-1"></i> Reject
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.blogs.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Permanently remove this comment?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.72rem;">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 p-3 rounded-3 bg-surface border border-secondary text-muted small">
                        <i class="bi bi-chat-heart text-gold fs-4 mb-2 d-block"></i>
                        No comments published yet. Be the first to share your thoughts on this masterclass publication!
                    </div>
                @endif
            </div>
        </section>

        <!-- Related Masterclass Articles -->
        @if($related->count() > 0)
            <div class="mt-5 pt-4 border-top border-secondary">
                <h4 class="font-serif text-white fw-bold mb-4"><i class="bi bi-journal-text text-gold me-2"></i> Further Conservatory Reading</h4>
                <div class="row g-3">
                    @foreach($related as $rel)
                        <div class="col-md-4">
                            <div class="card card-solid h-100 p-3">
                                <span class="badge bg-gold text-dark small w-auto mb-2 text-uppercase" style="font-size: 0.65rem;">{{ $rel->category }}</span>
                                <h6 class="font-serif fw-bold text-white mb-2">
                                    <a href="{{ route('blog.show', $rel) }}" class="text-white text-decoration-none hover-gold">
                                        {{ $rel->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-0">{{ Str::limit($rel->excerpt ?? strip_tags($rel->body), 80) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</article>

@endsection
