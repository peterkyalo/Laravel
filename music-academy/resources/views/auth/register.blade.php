@extends('layouts.app')

@section('title', 'Student Registration — Harmonia Music Academy')

@section('content')
<div class="container py-5 my-md-3">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card card-glass p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="badge bg-gold p-3 rounded-circle mb-3"><i class="bi bi-music-player text-dark fs-3"></i></span>
                    <h2 class="font-serif text-white fw-bold">Begin Your Musical Journey</h2>
                    <p class="text-muted small">Create your student account to enroll in masterclasses and track your progress.</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-person"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus placeholder="Wolfgang Mozart">
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="name@domain.com">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5">
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-telephone"></i></span>
                                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+1 555-0199">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Min 8 characters">
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-shield-check"></i></span>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Repeat password">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-2 mb-3">
                        <i class="bi bi-check-lg me-1"></i> Complete Registration
                    </button>
                </form>

                <div class="text-center mt-3 text-muted small">
                    Already an academy student?
                    <a href="{{ route('login') }}" class="text-gold text-decoration-none fw-semibold">Sign in here</a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
