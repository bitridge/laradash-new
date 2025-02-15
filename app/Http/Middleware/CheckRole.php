<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;
        
        // Treat seo_provider as admin
        if ($userRole === 'seo_provider') {
            $userRole = 'admin';
        }

        // Get all allowed roles by splitting any comma-separated values
        $allowedRoles = collect($roles)
            ->flatMap(fn($role) => explode(',', $role))
            ->map(fn($role) => trim($role))
            ->unique()
            ->values()
            ->all();

        if (!in_array($userRole, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 