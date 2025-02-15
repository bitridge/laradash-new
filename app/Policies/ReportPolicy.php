<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'seo_provider']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Report $report): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $report->project->customer->seoProviders()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'seo_provider']);
    }

    /**
     * Determine whether the user can create a report for a specific project.
     */
    public function createForProject(User $user, int $projectId): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            Project::where('id', $projectId)
                ->whereHas('customer', function ($query) use ($user) {
                    $query->whereHas('seoProviders', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                })->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $report->project->customer->seoProviders()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $report->project->customer->seoProviders()->where('users.id', $user->id)->exists();
    }
} 