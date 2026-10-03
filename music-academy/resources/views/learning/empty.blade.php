@extends('layouts.classroom')

@section('title', 'Empty Course — ' . $course->title)

@section('content')
<div class="container py-5 text-center my-auto">
    <div class="card card-solid p-5 mx-auto" style="max-width: 600px;">
        <i class="bi bi-hourglass-split text-gold display-3 mb-3"></i>
        <h3 class="font-serif text-white fw-bold mb-2">Lessons Coming Soon</h3>
        <p class="text-muted mb-4">
            Instructor <strong>{{ $course->instructor->name ?? 'Academy Faculty' }}</strong> is currently preparing and curating the video lessons and sheet music scores for <em>{{ $course->title }}</em>.
        </p>
        <div>
            <a href="{{ route('student.courses') }}" class="btn btn-gold">
                <i class="bi bi-arrow-left me-1"></i> Return to My Courses
            </a>
        </div>
    </div>
</div>
@endsection
