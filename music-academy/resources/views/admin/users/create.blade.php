@extends('layouts.dashboard')

@section('title', 'Add Academy Member — Baritone')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary text-white border-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="font-serif text-white fw-bold mb-0">Add Academy Member</h2>
        </div>

        <div class="card card-solid p-4">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Niccolò Paganini">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="paganini@academy.test">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 555-0100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Musical Bio / Notes</label>
                    <textarea name="bio" rows="3" class="form-control" placeholder="Conservatory background, specialty instruments...">{{ old('bio') }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label text-white" for="is_active">
                        Account Active (Member can sign in immediately)
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <i class="bi bi-check-lg me-1"></i> Create Member
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
