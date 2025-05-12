<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CoachMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in and is a coach (can also be an admin)
        if (!auth()->check() || !auth()->user()->is_coach) {
            return redirect('/');
        }

        return $next($request);
    }
}