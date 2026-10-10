<?php

namespace Tests\Feature\Api;

use App\Models\BlogPost;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instrument;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_courses_publicly(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $instrument = Instrument::create(['name' => 'Piano', 'slug' => 'piano']);

        Course::create([
            'instructor_id' => $instructor->id,
            'instrument_id' => $instrument->id,
            'title' => 'Mastering Piano Basics',
            'slug' => 'mastering-piano-basics',
            'level' => 'beginner',
            'price' => 100.00,
            'description' => 'A comprehensive beginner course.',
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/v1/courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'level', 'price', 'instructor', 'instrument']
                ],
                'links',
                'meta'
            ]);
    }

    public function test_user_can_register_and_receive_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'student',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'johndoe@example.com']);
    }

    public function test_user_can_login_and_access_me_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'student',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'Secret123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'user', 'token']);

        $token = $loginResponse->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('user.email', 'jane@example.com');
    }

    public function test_public_can_read_published_blogs(): void
    {
        $author = User::factory()->create(['role' => 'admin']);

        BlogPost::create([
            'author_id' => $author->id,
            'title' => 'Top 5 Tips for Vocalists',
            'slug' => 'top-5-tips-for-vocalists',
            'excerpt' => 'Vocal warmups and techniques.',
            'body' => 'Full article content here.',
            'category' => 'Masterclass & Technique',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/blogs');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Top 5 Tips for Vocalists');
    }

    public function test_unauthenticated_requests_receive_json_401(): void
    {
        $response = $this->getJson('/api/v1/student/courses');
        $response->assertStatus(401);
    }

    public function test_student_can_enroll_in_free_course_and_view_classroom(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $instrument = Instrument::create(['name' => 'Guitar', 'slug' => 'guitar']);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'instrument_id' => $instrument->id,
            'title' => 'Guitar Free Intro',
            'slug' => 'guitar-free-intro',
            'level' => 'beginner',
            'price' => 0.00,
            'is_free' => true,
            'description' => 'Free course for guitar enthusiasts.',
            'status' => 'published',
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'First Chords',
            'position' => 1,
            'is_preview' => false,
            'content' => 'Finger positioning for G, C, D.',
        ]);

        $this->actingAs($student, 'sanctum');

        $enrollResponse = $this->postJson("/api/v1/student/courses/{$course->slug}/enroll");
        $enrollResponse->assertStatus(201);

        $classroomResponse = $this->getJson("/api/v1/student/classroom/{$course->slug}");
        $classroomResponse->assertStatus(200)
            ->assertJsonPath('course.title', 'Guitar Free Intro');
    }

    public function test_instructor_can_create_course_lesson(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $instrument = Instrument::create(['name' => 'Violin', 'slug' => 'violin']);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'instrument_id' => $instrument->id,
            'title' => 'Violin Mastery',
            'slug' => 'violin-mastery',
            'level' => 'intermediate',
            'price' => 200.00,
            'status' => 'published',
        ]);

        $this->actingAs($instructor, 'sanctum');

        $response = $this->postJson("/api/v1/instructor/courses/{$course->slug}/lessons", [
            'title' => 'Bowing Technique Masterclass',
            'order' => 1,
            'is_free_preview' => true,
            'content' => 'Detailed posture and bow hold guides.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Lesson created successfully.');

        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Bowing Technique Masterclass',
        ]);
    }

    public function test_admin_can_manage_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Dr. Franz Liszt',
            'email' => 'liszt@conservatory.edu',
            'password' => 'Pianist1840!',
            'role' => 'instructor',
            'phone' => '+1234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.name', 'Dr. Franz Liszt');

        $this->assertDatabaseHas('users', ['email' => 'liszt@conservatory.edu']);
    }

    public function test_checkout_summary_and_mpesa_stk_push(): void
    {
        $student = User::factory()->create(['role' => 'student', 'phone' => '0712345678']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $instrument = Instrument::create(['name' => 'Cello', 'slug' => 'cello']);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'instrument_id' => $instrument->id,
            'title' => 'Cello Foundations',
            'slug' => 'cello-foundations',
            'level' => 'beginner',
            'fee' => 150.00,
            'status' => 'published',
        ]);

        $this->actingAs($student, 'sanctum');

        $summaryRes = $this->getJson("/api/v1/checkout/{$course->slug}/summary");
        $summaryRes->assertStatus(200)
            ->assertJsonPath('course_title', 'Cello Foundations');

        $stkRes = $this->postJson('/api/v1/checkout/mpesa/stk-push', [
            'course_id' => $course->id,
            'phone' => '0712345678',
        ]);

        $stkRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
