<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\AppParamsController;
use App\Models\AppParams;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$isWebRoute): Response
    {

        // Get the type of the request (GET, POST, etc.)
        $requestType = $request->getMethod();

        $params = AppParams::select('app_params.*')->first();
        if ($params->maintenance == true) {
            if ($isWebRoute) {
                // If maintenance mode is enabled, redirect to the maintenance page
                return response()->view('maintenance', [], 503);
            } else if ($requestType !== 'GET') {
                // If maintenance mode is enabled, return a 503 response
                return response()->json(['error' => 'The application is currently under maintenance. Please try again later.'], 503);
            }
        }
        return $next($request);
    }
}
