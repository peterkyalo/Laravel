<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (API Status & Documentation Reference)
|--------------------------------------------------------------------------
|
| Baritone Music Academy operates as a pure headless API backend.
| All application endpoints are hosted under /api/v1/*.
|
*/

Route::get('/', function () {
    return response()->json([
        'name' => 'Baritone Music Academy API',
        'version' => '1.0.0',
        'status' => 'online',
        'framework' => 'Laravel 13',
        'api_base_url' => url('/api/v1'),
        'endpoints' => [
            'auth' => url('/api/v1/auth'),
            'courses' => url('/api/v1/courses'),
            'instruments' => url('/api/v1/instruments'),
            'blogs' => url('/api/v1/blogs'),
            'student' => url('/api/v1/student'),
            'checkout' => url('/api/v1/checkout'),
            'instructor' => url('/api/v1/instructor'),
            'admin' => url('/api/v1/admin'),
        ],
    ]);
});
