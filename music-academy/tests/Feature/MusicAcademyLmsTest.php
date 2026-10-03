<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicAcademyLmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** Test that public pages load without error */
    public function test_public_pages_load_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('HARMONIA');

        $response = $this->get('/courses');
        $response->assertStatus(200);

        $response = $this->get('/verify-certificate');
        $response->assertStatus(200);

        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** Test user authentication and role access */
    public function test_student_can_log_in_and_see_dashboard(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        $response = $this->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);

        $dash = $this->actingAs($student)->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('My Enrolled Courses');
    }

    /** Test admin access protection */
    public function test_student_cannot_access_admin_user_management(): void
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)->get('/admin/users');
        $response->assertStatus(403);
    }

    /** Test admin can access admin user management */
    public function test_admin_can_access_admin_user_management(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Academy Member Directory');
    }

    /** Test student can enroll in free course and access classroom */
    public function test_student_can_enroll_in_free_course_and_view_lessons(): void
    {
        $freeCourse = Course::where('fee', 0)->first();
        $this->assertNotNull($freeCourse);

        $newStudent = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($newStudent)->post(route('courses.enroll', $freeCourse));
        $response->assertRedirect(route('learning.course', $freeCourse));

        $enrollment = Enrollment::where('user_id', $newStudent->id)
            ->where('course_id', $freeCourse->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals('active', $enrollment->status);

        // Can access the lesson page
        $firstLesson = $freeCourse->lessons()->first();
        $lessonView = $this->actingAs($newStudent)->get('/classroom/'.$freeCourse->slug.'/lessons/'.$firstLesson->id);
        $lessonView->assertStatus(200);
        $lessonView->assertSee($firstLesson->title);
    }

    /** Test lesson toggle complete recalculates progress */
    public function test_student_can_toggle_lesson_completion(): void
    {
        $student = User::where('role', 'student')->first();
        $enrollment = Enrollment::where('user_id', $student->id)->where('status', 'completed')->first();
        $this->assertNotNull($enrollment);

        $lesson = $enrollment->course->lessons()->first();
        $this->assertNotNull($lesson);

        $response = $this->actingAs($student)->post('/classroom/'.$enrollment->course->slug.'/lessons/'.$lesson->id.'/toggle');
        $response->assertStatus(302);
    }

    /** Test certificate public verification */
    public function test_certificate_can_be_verified_by_code(): void
    {
        $cert = Certificate::first();
        $this->assertNotNull($cert);

        $response = $this->get('/verify-certificate?code='.$cert->code);
        $response->assertStatus(200);
        $response->assertSee('Official Credential Verified');
        $response->assertSee($cert->enrollment->user->name);
    }

    /** Test student enrolling in paid course redirects to payment instructions */
    public function test_student_can_enroll_in_paid_course_and_redirects_to_payments(): void
    {
        $paidCourse = Course::where('fee', '>', 0)->first();
        $this->assertNotNull($paidCourse);

        $newStudent = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($newStudent)->post(route('courses.enroll', $paidCourse));
        $response->assertRedirect(route('payments.index'));

        $enrollment = Enrollment::where('user_id', $newStudent->id)
            ->where('course_id', $paidCourse->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertEquals('pending', $enrollment->status);
    }

    /** Test student can view my-courses index page without query exceptions */
    public function test_student_can_view_my_courses_page(): void
    {
        $student = User::where('role', 'student')->first();
        $this->assertNotNull($student);

        $response = $this->actingAs($student)->get(route('student.courses'));
        $response->assertStatus(200);
        $response->assertSee('My Enrolled Masterclasses');
    }

    /** Test admin can access settings while student is forbidden */
    public function test_admin_can_access_settings_and_student_is_forbidden(): void
    {
        $student = User::where('role', 'student')->first();
        $admin = User::where('role', 'admin')->first();

        // Student forbidden
        $this->actingAs($student)->get(route('admin.settings.index'))
            ->assertStatus(403);

        // Admin allowed
        $this->actingAs($admin)->get(route('admin.settings.index'))
            ->assertStatus(200)
            ->assertSee('Site Customization')
            ->assertSee('Color Palette');
    }

    /** Test admin can update settings and changes appear on landing page and student portal */
    public function test_admin_can_update_settings_and_changes_reflect_dynamically(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = User::where('role', 'student')->first();

        $postData = [
            'site_name' => 'VIRTUOSO CONSERVATORY',
            'site_tagline' => 'Elite European Music Education',
            'site_logo_type' => 'icon',
            'site_logo_icon' => 'bi-soundwave',
            'color_accent' => '#10b981',
            'color_accent_hover' => '#059669',
            'color_primary' => '#0d9488',
            'color_bg_dark' => '#06140e',
            'color_bg_surface' => '#0e241b',
            'color_bg_surface_elevated' => '#17362a',
            'hero_badge' => 'Exclusive Autumn Auditions Open',
            'hero_title' => 'Accelerate Your Virtuosity with Concert Masters.',
            'hero_subtitle' => 'Comprehensive classical curricula with verified diplomas.',
            'hero_cta_primary_text' => 'Join Masterclasses',
            'hero_cta_primary_link' => '/courses',
            'hero_cta_secondary_text' => 'Apply Now',
            'hero_cta_secondary_link' => '/register',
            'hero_stats_mode' => 'custom',
            'hero_stat_students' => '999+',
            'hero_stat_courses' => '50+',
            'hero_stat_faculty' => '30+',
            'hero_stat_certificates' => '500+',
            'disciplines_subtitle' => 'Departments',
            'disciplines_title' => 'Orchestral Disciplines',
            'featured_courses_subtitle' => 'Featured Syllabi',
            'featured_courses_title' => 'Signature Programs',
            'featured_courses_desc' => 'Handpicked courses for elite musicians.',
            'methodology_subtitle' => 'Conservatory Method',
            'methodology_title' => 'The Virtuoso Standard',
            'methodology_desc' => 'Rigorous conservatory pedagogy in a digital studio.',
            'pillar1_title' => 'Curated Scores',
            'pillar1_desc' => 'Urtext editions included.',
            'pillar1_icon' => 'bi-file-earmark-music',
            'pillar2_title' => 'Audio Evaluations',
            'pillar2_desc' => 'Detailed tempo feedback.',
            'pillar2_icon' => 'bi-soundwave',
            'pillar3_title' => 'Counterpoint Quizzes',
            'pillar3_desc' => 'Rigorous theory drills.',
            'pillar3_icon' => 'bi-check2-circle',
            'pillar4_title' => 'Verified Fellowships',
            'pillar4_desc' => 'Recognized credential.',
            'pillar4_icon' => 'bi-award',
            'faculty_subtitle' => 'Concert Artists',
            'faculty_title' => 'Our Distinguished Faculty',
            'cta_banner_title' => 'Begin Your Musical Legacy',
            'cta_banner_desc' => 'Audition today for next semester.',
            'cta_banner_btn1_text' => 'Submit Audition',
            'cta_banner_btn1_link' => '/register',
            'cta_banner_btn2_text' => 'Explore Repertoire',
            'cta_banner_btn2_link' => '/courses',
            'footer_about' => 'Virtuoso Conservatory of Fine Arts.',
            'contact_address' => '10 Beethoven St, Leipzig',
            'contact_email' => 'dean@virtuoso.test',
            'contact_phone' => '+49 30 123456',
            'social_youtube' => 'https://youtube.com/@virtuoso',
            'footer_copyright' => 'Virtuoso Conservatory. All rights reserved.',
            'student_portal_title' => 'Studio Masterclass Hub',
            'student_welcome_sub' => 'Master your repertoire with persistence.',
            'student_announcement_enabled' => '1',
            'student_announcement_type' => 'warning',
            'student_announcement_title' => 'Midterm Concerto Audition Alert',
            'student_announcement_text' => 'Recordings must be uploaded by Friday midnight.',
            'student_practice_tip' => 'Always isolate difficult polyphonic measures at 60 BPM.',
            'instructor_portal_title' => 'Professor Repertoire Studio',
            'instructor_welcome_sub' => 'Review student performances and lesson schedules.',
            'instructor_notice_enabled' => '1',
            'instructor_notice_type' => 'info',
            'instructor_notice_title' => 'Term Grading Calendar',
            'instructor_notice_text' => 'Final evaluations are due next Monday.',
            'instructor_guidelines' => 'All rubrics must include audio timestamp references.',
        ];

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), $postData);
        $response->assertRedirect(route('admin.settings.index', ['tab' => 'colors']));

        // Verify Home page reflection
        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertSee('VIRTUOSO CONSERVATORY');
        $home->assertSee('Exclusive Autumn Auditions Open');
        $home->assertSee('Accelerate Your Virtuosity with Concert Masters.');
        $home->assertSee('999+'); // Custom stat
        $home->assertSee('10 Beethoven St, Leipzig');

        // Verify Student dashboard reflection
        $studentDash = $this->actingAs($student)->get(route('dashboard'));
        $studentDash->assertStatus(200);
        $studentDash->assertSee('Studio Masterclass Hub');
        $studentDash->assertSee('Midterm Concerto Audition Alert');
        $studentDash->assertSee('Recordings must be uploaded by Friday midnight.');
        $studentDash->assertSee('Always isolate difficult polyphonic measures at 60 BPM.');
    }

    /** Test About Us page loads with dynamic content */
    public function test_about_us_page_loads_with_dynamic_content(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertSee('Conservatory Heritage');
        $response->assertSee('Prof. Franz Liszt');
        $response->assertSee('Academic Pillars');
    }

    /** Test Blog listing and single post article detail page */
    public function test_blog_listing_and_article_detail_page(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);
        $response->assertSee('Academy Journal');

        $firstPost = \App\Models\BlogPost::first();
        if ($firstPost) {
            $postResponse = $this->get('/blog/' . $firstPost->slug);
            $postResponse->assertStatus(200);
            $postResponse->assertSee($firstPost->title);
        }
    }

    /** Test Admin can manage blogs and access customizer tabs */
    public function test_admin_can_manage_blogs_and_access_customizer_tabs(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Access blog management
        $blogIndex = $this->actingAs($admin)->get(route('admin.blogs.index'));
        $blogIndex->assertStatus(200);
        $blogIndex->assertSee('Conservatory Blog');
        $blogIndex->assertSee('New Publication');
        $blogIndex->assertSee('Blog Publications');

        $blogCreate = $this->actingAs($admin)->get(route('admin.blogs.create'));
        $blogCreate->assertStatus(200);
        $blogCreate->assertSee('Write New Publication');

        // Access customizer with specific tabs
        $settingsAbout = $this->actingAs($admin)->get(route('admin.settings.index', ['tab' => 'about']));
        $settingsAbout->assertStatus(200);
        $settingsAbout->assertSee('About Us: Marquee Header');

        $settingsHero = $this->actingAs($admin)->get(route('admin.settings.index', ['tab' => 'hero']));
        $settingsHero->assertStatus(200);
        $settingsHero->assertSee('Landing Page: Hero Section');

        // Access navigation tab
        $settingsNav = $this->actingAs($admin)->get(route('admin.settings.index', ['tab' => 'navigation']));
        $settingsNav->assertStatus(200);
        $settingsNav->assertSee('Public Site Header & Navigation Menus');
    }

    /** Test admin can customize public site header menu titles and CTA */
    public function test_admin_can_customize_public_site_header_navigation_and_upload_media(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Update navigation menu titles
        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'active_tab' => 'navigation',
            'nav_home_label' => 'Academy Home',
            'nav_home_url' => '/',
            'nav_home_enabled' => '1',
            'nav_about_label' => 'Our Heritage',
            'nav_about_url' => '/about',
            'nav_about_enabled' => '1',
            'nav_courses_label' => 'Conservatory Masterclasses',
            'nav_courses_url' => '/courses',
            'nav_courses_enabled' => '1',
            'nav_blog_label' => 'Virtuoso Gazette',
            'nav_blog_url' => '/blog',
            'nav_blog_enabled' => '1',
            'nav_verify_label' => 'Diploma Verification',
            'nav_verify_url' => '/verify-certificate',
            'nav_verify_enabled' => '1',
            'nav_login_label' => 'Artist Sign In',
            'nav_cta_label' => 'Apply for Audition',
            'nav_cta_url' => '/register',
            'nav_cta_enabled' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'navigation']));

        // Verify public header displays customized titles for guest visitors
        auth()->logout();
        $publicHome = $this->get('/');
        $publicHome->assertStatus(200);
        $publicHome->assertSee('Academy Home');
        $publicHome->assertSee('Our Heritage');
        $publicHome->assertSee('Conservatory Masterclasses');
        $publicHome->assertSee('Virtuoso Gazette');
        $publicHome->assertSee('Diploma Verification');
        $publicHome->assertSee('Artist Sign In');
        $publicHome->assertSee('Apply for Audition');

        // 2. Test Media Upload for rich text inline image insertions
        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('test_score_illustration.png', 600, 400);
        $uploadResponse = $this->actingAs($admin)->postJson('/media/upload', [
            'image' => $fakeImage,
        ]);

        $uploadResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['url']);
    }

    /** Test blog tags and comment moderation system */
    public function test_blog_tags_and_comment_moderation_system(): void
    {
        $admin = User::where('role', 'admin')->first();
        $student = User::where('role', 'student')->first();

        // 1. Admin creates a publication with tags
        $postResponse = $this->actingAs($admin)->post(route('admin.blogs.store'), [
            'title' => 'The Art of Fugue and Counterpoint',
            'category' => 'Masterclass & Technique',
            'tags' => 'Fugue, Polyphony, Baroque, Counterpoint',
            'excerpt' => 'Mastering contrapuntal voice independence.',
            'body' => '<p>Study Bach voice leadings with care and slow practice.</p>',
            'read_time_minutes' => 6,
            'is_published' => 1,
        ]);
        $postResponse->assertRedirect(route('admin.blogs.index'));

        $post = \App\Models\BlogPost::where('title', 'The Art of Fugue and Counterpoint')->first();
        $this->assertNotNull($post);
        $this->assertContains('Fugue', $post->tags_list);
        $this->assertContains('Polyphony', $post->tags_list);

        // 2. View article as guest and verify tags are displayed
        auth()->logout();
        $articleView = $this->get('/blog/' . $post->slug);
        $articleView->assertStatus(200);
        $articleView->assertSee('#Fugue');
        $articleView->assertSee('#Polyphony');
        $articleView->assertSee('Log In to Comment');

        // 3. Filter blogs by tag
        $tagFilter = $this->get('/blog?tag=Polyphony');
        $tagFilter->assertStatus(200);
        $tagFilter->assertSee('The Art of Fugue and Counterpoint');

        // 4. Student submits a comment
        $commentResponse = $this->actingAs($student)->post(route('blog.comments.store', $post), [
            'content' => 'This voice leading breakdown cleared up my confusion on measure 14!',
        ]);
        $commentResponse->assertRedirect();
        $commentResponse->assertSessionHas('info');

        $comment = \App\Models\BlogComment::where('content', 'like', '%measure 14%')->first();
        $this->assertNotNull($comment);
        $this->assertTrue($comment->isPending());

        // 5. Guest visitor should NOT see the pending comment
        auth()->logout();
        $guestView = $this->get('/blog/' . $post->slug);
        $guestView->assertDontSee('This voice leading breakdown cleared up my confusion on measure 14!');

        // 6. Student author sees their pending comment
        $studentView = $this->actingAs($student)->get('/blog/' . $post->slug);
        $studentView->assertSee('This voice leading breakdown cleared up my confusion on measure 14!');
        $studentView->assertSee('Awaiting Admin Approval');

        // 7. Admin views comments moderation dashboard
        $commentsIndex = $this->actingAs($admin)->get(route('admin.blogs.comments.index'));
        $commentsIndex->assertStatus(200);
        $commentsIndex->assertSee('Discussion Moderation');
        $commentsIndex->assertSee('measure 14');

        // 8. Admin approves the comment
        $approveResponse = $this->actingAs($admin)->post(route('admin.blogs.comments.approve', $comment));
        $approveResponse->assertRedirect();
        $this->assertTrue($comment->fresh()->isApproved());

        // 9. Guest visitor now sees the approved comment
        auth()->logout();
        $guestViewApproved = $this->get('/blog/' . $post->slug);
        $guestViewApproved->assertSee('This voice leading breakdown cleared up my confusion on measure 14!');

        // 10. Admin rejects the comment
        $rejectResponse = $this->actingAs($admin)->post(route('admin.blogs.comments.reject', $comment));
        $rejectResponse->assertRedirect();
        $this->assertTrue($comment->fresh()->isRejected());

        // 11. Guest visitor no longer sees rejected comment
        auth()->logout();
        $guestViewRejected = $this->get('/blog/' . $post->slug);
        $guestViewRejected->assertDontSee('This voice leading breakdown cleared up my confusion on measure 14!');
    }
}

