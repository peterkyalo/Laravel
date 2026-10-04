<?php

use App\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\InstrumentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/about', [CatalogController::class, 'about'])->name('about');
Route::get('/courses', [CatalogController::class, 'index'])->name('courses.public.index');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.public.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/blog/{blog:slug}/comments', [BlogCommentController::class, 'store'])->name('blog.comments.store')->middleware('auth');

Route::get('/verify-certificate', [CertificateController::class, 'verify'])->name('certificates.verify');
Route::get('/certificates/{certificate:code}', [CertificateController::class, 'show'])->name('certificates.show');

/*
|--------------------------------------------------------------------------
| Payment Webhooks (No Auth, No CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/payments/stripe/webhook', [\App\Http\Controllers\CheckoutController::class, 'stripeWebhook'])->name('payments.stripe.webhook');
Route::post('/payments/mpesa/callback/{token}', [\App\Http\Controllers\CheckoutController::class, 'mpesaCallback'])->name('payments.mpesa.callback');
Route::post('/payments/mpesa/c2b/validate/{token}', [\App\Http\Controllers\CheckoutController::class, 'mpesaC2bValidate'])->name('payments.mpesa.c2b.validate');
Route::post('/payments/mpesa/c2b/confirm/{token}', [\App\Http\Controllers\CheckoutController::class, 'mpesaC2bConfirm'])->name('payments.mpesa.c2b.confirm');

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
// Serve lesson files (forces correct MIME types for PDFs on local server)
Route::get('/serve-file', function (\Illuminate\Http\Request $request) {
    $path = $request->query('path');
    abort_unless($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path), 404);
    
    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
    
    if (\Illuminate\Support\Str::endsWith(strtolower($path), '.pdf')) {
        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
    
    // Explicitly set MIDI mime type if it's a MIDI file to avoid issues
    if (\Illuminate\Support\Str::endsWith(strtolower($path), ['.mid', '.midi'])) {
        return response()->file($fullPath, [
            'Content-Type' => 'audio/midi',
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
    
    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*'
    ]);
})->name('serve.file');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Rich Text Editor Media Upload
    Route::post('/media/upload', [\App\Http\Controllers\MediaUploadController::class, 'upload'])->name('media.upload');

    // Profile & Password
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Certificates
    Route::get('/my-certificates', [CertificateController::class, 'index'])->name('student.certificates');
    Route::get('/certificates/{certificate:code}/download', [CertificateController::class, 'download'])->name('certificates.download');

    // Enrollment & Learning Classroom
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('courses.enroll');
    Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('student.courses');

    Route::get('/classroom/{course:slug}', [LearningController::class, 'course'])->name('learning.course');
    Route::get('/classroom/{course:slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('learning.lesson');
    Route::post('/classroom/{course:slug}/lessons/{lesson}/toggle', [LearningController::class, 'toggleComplete'])->name('learning.lesson.toggle');
    


    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');

    // Quizzes (student take / view results)
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/quizzes/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/results/{attempt}', [QuizController::class, 'result'])->name('quizzes.result');

    // Schedule & Calendar
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/events', [ScheduleController::class, 'events'])->name('schedule.events');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/my-payments', [PaymentController::class, 'index'])->name('student.payments.index');
    Route::post('/payments/submit', [PaymentController::class, 'submit'])->name('payments.submit');
    
    // Checkout
    Route::get('/courses/{course:slug}/checkout', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/courses/{course:slug}/checkout/stripe', [\App\Http\Controllers\CheckoutController::class, 'stripe'])->name('checkout.stripe');
    Route::post('/courses/{course:slug}/checkout/paypal', [\App\Http\Controllers\CheckoutController::class, 'paypal'])->name('checkout.paypal');
    Route::post('/courses/{course:slug}/checkout/mpesa', [\App\Http\Controllers\CheckoutController::class, 'mpesa'])->name('checkout.mpesa');
    Route::post('/courses/{course:slug}/checkout/mpesa/c2b', [\App\Http\Controllers\CheckoutController::class, 'mpesaC2b'])->name('checkout.mpesa.c2b');
    Route::post('/courses/{course:slug}/checkout/cash', [\App\Http\Controllers\CheckoutController::class, 'cash'])->name('checkout.cash');

    Route::get('/checkout/{payment}/pending', [\App\Http\Controllers\CheckoutController::class, 'pending'])->name('checkout.pending');
    Route::get('/checkout/{payment}/status', [\App\Http\Controllers\CheckoutController::class, 'status'])->name('checkout.status');
    Route::get('/checkout/{payment}/cancel', [\App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/checkout/{payment}/simulate', [\App\Http\Controllers\CheckoutController::class, 'simulate'])->name('checkout.simulate');
    Route::post('/checkout/{payment}/simulate', [\App\Http\Controllers\CheckoutController::class, 'simulateComplete'])->name('checkout.simulate.complete');
    
    Route::get('/checkout/stripe/return', [\App\Http\Controllers\CheckoutController::class, 'stripeReturn'])->name('checkout.stripe.return');
    Route::get('/checkout/paypal/return', [\App\Http\Controllers\CheckoutController::class, 'paypalReturn'])->name('checkout.paypal.return');


    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/sent', [MessageController::class, 'sent'])->name('messages.sent');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');

    /*
    |--------------------------------------------------------------------------
    | Instructor & Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:instructor,admin')->group(function () {
        // Course Management
        Route::get('/manage/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/manage/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/manage/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/manage/courses/{course:slug}', [CourseController::class, 'manage'])->name('courses.show.manage');
        Route::get('/manage/courses/{course:slug}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/manage/courses/{course:slug}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/manage/courses/{course:slug}', [CourseController::class, 'destroy'])->name('courses.destroy');

        // Lesson Management
        Route::get('/manage/courses/{course:slug}/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/manage/courses/{course:slug}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/manage/courses/{course:slug}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/manage/courses/{course:slug}/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/manage/courses/{course:slug}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

        // Assignment Management & Grading
        Route::get('/manage/courses/{course:slug}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/manage/courses/{course:slug}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/manage/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::post('/manage/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('submissions.grade');

        // Quiz Management
        Route::get('/manage/courses/{course:slug}/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/manage/courses/{course:slug}/quizzes', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('/manage/quizzes/{quiz}/manage', [QuizController::class, 'manage'])->name('quizzes.manage');
        Route::post('/manage/quizzes/{quiz}/questions', [QuizController::class, 'addQuestion'])->name('quizzes.questions.add');
        Route::delete('/manage/quizzes/{quiz}/questions/{question}', [QuizController::class, 'deleteQuestion'])->name('quizzes.questions.delete');
        Route::post('/manage/quizzes/{quiz}/toggle-publish', [QuizController::class, 'togglePublish'])->name('quizzes.publish.toggle');
        Route::delete('/manage/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');

        // Class Session Scheduling
        Route::post('/manage/courses/{course:slug}/sessions', [ScheduleController::class, 'store'])->name('sessions.store');
        Route::delete('/manage/sessions/{session}', [ScheduleController::class, 'destroy'])->name('sessions.destroy');

        // Announcements
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::resource('users', UserController::class)->except(['show']);

        // Instruments
        Route::resource('instruments', InstrumentController::class)->except(['show', 'create', 'edit']);

        // Payments
        Route::post('payments/record', [PaymentController::class, 'record'])->name('payments.record');
        Route::post('payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

        // Site Settings & Dynamic Customization
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [SettingController::class, 'reset'])->name('settings.reset');

        // Academy Blog Publications & Comments Moderation
        Route::get('blogs/comments', [AdminBlogCommentController::class, 'index'])->name('blogs.comments.index');
        Route::post('blogs/comments/{comment}/approve', [AdminBlogCommentController::class, 'approve'])->name('blogs.comments.approve');
        Route::post('blogs/comments/{comment}/reject', [AdminBlogCommentController::class, 'reject'])->name('blogs.comments.reject');
        Route::delete('blogs/comments/{comment}', [AdminBlogCommentController::class, 'destroy'])->name('blogs.comments.destroy');
        Route::resource('blogs', AdminBlogController::class);
    });
});
