<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\SeoPolicy;
use App\Models\SeoLog;
use App\Models\Project;
use App\Models\Report;
use App\Policies\SeoLogPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReportPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SeoLog::class => SeoLogPolicy::class,
        Project::class => ProjectPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register SEO-related gates
        Gate::define('manage-seo', [SeoPolicy::class, 'manage']);
        Gate::define('view-seo-reports', [SeoPolicy::class, 'viewReports']);
        Gate::define('create-seo-logs', [SeoPolicy::class, 'createLogs']);
    }
} 