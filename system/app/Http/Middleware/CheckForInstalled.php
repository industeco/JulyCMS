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
        $installed = config('app.installed');
        $toInstall = $request->is('install');
        $toMigrate = $request->is('install/migrate');
        if (($installed && ($toInstall || $toMigrate)) || (!$installed && !$toInstall)) {
            throw new NotFoundHttpException();
        }
        return $next($request);
    }
}
