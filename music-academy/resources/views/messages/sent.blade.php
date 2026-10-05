@extends('layouts.dashboard')

@section('title', 'Sent Messages — Baritone Music Academy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Sent Messages</h2>
        <span class="text-muted small">Dispatched inquiries, practice advice, and academy communications.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('messages.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Compose Message
        </a>
    </div>
</div>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link text-white bg-surface border border-secondary" href="{{ route('messages.index') }}">
            <i class="bi bi-inbox-fill me-1"></i> Inbox
        </a>
    </li>
    <li class="nav-item ms-2">
        <a class="nav-link active bg-gold text-dark fw-semibold" href="{{ route('messages.sent') }}">
            <i class="bi bi-send-fill me-1"></i> Sent Messages
        </a>
    </li>
</ul>

<div class="card card-solid p-3">
    <div class="list-group list-group-flush">
        @forelse($messages as $msg)
            <a href="{{ route('messages.show', $msg) }}" class="list-group-item bg-transparent text-white px-3 py-3 border-secondary d-flex align-items-center justify-content-between text-decoration-none hover-glass">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $msg->recipient->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="38" height="38" style="object-fit: cover;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">To:</span>
                            <strong class="text-white small">{{ $msg->recipient->name }}</strong>
                            <span class="badge bg-surface-elevated text-gold small" style="font-size: 0.65rem;">{{ $msg->recipient->role }}</span>
                        </div>
                        <div class="text-light fw-semibold small mt-1">{{ $msg->subject }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 500px;">{{ Str::limit($msg->body, 80) }}</div>
                    </div>
                </div>

                <div class="text-end text-muted small">
                    {{ $msg->created_at->format('M j, Y') }}
                </div>
            </a>
        @empty
            <div class="text-center py-5 text-muted small">
                No sent messages.
            </div>
        @endforelse
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $messages->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
