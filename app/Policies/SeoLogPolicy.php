<?php

namespace App\Policies;

use App\Models\SeoLog;
use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SeoLogPolicy
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
    public function view(User $user, SeoLog $seoLog): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $seoLog->project->seoProviders()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'seo_provider']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SeoLog $seoLog): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $seoLog->project->seoProviders()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SeoLog $seoLog): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            $seoLog->project->seoProviders()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can access a project.
     */
    public function accessProject(User $user, $projectId): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'seo_provider' && 
            Project::where('id', $projectId)
                ->whereHas('seoProviders', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->exists();
    }
} 