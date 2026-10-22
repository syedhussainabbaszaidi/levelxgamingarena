<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Usage: ->middleware('permission:bookings.view') or
     *        ->middleware('permission:bookings.edit,bookings.create').
     *
     * Permissions are checked server-side against the database-backed role
     * matrix — never trust a hidden button in the browser.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
