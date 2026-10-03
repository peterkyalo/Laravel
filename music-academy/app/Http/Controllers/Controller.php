<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $user = auth()->user();

        if ($user?->canAccessCourse($course)) {
            return;
        }

        // Enrolled but tuition not fully paid -> send them to checkout.
        if ($user && $user->enrollmentFor($course)) {
            throw new HttpResponseException(
                redirect()->route('checkout.show', $course)
                    ->with('info', 'Complete full payment of the tuition to unlock this course.')
            );
        }

        abort(403, 'Enroll in this course to access its content.');
    }
}
