<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetupMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If we are already on the setup pages, don't redirect
        if ($request->is('setup*')) {
            return $next($request);
        }

        // Check if the application is installed
        // We look for a specific env variable or a marker file
        if (config('app.installed', false) === false) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
