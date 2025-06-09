<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'api:'.$request->ip();
        
        // Limit to 60 requests per minute
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }
        
        RateLimiter::hit($key, 60);
        
        return $next($request);
    }
}