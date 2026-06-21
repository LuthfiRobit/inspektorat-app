<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeveloperKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.dev_ops.purge_key');

        if (empty($expectedKey)) {
            // If the key is not set in environment, pretend the route doesn't exist
            abort(404);
        }

        $providedKey = $request->header('X-Dev-Purge-Token') ?? $request->input('dev_key');

        if ($providedKey !== $expectedKey) {
            // Throw 404 instead of 401/403 to hide the endpoint
            abort(404);
        }

        return $next($request);
    }
}
