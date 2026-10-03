<?php

namespace App\Http\Controllers;

use App\Models\Course;

abstract class Controller
{
    /**
     * Abort unless the current user can manage the course
     * (admins manage everything, instructors manage their own courses).
     */
    protected function authorizeCourse(Course $course): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->isAdmin() || $course->instructor_id === $user->id),
            403,
            'You can only manage your own courses.'
        );
    }

    /** Abort unless the current user can view the learning content of a course. */
    protected function authorizeLearning(Course $course): void
    {
        abort_unless(auth()->user()?->canAccessCourse($course), 403, 'Enroll in this course to access its content.');
    }
}
