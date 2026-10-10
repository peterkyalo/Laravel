<?php

use App\Http\Controllers\Api\V1\Admin\AdminBlogController;
use App\Http\Controllers\Api\V1\Admin\AdminInstrumentController;
use App\Http\Controllers\Api\V1\Admin\AdminPaymentController;
use App\Http\Controllers\Api\V1\Admin\AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\Instructor\InstructorAssignmentController;
use App\Http\Controllers\Api\V1\Instructor\InstructorCourseController;
use App\Http\Controllers\Api\V1\Instructor\InstructorLessonController;
use App\Http\Controllers\Api\V1\Instructor\InstructorQuizController;
use App\Http\Controllers\Api\V1\Instructor\InstructorSessionController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Student\StudentAssignmentController;
use App\Http\Controllers\Api\V1\Student\StudentClassroomController;
use App\Http\Controllers\Api\V1\Student\StudentQuizController;
use App\Http\Controllers\Api\V1\Student\StudentScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Endpoints
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{slug}', [CourseController::class, 'show']);
    Route::get('instruments', [CourseController::class, 'instruments']);

    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{slug}', [BlogController::class, 'show']);

    Route::get('certificates/verify/{code}', [CertificateController::class, 'verify']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated Endpoints (Sanctum)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // User & Profile
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('profile', [ProfileController::class, 'update']);
            Route::post('password', [ProfileController::class, 'updatePassword']);
        });

        // Blog Comments
        Route::post('blogs/{slug}/comments', [BlogController::class, 'storeComment']);

        // Student Classroom, Learning & Quizzes
        Route::prefix('student')->group(function () {
            Route::get('courses', [StudentClassroomController::class, 'courses']);
            Route::post('courses/{course}/enroll', [StudentClassroomController::class, 'enroll']);
            Route::get('classroom/{slug}', [StudentClassroomController::class, 'show']);
            Route::get('classroom/{slug}/lessons/{lesson}', [StudentClassroomController::class, 'lesson']);
            Route::post('classroom/{slug}/lessons/{lesson}/toggle', [StudentClassroomController::class, 'toggleLessonComplete']);

            Route::get('assignments', [StudentAssignmentController::class, 'index']);
            Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show']);
            Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit']);

            Route::get('quizzes', [StudentQuizController::class, 'index']);
            Route::get('quizzes/{quiz}', [StudentQuizController::class, 'show']);
            Route::get('quizzes/{quiz}/take', [StudentQuizController::class, 'take']);
            Route::post('quizzes/{quiz}/submit', [StudentQuizController::class, 'submit']);
            Route::get('quizzes/{quiz}/results/{attempt}', [StudentQuizController::class, 'results']);

            Route::get('certificates', [CertificateController::class, 'index']);
            Route::get('schedule', [StudentScheduleController::class, 'index']);
        });

        // Payments & Multi-Gateway Checkout
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments/proof', [PaymentController::class, 'submitProof']);

        Route::prefix('checkout')->group(function () {
            Route::get('{course:slug}/summary', [CheckoutController::class, 'summary']);
            Route::post('mpesa/stk-push', [CheckoutController::class, 'mpesaStkPush']);
            Route::post('mpesa/query', [CheckoutController::class, 'mpesaQuery']);
            Route::post('stripe/create-intent', [CheckoutController::class, 'stripeCreateIntent']);
            Route::post('stripe/confirm', [CheckoutController::class, 'stripeConfirm']);
            Route::post('paypal/create-order', [CheckoutController::class, 'payPalCreateOrder']);
            Route::post('cash', [CheckoutController::class, 'cashCreate']);
            Route::get('{payment}/status', [CheckoutController::class, 'status']);
        });

        // Announcements
        Route::get('announcements', [AnnouncementController::class, 'index']);

        // Internal Messaging
        Route::get('messages', [MessageController::class, 'index']);
        Route::get('messages/sent', [MessageController::class, 'sent']);
        Route::post('messages', [MessageController::class, 'store']);
        Route::get('messages/{message}', [MessageController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | Instructor & Admin Management
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:instructor,admin')->prefix('instructor')->group(function () {
            Route::apiResource('courses', InstructorCourseController::class);

            Route::post('courses/{course}/lessons', [InstructorLessonController::class, 'store']);
            Route::put('courses/{course}/lessons/{lesson}', [InstructorLessonController::class, 'update']);
            Route::delete('courses/{course}/lessons/{lesson}', [InstructorLessonController::class, 'destroy']);

            Route::post('courses/{course}/assignments', [InstructorAssignmentController::class, 'store']);
            Route::delete('assignments/{assignment}', [InstructorAssignmentController::class, 'destroy']);
            Route::post('submissions/{submission}/grade', [InstructorAssignmentController::class, 'grade']);

            Route::post('courses/{course}/quizzes', [InstructorQuizController::class, 'store']);
            Route::post('quizzes/{quiz}/questions', [InstructorQuizController::class, 'addQuestion']);
            Route::delete('quizzes/{quiz}/questions/{question}', [InstructorQuizController::class, 'deleteQuestion']);
            Route::post('quizzes/{quiz}/toggle-publish', [InstructorQuizController::class, 'togglePublish']);
            Route::delete('quizzes/{quiz}', [InstructorQuizController::class, 'destroy']);

            Route::post('courses/{course}/sessions', [InstructorSessionController::class, 'store']);
            Route::delete('sessions/{session}', [InstructorSessionController::class, 'destroy']);

            Route::post('announcements', [AnnouncementController::class, 'store']);
            Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Admin Only Endpoints
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('users', AdminUserController::class);
            Route::apiResource('instruments', AdminInstrumentController::class)->except(['show']);

            Route::get('payments', [AdminPaymentController::class, 'index']);
            Route::post('payments/record', [AdminPaymentController::class, 'record']);
            Route::post('payments/{payment}/approve', [AdminPaymentController::class, 'approve']);
            Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject']);

            Route::get('settings', [AdminSettingController::class, 'index']);
            Route::post('settings', [AdminSettingController::class, 'update']);

            Route::get('blog-comments', [AdminBlogController::class, 'comments']);
            Route::post('blog-comments/{comment}/approve', [AdminBlogController::class, 'approveComment']);
            Route::post('blog-comments/{comment}/reject', [AdminBlogController::class, 'rejectComment']);
            Route::delete('blog-comments/{comment}', [AdminBlogController::class, 'destroyComment']);
            Route::apiResource('blogs', AdminBlogController::class)->except(['create', 'edit']);
        });
    });
});
