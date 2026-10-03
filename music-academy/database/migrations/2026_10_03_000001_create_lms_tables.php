<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create all music-academy LMS tables.
     */
    public function up(): void
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('music-note-beamed'); // Bootstrap Icons name
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('fee', 10, 2)->default(0);
            $table->unsignedSmallInteger('duration_weeks')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(1);
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();        // YouTube / Vimeo link
            $table->string('video_path')->nullable();       // uploaded MP4
            $table->string('sheet_music_path')->nullable(); // PDF
            $table->string('audio_path')->nullable();       // MP3 / WAV backing track
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lesson_id']);
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('instructions');
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['assignment_id', 'user_id']);
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('pass_mark')->default(60); // percent
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->unsignedInteger('position')->default(1);
            $table->unsignedSmallInteger('points')->default(1);
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0); // percent
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'card', 'mobile_money'])->default('cash');
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 32)->unique();
            $table->timestamp('issued_at');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('audience', ['all', 'students', 'instructors'])->default('all');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Drop all LMS tables.
     */
    public function down(): void
    {
        foreach ([
            'messages', 'announcements', 'certificates', 'payments', 'class_sessions',
            'quiz_attempts', 'question_options', 'questions', 'quizzes', 'submissions',
            'assignments', 'lesson_progress', 'enrollments', 'lessons', 'courses', 'instruments',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
