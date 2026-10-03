<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    /**
     * Display the site customization dashboard.
     */
    public function index()
    {
        $settings = Setting::getAll();

        // Preset Themes for quick 1-click selection
        $presets = [
            'gold' => [
                'name' => 'Venetian Gold & Midnight (Default)',
                'accent' => '#f59e0b',
                'hover' => '#d97706',
                'primary' => '#4f46e5',
                'dark' => '#0a0e17',
                'surface' => '#111827',
                'elevated' => '#1f2937',
            ],
            'emerald' => [
                'name' => 'Imperial Emerald & Obsidian',
                'accent' => '#10b981',
                'hover' => '#059669',
                'primary' => '#0d9488',
                'dark' => '#06140e',
                'surface' => '#0e241b',
                'elevated' => '#17362a',
            ],
            'ruby' => [
                'name' => 'Virtuoso Crimson & Onyx',
                'accent' => '#ef4444',
                'hover' => '#dc2626',
                'primary' => '#e11d48',
                'dark' => '#14080a',
                'surface' => '#210f13',
                'elevated' => '#31171d',
            ],
            'amethyst' => [
                'name' => 'Royal Amethyst & Night',
                'accent' => '#c084fc',
                'hover' => '#a855f7',
                'primary' => '#7c3aed',
                'dark' => '#0f0a1c',
                'surface' => '#18122c',
                'elevated' => '#251c44',
            ],
            'sapphire' => [
                'name' => 'Electric Cyan & Deep Ocean',
                'accent' => '#06b6d4',
                'hover' => '#0891b2',
                'primary' => '#2563eb',
                'dark' => '#06101e',
                'surface' => '#0b1c34',
                'elevated' => '#122a4d',
            ],
        ];

        return view('admin.settings.index', compact('settings', 'presets'));
    }

    /**
     * Update academy site configuration and branding.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Branding
            'site_name' => 'sometimes|required|string|max:60',
            'site_tagline' => 'nullable|string|max:120',
            'site_logo_type' => 'sometimes|required|in:icon,image',
            'site_logo_icon' => 'nullable|string|max:60',
            'site_logo_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'site_favicon_file' => 'nullable|mimes:jpeg,png,jpg,gif,svg,ico,webp|max:1024',
            'meta_description' => 'nullable|string|max:300',

            // Public Site Header Navigation Menu
            'nav_home_label' => 'nullable|string|max:60',
            'nav_home_url' => 'nullable|string|max:200',
            'nav_about_label' => 'nullable|string|max:60',
            'nav_about_url' => 'nullable|string|max:200',
            'nav_courses_label' => 'nullable|string|max:60',
            'nav_courses_url' => 'nullable|string|max:200',
            'nav_blog_label' => 'nullable|string|max:60',
            'nav_blog_url' => 'nullable|string|max:200',
            'nav_verify_label' => 'nullable|string|max:60',
            'nav_verify_url' => 'nullable|string|max:200',
            'nav_cta_label' => 'nullable|string|max:60',
            'nav_cta_url' => 'nullable|string|max:200',
            'nav_login_label' => 'nullable|string|max:60',

            // Colors
            'color_accent' => ['sometimes', 'required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'color_accent_hover' => ['nullable', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'color_primary' => ['sometimes', 'required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'color_bg_dark' => ['sometimes', 'required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'color_bg_surface' => ['sometimes', 'required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'color_bg_surface_elevated' => ['nullable', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            // Hero Section
            'hero_badge' => 'nullable|string|max:100',
            'hero_title' => 'sometimes|required|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_image' => 'nullable|string|max:255',
            'hero_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'hero_image_badge' => 'nullable|string|max:100',
            'hero_cta_primary_text' => 'nullable|string|max:60',
            'hero_cta_primary_link' => 'nullable|string|max:200',
            'hero_cta_secondary_text' => 'nullable|string|max:60',
            'hero_cta_secondary_link' => 'nullable|string|max:200',
            'hero_stats_mode' => 'sometimes|required|in:auto,custom',
            'hero_stat_students' => 'nullable|string|max:30',
            'hero_stat_courses' => 'nullable|string|max:30',
            'hero_stat_faculty' => 'nullable|string|max:30',
            'hero_stat_certificates' => 'nullable|string|max:30',

            // Landing Page Content Sections
            'disciplines_subtitle' => 'nullable|string|max:60',
            'disciplines_title' => 'nullable|string|max:100',
            'featured_courses_subtitle' => 'nullable|string|max:60',
            'featured_courses_title' => 'nullable|string|max:100',
            'featured_courses_desc' => 'nullable|string|max:300',

            'methodology_subtitle' => 'nullable|string|max:60',
            'methodology_title' => 'nullable|string|max:100',
            'methodology_desc' => 'nullable|string|max:400',
            'pillar1_title' => 'nullable|string|max:100',
            'pillar1_desc' => 'nullable|string|max:300',
            'pillar1_icon' => 'nullable|string|max:60',
            'pillar2_title' => 'nullable|string|max:100',
            'pillar2_desc' => 'nullable|string|max:300',
            'pillar2_icon' => 'nullable|string|max:60',
            'pillar3_title' => 'nullable|string|max:100',
            'pillar3_desc' => 'nullable|string|max:300',
            'pillar3_icon' => 'nullable|string|max:60',
            'pillar4_title' => 'nullable|string|max:100',
            'pillar4_desc' => 'nullable|string|max:300',
            'pillar4_icon' => 'nullable|string|max:60',

            'faculty_subtitle' => 'nullable|string|max:60',
            'faculty_title' => 'nullable|string|max:100',

            'cta_banner_title' => 'nullable|string|max:150',
            'cta_banner_desc' => 'nullable|string|max:400',
            'cta_banner_btn1_text' => 'nullable|string|max:60',
            'cta_banner_btn1_link' => 'nullable|string|max:200',
            'cta_banner_btn2_text' => 'nullable|string|max:60',
            'cta_banner_btn2_link' => 'nullable|string|max:200',

            // Footer & Social
            'footer_about' => 'nullable|string|max:500',
            'contact_address' => 'nullable|string|max:200',
            'contact_email' => 'nullable|email|max:120',
            'contact_phone' => 'nullable|string|max:60',
            'social_youtube' => 'nullable|string|max:200',
            'social_instagram' => 'nullable|string|max:200',
            'social_spotify' => 'nullable|string|max:200',
            'social_discord' => 'nullable|string|max:200',
            'footer_copyright' => 'nullable|string|max:250',

            // Student Portal
            'student_portal_title' => 'nullable|string|max:100',
            'student_welcome_sub' => 'nullable|string|max:300',
            'student_announcement_enabled' => 'nullable|in:0,1',
            'student_announcement_type' => 'nullable|in:info,warning,primary,success',
            'student_announcement_title' => 'nullable|string|max:100',
            'student_announcement_text' => 'nullable|string|max:500',
            'student_practice_tip' => 'nullable|string|max:400',

            // Instructor Portal
            'instructor_portal_title' => 'nullable|string|max:100',
            'instructor_welcome_sub' => 'nullable|string|max:300',
            'instructor_notice_enabled' => 'nullable|in:0,1',
            'instructor_notice_type' => 'nullable|in:info,warning,primary,success',
            'instructor_notice_title' => 'nullable|string|max:100',
            // About Us Page
            'about_hero_badge' => 'nullable|string|max:100',
            'about_hero_title' => 'nullable|string|max:200',
            'about_hero_subtitle' => 'nullable|string|max:500',
            'about_story_title' => 'nullable|string|max:150',
            'about_story_content' => 'nullable|string',
            'about_mission_title' => 'nullable|string|max:150',
            'about_mission_content' => 'nullable|string',
            'about_dean_name' => 'nullable|string|max:100',
            'about_dean_title' => 'nullable|string|max:120',
            'about_dean_quote' => 'nullable|string|max:400',
            'about_dean_letter' => 'nullable|string',
            'about_val1_title' => 'nullable|string|max:100',
            'about_val1_desc' => 'nullable|string|max:300',
            'about_val1_icon' => 'nullable|string|max:60',
            'about_val2_title' => 'nullable|string|max:100',
            'about_val2_desc' => 'nullable|string|max:300',
            'about_val2_icon' => 'nullable|string|max:60',
            'about_val3_title' => 'nullable|string|max:100',
            'about_val3_desc' => 'nullable|string|max:300',
            'about_val3_icon' => 'nullable|string|max:60',
            'about_val4_title' => 'nullable|string|max:100',
            'about_val4_desc' => 'nullable|string|max:300',
            'about_val4_icon' => 'nullable|string|max:60',
        ]);

        // Process File Uploads
        $uploadDir = public_path('uploads/branding');
        if (! File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('site_logo_image_file')) {
            $file = $request->file('site_logo_image_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            Setting::set('site_logo_image', 'uploads/branding/' . $filename, 'branding');
        } elseif ($request->boolean('remove_logo_image')) {
            Setting::set('site_logo_image', '', 'branding');
        }

        if ($request->hasFile('site_favicon_file')) {
            $file = $request->file('site_favicon_file');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            Setting::set('site_favicon', 'uploads/branding/' . $filename, 'branding');
        } elseif ($request->boolean('remove_favicon')) {
            Setting::set('site_favicon', '', 'branding');
        }

        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $filename = 'hero_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            Setting::set('hero_image', 'uploads/branding/' . $filename, 'hero');
            unset($validated['hero_image']);
        } elseif ($request->boolean('reset_hero_image')) {
            Setting::set('hero_image', 'images/hero-conservatory.jpg', 'hero');
            unset($validated['hero_image']);
        }

        $activeTab = $request->input('active_tab', 'colors');

        // Process navigation checkboxes
        if ($activeTab === 'navigation' || $request->has('nav_home_label')) {
            $validated['nav_home_enabled'] = $request->has('nav_home_enabled') ? '1' : '0';
            $validated['nav_about_enabled'] = $request->has('nav_about_enabled') ? '1' : '0';
            $validated['nav_courses_enabled'] = $request->has('nav_courses_enabled') ? '1' : '0';
            $validated['nav_blog_enabled'] = $request->has('nav_blog_enabled') ? '1' : '0';
            $validated['nav_verify_enabled'] = $request->has('nav_verify_enabled') ? '1' : '0';
            $validated['nav_cta_enabled'] = $request->has('nav_cta_enabled') ? '1' : '0';
        }

        // Process portal checkboxes
        if ($activeTab === 'student' || $request->has('student_portal_title')) {
            $validated['student_announcement_enabled'] = $request->has('student_announcement_enabled') ? '1' : '0';
        }
        if ($activeTab === 'instructor' || $request->has('instructor_portal_title')) {
            $validated['instructor_notice_enabled'] = $request->has('instructor_notice_enabled') ? '1' : '0';
        }

        // Auto-generate hover color if not supplied
        if (isset($validated['color_accent']) && empty($validated['color_accent_hover'])) {
            $validated['color_accent_hover'] = $validated['color_accent'];
        }
        if (isset($validated['color_bg_surface']) && empty($validated['color_bg_surface_elevated'])) {
            $validated['color_bg_surface_elevated'] = $validated['color_bg_surface'];
        }

        // Exclude file object keys from direct string saving
        unset($validated['site_logo_image_file'], $validated['site_favicon_file'], $validated['hero_image_file'], $validated['reset_hero_image']);

        // Persist all settings
        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        Setting::clearCache();

        return redirect()->route('admin.settings.index', ['tab' => $request->input('active_tab', 'colors')])
            ->with('success', 'Academy customization and live dynamic styling saved successfully!');
    }

    /**
     * Reset all settings to conservatory factory defaults.
     */
    public function reset()
    {
        Setting::resetToDefaults();

        return redirect()->route('admin.settings.index')
            ->with('info', 'All academy settings, hero copy, and color themes have been reset to factory defaults.');
    }
}
