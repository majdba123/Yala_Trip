<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class driver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && !$user->Driver) {
            // If the user is authenticated but doesn't have a corresponding entry in the drivers table,
            // you can redirect them to a specific page or return a response with an error message.
            return response()->json(['error' => 'You are not Driver'], 403);
        }
        return $next($request);
    }
}
