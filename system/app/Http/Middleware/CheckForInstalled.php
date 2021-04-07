<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckForInstalled
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
        $installed = config('app.is_installed');
        $toInstall = $request->is('install') || $request->is('install/migrate');
        if (($installed && $toInstall) || (!$installed && !$toInstall)) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}
