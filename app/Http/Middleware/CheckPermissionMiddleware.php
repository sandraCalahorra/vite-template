<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $entity, string $action): Response
    {
        $user = auth()->user();

        if (!$user->hasPermission($entity, $action)) {
            return response()->json([
                'error' => 'Permission Denied',
            ], 403);
        }

        return $next($request);
    }
}
