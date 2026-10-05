@extends('layouts.dashboard')

@section('title', 'Academy Bulletins & Notices — Baritone')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Academy Bulletins & Notices</h2>
        <span class="text-muted small">Official masterclass notices, recital dates, guest lectures, and conservatory announcements.</span>
    </div>
</div>

<div class="row g-4">
    <!-- Post Announcement Form (Admin & Instructors) -->
    @if(auth()->user()->isAdmin() || auth()->user()->isInstructor())
        <div class="col-lg-4">
            <div class="card card-solid p-4 sticky-top" style="top: 90px;">
                <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-megaphone text-gold"></i> Post Announcement
                </h5>

                <form action="{{ route('announcements.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Bulletin Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Guest Soloist Masterclass this Friday">
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="mb-3">
                            <label class="form-label">Audience Scope <span class="text-danger">*</span></label>
                            <select name="audience" class="form-select form-select-sm" required>
                                <option value="all">Entire Academy (All Members)</option>
                                <option value="students">All Enrolled Students</option>
                                <option value="instructors">Faculty Only</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="audience" value="students">
                    @endif

                    @if($courses->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Target Specific Masterclass (Optional)</label>
                            <select name="course_id" class="form-select form-select-sm">
                                <option value="">General Academy Announcement</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Message Content <span class="text-danger">*</span></label>
                        <textarea name="body" rows="4" class="form-control form-control-sm" required placeholder="Write your announcement details here..."></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1">
                        <label class="form-check-label text-white small" for="is_pinned">
                            Pin to top of bulletins and dashboards
                        </label>
                    </div>

                    <button type="submit" class="btn btn-gold btn-sm w-100">
                        <i class="bi bi-send me-1"></i> Publish Bulletin
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Bulletins List -->
    <div class="{{ auth()->user()->isAdmin() || auth()->user()->isInstructor() ? 'col-lg-8' : 'col-12' }}">
        @forelse($announcements as $ann)
            <div class="card card-glass p-4 mb-3 {{ $ann->is_pinned ? 'border-gold' : '' }}">
                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        @if($ann->is_pinned)
                            <span class="badge bg-gold text-dark small"><i class="bi bi-pin-fill"></i> Pinned</span>
                        @endif
                        @if($ann->course)
                            <span class="badge bg-surface-elevated text-gold border border-secondary small">{{ $ann->course->title }}</span>
                        @else
                            <span class="badge bg-surface-elevated text-muted border border-secondary small text-capitalize">{{ $ann->audience }}</span>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin() || auth()->id() === $ann->user_id)
                        <form action="{{ route('announcements.destroy', $ann) }}" method="POST" onsubmit="return confirm('Delete this announcement?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger p-0 px-2" style="font-size: 0.75rem;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>

                <h4 class="font-serif text-white fw-bold mb-2">{{ $ann->title }}</h4>
                <div class="text-light mb-3" style="line-height: 1.7;">
                    {!! nl2br(e($ann->body)) !!}
                </div>

                <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center text-muted small">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $ann->author->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                        <span>{{ $ann->author->name }} (<span class="text-capitalize text-gold">{{ $ann->author->role }}</span>)</span>
                    </div>
                    <span>{{ $ann->created_at->format('M j, Y · g:i A') }}</span>
                </div>
            </div>
        @empty
            <div class="card card-solid p-5 text-center">
                <i class="bi bi-megaphone text-muted display-4 mb-3"></i>
                <h4 class="text-white font-serif">No Bulletins Currently Posted</h4>
                <p class="text-muted mb-0">Check back regularly for recital schedules and masterclass announcements.</p>
            </div>
        @endforelse

        <div class="mt-4 d-flex justify-content-center">
            {{ $announcements->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
