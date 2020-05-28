<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = '/'.ltrim(strtolower($request->decodedPath()), '/');
        $except = [
            short_route('admin.login'),
            short_route('admin.logout'),
        ];

        if (Auth::guard('admin')->guest() && !in_array($path, $except)) {
            return redirect()->guest(route('admin.login'));
        }

        return $next($request);
    }
}
