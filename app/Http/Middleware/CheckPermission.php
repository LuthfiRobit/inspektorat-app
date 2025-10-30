<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    // /**
    //  * Handle an incoming request.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \Closure  $next
    //  * @param  string  $permission
    //  * @return \Symfony\Component\HttpFoundation\Response
    //  */
    // public function handle(Request $request, Closure $next, $permission = null) // Permission is now optional
    // {
    //     if (Auth::check()) {
    //         $user = Auth::user();

    //         if ($permission === null) {  // No permission specified - allow access (use with caution!)
    //             return $next($request);
    //         }

    //         foreach ($user->roles as $role) {
    //             if ($role->permissions->contains('permission_name', $permission)) {
    //                 return $next($request);
    //             }
    //         }
    //     }
    //     // Handle unauthorized request

    //     // Check if the request is an AJAX request
    //     if ($request->ajax()) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     // For non-AJAX requests, return a 403 error view
    //     return response()->view('errors.403', [], 403);

    //     // abort(403, 'Unauthorized.');
    // }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $this->unauthorized($request);
        }

        $user = Auth::user();
        $routeName = $request->route()?->getName();

        // Jika route tidak dinamai, tolak akses sebagai pengaman tambahan
        if (!$routeName) {
            return $this->unauthorized($request, 'Route is not named.');
        }

        // Periksa apakah user punya permission melalui role
        foreach ($user->roles as $role) {
            if ($role->permissions->contains('permission_name', $routeName)) {
                return $next($request);
            }
        }

        // Tidak punya izin
        return $this->unauthorized($request);
    }

    protected function unauthorized(Request $request, string $message = 'Unauthorized')
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['error' => $message], 403);
        }

        return response()->view('errors.403', ['message' => $message], 403);
    }
}
