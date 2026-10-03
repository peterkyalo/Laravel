@extends('layouts.dashboard')

@section('title', $message->subject)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h3 class="font-serif text-white fw-bold mb-0">{{ $message->subject }}</h3>
                <small class="text-muted">Conversation between {{ $message->sender->name }} and {{ $message->recipient->name }}</small>
            </div>
        </div>

        <!-- Initial Message -->
        <div class="card card-solid p-4 mb-3 border-secondary">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $message->sender->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                    <div>
                        <strong class="text-white">{{ $message->sender->name }}</strong>
                        <span class="badge bg-surface-elevated text-gold small ms-1">{{ $message->sender->role }}</span>
                        <div class="text-muted small" style="font-size: 0.72rem;">To: {{ $message->recipient->name }}</div>
                    </div>
                </div>
                <span class="text-muted small">{{ $message->created_at->format('M j, Y · g:i A') }}</span>
            </div>

            <div class="text-light" style="line-height: 1.7;">
                {!! nl2br(e($message->body)) !!}
            </div>
        </div>

        <!-- Threaded Replies -->
        @foreach($message->replies as $reply)
            <div class="card card-glass p-4 mb-3 {{ $reply->sender_id === auth()->id() ? 'ms-md-5 border-gold' : 'me-md-5' }}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $reply->sender->avatarUrl() }}" alt="Avatar" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
                        <strong class="text-white small">{{ $reply->sender->name }}</strong>
                        <span class="badge bg-surface-elevated text-muted small" style="font-size: 0.65rem;">{{ $reply->sender->role }}</span>
                    </div>
                    <span class="text-muted small" style="font-size: 0.72rem;">{{ $reply->created_at->format('M j, g:i A') }}</span>
                </div>
                <div class="text-light small" style="line-height: 1.6;">
                    {!! nl2br(e($reply->body)) !!}
                </div>
            </div>
        @endforeach

        <!-- Reply Form -->
        <div class="card card-solid p-4 mt-4">
            <h5 class="text-white font-serif fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-reply-fill text-gold"></i> Send Reply
            </h5>

            <form action="{{ route('messages.reply', $message) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <textarea name="body" rows="4" class="form-control" placeholder="Write your response here..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-gold btn-sm px-4">
                        <i class="bi bi-send me-1"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
