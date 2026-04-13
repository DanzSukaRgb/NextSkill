<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AI\AiServiceInterface;
use App\Services\AI\GroqService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiServiceInterface::class, GroqService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
