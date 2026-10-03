@extends('layouts.dashboard')

@section('title', 'Compose Message — Harmonia')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="font-serif text-white fw-bold mb-0">Compose Message</h2>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Recipient <span class="text-danger">*</span></label>
                    <select name="recipient_id" class="form-select" required>
                        <option value="">Select faculty or student...</option>
                        @foreach($recipients as $r)
                            <option value="{{ $r->id }}" {{ (string)$selectedRecipientId === (string)$r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ ucfirst($r->role) }} · {{ $r->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" required placeholder="e.g. Question regarding Chopin Scherzo fingering">
                </div>

                <div class="mb-4">
                    <label class="form-label">Message Body <span class="text-danger">*</span></label>
                    <textarea name="body" rows="6" class="form-control" required placeholder="Write your message here..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-send me-1"></i> Send Message
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
