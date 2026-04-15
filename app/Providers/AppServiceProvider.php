<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Enrollment::observe(EnrollmentObserver::class);

        // Add CORS headers to storage responses
        $this->configureStorageCors();
    }

    /**
     * Configure CORS headers for storage files
     * Allows frontend to load images from storage without CORS issues
     */
    private function configureStorageCors(): void
    {
        // Use middleware to add headers to storage responses
        if (app()->environment(['local', 'production'])) {
            Response::macro('withCorsHeaders', function () {
                return response()
                    ->header('Cross-Origin-Resource-Policy', 'cross-origin')
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type');
            });
        }
    }
}
