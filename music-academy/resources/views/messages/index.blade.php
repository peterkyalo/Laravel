@extends('layouts.dashboard')

@section('title', 'Academy Inbox — Baritone Music Academy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Studio Communications</h2>
        <span class="text-muted small">Private correspondence between students, conservatory faculty, and academy bursar.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('messages.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Compose Message
        </a>
    </div>
</div>

<!-- Tabs: Inbox / Sent -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active bg-gold text-dark fw-semibold" href="{{ route('messages.index') }}">
            <i class="bi bi-inbox-fill me-1"></i> Inbox
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item ms-2">
        <a class="nav-link text-white bg-surface border border-secondary" href="{{ route('messages.sent') }}">
            <i class="bi bi-send-fill me-1"></i> Sent Messages
        </a>
    </li>
</ul>

<!-- Messages List -->
<div class="card card-solid p-3">
    <div class="list-group list-group-flush">
        @forelse($messages as $msg)
            <a href="{{ route('messages.show', $msg) }}" class="list-group-item bg-transparent text-white px-3 py-3 border-secondary d-flex align-items-center justify-content-between text-decoration-none hover-glass {{ $msg->isUnread() ? 'bg-surface-elevated border-start border-gold border-3' : '' }}">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $msg->sender->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="38" height="38" style="object-fit: cover;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <strong class="{{ $msg->isUnread() ? 'text-gold' : 'text-white' }} small">{{ $msg->sender->name }}</strong>
                            <span class="badge bg-surface-elevated text-muted small" style="font-size: 0.65rem;">{{ $msg->sender->role }}</span>
                            @if($msg->replies->count() > 0)
                                <span class="badge bg-primary-subtle text-primary small" style="font-size: 0.65rem;">
                                    <i class="bi bi-chat-dots-fill me-1"></i> {{ $msg->replies->count() }} {{ Str::plural('reply', $msg->replies->count()) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-light fw-semibold small mt-1">{{ $msg->subject }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 500px;">{{ Str::limit($msg->body, 80) }}</div>
                    </div>
                </div>

                <div class="text-end text-muted small">
                    <div>{{ $msg->created_at->diffForHumans() }}</div>
                    @if($msg->isUnread())
                        <span class="badge bg-danger small" style="font-size: 0.65rem;">New</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-5 text-muted small">
                <i class="bi bi-inbox display-4 d-block mb-2 text-muted"></i>
                Your academy inbox is currently empty.
            </div>
        @endforelse
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $messages->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
