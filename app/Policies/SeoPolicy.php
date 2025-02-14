<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can manage SEO-related actions.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->isSeoProvider();
    }

    /**
     * Determine if the user can view SEO reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->isAdmin() || $user->isSeoProvider();
    }

    /**
     * Determine if the user can create SEO logs.
     */
    public function createLogs(User $user): bool
    {
        return $user->isAdmin() || $user->isSeoProvider();
    }
} 