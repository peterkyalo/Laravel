@extends('layouts.dashboard')

@section('title', 'Edit Member: ' . $user->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="font-serif text-white fw-bold mb-0">Edit Academy Member</h2>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="instructor" {{ old('role', $user->role) == 'instructor' ? 'selected' : '' }}>Instructor</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reset Password (Optional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Musical Bio / Notes</label>
                    <textarea name="bio" rows="3" class="form-control">{{ old('bio', $user->bio) }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_active">
                        Account Active
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-save me-1"></i> Update Member
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
