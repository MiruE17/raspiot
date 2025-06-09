<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiToken;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the token from the Authorization header
        $bearerToken = $request->bearerToken();
        
        if (!$bearerToken) {
            throw new AuthenticationException('API token not provided');
        }
        
        // Find the token in the database
        $token = ApiToken::findToken($bearerToken);
        
        if (!$token) {
            throw new AuthenticationException('Invalid API token');
        }
        
        // Check if token is active
        if (!$token->active) {
            throw new AuthenticationException('API token is inactive');
        }
        
        // Check if token is expired
        if ($token->isExpired()) {
            $token->active = false;
            $token->save();
            throw new AuthenticationException('API token has expired');
        }
        
        // Record token usage
        $token->recordUsage();
        
        // Set the authenticated user
        auth()->setUser($token->user);
        
        // Add token info to the request
        $request->attributes->add(['api_token' => $token]);
        
        return $next($request);
    }
}