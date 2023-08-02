<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;

class LanguageMiddleware
{
    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->app->setLocale(session('locale', config('app.locale')));

        return $next($request);
    }
}
