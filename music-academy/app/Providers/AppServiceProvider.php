<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        if (request()->header('X-Forwarded-Proto') === 'https' || str_contains(request()->header('host', ''), 'ngrok')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            try {
                $view->with('siteSettings', Setting::getAll());
            } catch (\Throwable $e) {
                $view->with('siteSettings', Setting::defaults());
            }
        });
    }
}
