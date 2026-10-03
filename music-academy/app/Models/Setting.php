<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Default application configuration values.
     */
    public static function defaults(): array
    {
        return [
            // Branding & Identity
            'site_name' => 'HARMONIA',
            'site_tagline' => 'World-Class Conservatory Online',
            'site_logo_type' => 'icon', // 'icon' or 'image'
            'site_logo_icon' => 'bi-music-note-beamed',
            'site_logo_image' => '',
            'site_favicon' => '',
            'meta_description' => 'Premier online music academy delivering masterclasses, instrument lessons, theory training, and verified certifications worldwide.',

            // Public Site Header Navigation Menu Titles & Links
            'nav_home_label' => 'Home',
            'nav_home_url' => '/',
            'nav_home_enabled' => '1',

            'nav_about_label' => 'About Us',
            'nav_about_url' => '/about',
            'nav_about_enabled' => '1',

            'nav_courses_label' => 'Courses',
            'nav_courses_url' => '/courses',
            'nav_courses_enabled' => '1',

            'nav_blog_label' => 'Journal',
            'nav_blog_url' => '/blog',
            'nav_blog_enabled' => '1',

            'nav_verify_label' => 'Verify Certificate',
            'nav_verify_url' => '/verify-certificate',
            'nav_verify_enabled' => '1',

            'nav_cta_label' => 'Join Academy',
            'nav_cta_url' => '/register',
            'nav_cta_enabled' => '1',

            'nav_login_label' => 'Log In',

            // Color Scheme & Theming
            'color_accent' => '#f59e0b',       // Venetian Gold
            'color_accent_hover' => '#d97706', // Deep Gold
            'color_primary' => '#4f46e5',      // Royal Indigo
            'color_bg_dark' => '#0a0e17',      // Midnight Obsidian
            'color_bg_surface' => '#111827',   // Surface Dark
            'color_bg_surface_elevated' => '#1f2937', // Elevated Card

            // Landing Page: Hero Section
            'hero_badge' => 'World-Class Conservatory Online',
            'hero_title' => 'Master Your Instrument with Virtuoso Instruction.',
            'hero_subtitle' => 'Study classical, jazz, and contemporary music through interactive video lessons, sheet music annotations, real-time practice feedback, and verified academy certifications.',
            'hero_cta_primary_text' => 'Explore Masterclasses',
            'hero_cta_primary_link' => '/courses',
            'hero_cta_secondary_text' => 'Apply for Enrollment',
            'hero_cta_secondary_link' => '/register',
            'hero_stats_mode' => 'auto', // 'auto' or 'custom'
            'hero_stat_students' => '450+',
            'hero_stat_courses' => '24',
            'hero_stat_faculty' => '18',
            'hero_stat_certificates' => '320+',
            'hero_image' => 'images/hero-conservatory.jpg',
            'hero_image_badge' => 'Live Academy Recitals & HD Scores',

            // Landing Page: Repertoire / Courses Section
            'featured_courses_subtitle' => 'Curated Repertoire',
            'featured_courses_title' => 'Featured Masterclasses',
            'featured_courses_desc' => 'Comprehensive curricula designed by conservatory concert soloists and master educators.',

            // Landing Page: Disciplines / Instruments
            'disciplines_subtitle' => 'Disciplines',
            'disciplines_title' => 'Browse by Instrument',

            // Landing Page: Conservatory Advantage / 4 Pillars
            'methodology_subtitle' => 'Methodology',
            'methodology_title' => 'The Conservatory Advantage',
            'methodology_desc' => 'Traditional academic rigor integrated seamlessly into a modern digital learning environment.',
            'pillar1_title' => 'Sheet Music Library',
            'pillar1_desc' => 'Every lesson includes annotated master scores, fingerings, and printable PDFs.',
            'pillar1_icon' => 'bi-file-earmark-pdf',
            'pillar2_title' => 'Practice Recordings',
            'pillar2_desc' => 'Record and upload your etudes for personalised audio feedback and grade rubrics from your instructor.',
            'pillar2_icon' => 'bi-mic',
            'pillar3_title' => 'Theory Assessments',
            'pillar3_desc' => 'Interactive quizzes test harmonic ear training, interval recognition, notation, and music history.',
            'pillar3_icon' => 'bi-question-circle',
            'pillar4_title' => 'Verified Credentials',
            'pillar4_desc' => 'Receive a unique, cryptographically verifiable certificate of completion upon mastery.',
            'pillar4_icon' => 'bi-award',

            // Landing Page: Faculty Section
            'faculty_subtitle' => 'World-Class Mentors',
            'faculty_title' => 'Meet Your Master Instructors',

            // Landing Page: Call to Action Banner
            'cta_banner_title' => 'Begin Your Audition Today',
            'cta_banner_desc' => 'Join hundreds of passionate musicians progressing through our structured conservatory syllabus.',
            'cta_banner_btn1_text' => 'Register as Student',
            'cta_banner_btn1_link' => '/register',
            'cta_banner_btn2_text' => 'View Course Catalog',
            'cta_banner_btn2_link' => '/courses',

            // Footer & Social
            'footer_about' => 'Premier online music academy delivering masterclasses, instrument lessons, theory training, and verified certifications worldwide.',
            'contact_address' => '440 Symphony Hall Way, Vienna & Online Worldwide',
            'contact_email' => 'admissions@harmonia-academy.test',
            'contact_phone' => '+1 (800) 427-6664',
            'social_youtube' => 'https://youtube.com',
            'social_instagram' => 'https://instagram.com',
            'social_spotify' => 'https://spotify.com',
            'social_discord' => 'https://discord.com',
            'footer_copyright' => 'Harmonia Music Academy. Built with Laravel 13, Bootstrap 5 & XAMPP MySQL. All rights reserved.',

            // About Us Page Customization
            'about_hero_badge' => 'Conservatory Heritage',
            'about_hero_title' => 'A Century of Virtuosity & Academic Distinction.',
            'about_hero_subtitle' => 'Harmonia blends European conservatory discipline with interactive digital scores, high-fidelity audio critique, and global recital masterclasses.',
            'about_stat_founded' => '1998',
            'about_stat_graduates' => '3,400+',
            'about_stat_masterclasses' => '120+',
            'about_stat_countries' => '42',
            'about_story_subtitle' => 'OUR HERITAGE & ORIGINS',
            'about_story_title' => 'Our Academy Heritage',
            'about_story_content' => '<p>Founded by distinguished concert soloists and conservatory educators, <strong>Harmonia Music Academy</strong> was conceived to transcend geographic boundaries, granting devoted students worldwide immediate access to premier musical mentoring.</p><p>Rooted in the timeless traditions of the Vienna and Paris Conservatories, our institution insists upon structural rigor, rhythmic clarity, and interpretive depth. Whether deciphering the architectural counterpoint of J.S. Bach or expanding your improvisational jazz vocabulary, each syllabus is calibrated to foster artistry through measured, disciplined devotion.</p>',
            'about_mission_title' => 'Artistic Mission & Pedagogical Vision',
            'about_mission_content' => '<p>Our mission is to nurture the next generation of expressive virtuosos through uncompromising academic standards, personalized audio rubric evaluation, and continuous artistic mentorship.</p><p>We believe every musician deserves unhindered access to authenticated masterscores, individualized performance diagnostics, and an international community of peers united in pursuit of sonic beauty.</p>',
            'about_dean_name' => 'Prof. Franz Liszt',
            'about_dean_title' => 'General Director & Dean of Faculty',
            'about_dean_quote' => 'Music is the divine medium that translates the unspoken longings of the soul into timeless resonance.',
            'about_dean_letter' => '<p>To our esteemed students and patrons of the musical arts:</p><p>Welcome to Harmonia. Here, the pursuit of mastery is not merely an educational goal—it is a sacred daily ritual. In our digital halls, you will find demanding faculty, uncompromising standards, and a profound respect for the score.</p><p>Approach your instrument each day with humility, patience, and unwavering curiosity. We look forward to listening to your progress.</p>',
            'about_val1_title' => 'Virtuoso Discipline',
            'about_val1_desc' => 'We champion deliberate practice: isolating technical friction points with slow tempos and metronomic accuracy.',
            'about_val1_icon' => 'bi-trophy-fill',
            'about_val2_title' => 'Urtext Fidelity',
            'about_val2_desc' => 'We study original manuscripts, composer annotations, and historical phrasing to honor the creator\'s intent.',
            'about_val2_icon' => 'bi-book-half',
            'about_val3_title' => 'Constructive Critique',
            'about_val3_desc' => 'Faculty provide precise audio evaluations with timestamped suggestions to correct posture, tone, and pacing.',
            'about_val3_icon' => 'bi-soundwave',
            'about_val4_title' => 'Verified Excellence',
            'about_val4_desc' => 'Every graduating diploma represents rigorous jury approval, complete repertoire mastery, and public verification.',
            'about_val4_icon' => 'bi-award-fill',

            // Student Portal Customization
            'student_portal_title' => 'Conservatory Virtual Studio',
            'student_welcome_sub' => 'Continue your musical journey where you left off. Practice makes virtuoso.',
            'student_announcement_enabled' => '1',
            'student_announcement_type' => 'info', // 'info', 'primary', 'warning', 'success'
            'student_announcement_title' => 'Masterclass Practice Notice',
            'student_announcement_text' => 'Spring Masterclass Auditions & Live Rehearsal Bookings are now open in the calendar schedule! Check upcoming rehearsals.',
            'student_practice_tip' => 'Virtuoso Tip: 30 minutes of slow metronome practice with focused phrasing yields 10x greater retention.',

            // Instructor Portal Customization
            'instructor_portal_title' => 'Faculty Studio Overview',
            'instructor_welcome_sub' => 'Manage your courses, submissions, and rehearsals.',
            'instructor_notice_enabled' => '1',
            'instructor_notice_type' => 'warning', // 'info', 'primary', 'warning', 'success'
            'instructor_notice_title' => 'Academy Faculty Directive',
            'instructor_notice_text' => 'Mid-term rubric grading deadline: Please evaluate student practice recordings and audio feedback by end of the week.',
            'instructor_guidelines' => 'All course materials should be uploaded in high-resolution audio (320kbps MP3) and vector PDF score formats.',
        ];
    }

    /**
     * Get a setting by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::getAll();
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        $defaults = static::defaults();
        if ($default !== null) {
            return $default;
        }

        return $defaults[$key] ?? null;
    }

    /**
     * Get all settings merged with defaults.
     */
    public static function getAll(): array
    {
        return Cache::rememberForever('harmonia_site_settings', function () {
            $dbSettings = static::pluck('value', 'key')->all();
            return array_merge(static::defaults(), $dbSettings);
        });
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget('harmonia_site_settings');
        return $setting;
    }

    /**
     * Set multiple settings.
     */
    public static function setMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        Cache::forget('harmonia_site_settings');
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('harmonia_site_settings');
    }

    /**
     * Reset all settings to defaults.
     */
    public static function resetToDefaults(): void
    {
        static::truncate();
        Cache::forget('harmonia_site_settings');
    }
}
