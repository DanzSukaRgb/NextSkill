<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * StorageCorsMiddleware
 *
 * Adds CORS headers to storage file responses to allow cross-origin access to images.
 * This prevents CORS errors when frontend loads images from storage path.
 */
class StorageCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if request is for a storage file
        if ($request->is('storage/*')) {
            // Get the response from the next middleware
            $response = $next($request);

            // Add CORS headers
            $response->header('Cross-Origin-Resource-Policy', 'cross-origin');
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type');

            return $response;
        }

        return $next($request);
    }
}
