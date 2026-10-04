@extends('layouts.app')

@section('title', 'Academy Masterclasses & Courses — Harmonia')

@section('content')
<div class="py-5" style="background: radial-gradient(circle at 50% 0%, rgba(79, 70, 229, 0.15) 0%, transparent 70%);">
    <div class="container">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="text-gold fw-bold text-uppercase small" style="letter-spacing: 0.08em;">Conservatory Curriculum</span>
                <h1 class="display-5 font-serif text-white fw-bold mb-2">Academy Course Catalog</h1>
                <p class="text-muted">Master your craft through systematic step-by-step performance modules.</p>
            </div>
            <div class="col-md-5 text-md-end">
                <span class="text-muted small">Showing {{ $courses->total() }} available {{ Str::plural('masterclass', $courses->total()) }}</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card card-glass p-3 mb-5">
            <form action="{{ route('courses.public.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search by title or topic..." value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <select name="instrument" class="form-select" onchange="this.form.submit()">
                        <option value="">All Instruments</option>
                        @foreach($instruments as $inst)
                            <option value="{{ $inst->slug }}" {{ request('instrument') == $inst->slug ? 'selected' : '' }}>
                                {{ $inst->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-2">
                    <select name="level" class="form-select" onchange="this.form.submit()">
                        <option value="">All Levels</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>
                                {{ ucfirst($lvl) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-6 col-md-2">
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Fee: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Fee: High to Low</option>
                    </select>
                </div>

                <div class="col-sm-6 col-md-1 d-grid">
                    <a href="{{ route('courses.public.index') }}" class="btn btn-outline-secondary text-white" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Course Cards Grid -->
        @if($courses->count() > 0)
            <div class="row g-4 mb-5">
                @foreach($courses as $course)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-glass h-100 overflow-hidden d-flex flex-column">
                            <div class="position-relative">
                                <img src="{{ $course->coverUrl() }}" 
                                     alt="{{ $course->title }}" 
                                     class="w-100" 
                                     style="height: 210px; object-fit: cover;"
                                     onerror="this.onerror=null;this.src='{{ asset('images/course-default.svg') }}';">
                                <span class="position-absolute top-0 start-0 m-3 badge {{ $course->levelBadgeClass() }} text-uppercase">
                                    {{ $course->level }}
                                </span>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 text-white border border-secondary">
                                    <i class="bi bi-{{ $course->instrument->icon }} text-gold me-1"></i> {{ $course->instrument->name }}
                                </span>
                            </div>

                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ $course->instructor->avatarUrl() }}" alt="{{ $course->instructor->name }}" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                                    <small class="text-muted">{{ $course->instructor->name }}</small>
                                </div>

                                <h5 class="font-serif fw-bold text-white mb-2">
                                    <a href="{{ route('courses.public.show', $course) }}" class="text-white text-decoration-none hover-gold">
                                        {{ $course->title }}
                                    </a>
                                </h5>

                                <p class="text-muted small mb-4 flex-grow-1">
                                    {{ Str::limit($course->short_description ?? $course->description, 110) }}
                                </p>

                                <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        <i class="bi bi-play-circle text-gold me-1"></i> {{ $course->lessons_count }} Lessons
                                        @if($course->duration_weeks)
                                            <span class="ms-2">· {{ $course->duration_weeks }} wks</span>
                                        @endif
                                    </div>
                                    <div class="fw-bold fs-5 text-gold">
                                        {{ $course->isFree() ? 'FREE' : 'KES ' . number_format($course->fee, 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-0">
                                <a href="{{ route('courses.public.show', $course) }}" class="btn btn-outline-gold w-100">
                                    Explore Curriculum
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $courses->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="card card-solid p-5 text-center my-4">
                <i class="bi bi-music-note-beamed text-gold display-4 mb-3"></i>
                <h4 class="text-white font-serif">No courses found matching your criteria</h4>
                <p class="text-muted mb-4">Try clearing some filters or searching for different instrument categories.</p>
                <div>
                    <a href="{{ route('courses.public.index') }}" class="btn btn-gold">
                        Reset Filters
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
