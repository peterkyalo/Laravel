@extends('layouts.dashboard')

@section('title', 'My Profile & Security')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <h2 class="font-serif text-white fw-bold mb-1">Account & Settings</h2>
        <p class="text-muted small">Update your academy profile details, avatar, and password credentials.</p>
    </div>

    <!-- Profile Details Card -->
    <div class="col-lg-7">
        <div class="card card-solid p-4">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-gold"></i> Profile Details
            </h5>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $user->avatarUrl() }}" alt="Avatar" class="rounded-circle border border-gold" width="70" height="70" style="object-fit: cover;">
                    <div class="flex-grow-1">
                        <label class="form-label small">Change Profile Photo</label>
                        <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">JPG or PNG, max 2MB.</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1 555-0100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Academy Role</label>
                        <input type="text" class="form-control text-capitalize" value="{{ $user->role }}" disabled readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Biography / Musical Background</label>
                    <textarea name="bio" rows="4" class="form-control" placeholder="Tell other academy members about your musical interests, experience, or preferred repertoire...">{{ old('bio', $user->bio) }}</textarea>
                </div>

                <button type="submit" class="btn btn-gold">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Password Card -->
    <div class="col-lg-5">
        <div class="card card-solid p-4">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-gold"></i> Change Password
            </h5>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat new password">
                </div>

                <button type="submit" class="btn btn-outline-gold w-100">
                    <i class="bi bi-key me-1"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
