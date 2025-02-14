<?php

namespace App\Providers;

use App\Models\SeoLog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('seo-logs.*', function ($view) {
            $view->with([
                'TYPES' => SeoLog::TYPES,
                'SeoLog' => SeoLog::class,
                'logTypes' => SeoLog::TYPES
            ]);
        });
    }
}
