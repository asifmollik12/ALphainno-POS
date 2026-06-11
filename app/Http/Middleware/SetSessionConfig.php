<?php

// app/Http/Middleware/SetSessionConfig.php

namespace App\Http\Middleware;

use Closure;
use Config;

class SetSessionConfig
{
    public function handle($request, Closure $next)
    {
        if ($request->is('online_store') || $request->is('online_store/*')) {
            Config::set('session.path', '/online_store');
            Config::set('session.cookie', 'store_session');
        } else {
            Config::set('session.path', env('SESSION_PATH', '/pos'));
            Config::set('session.cookie', env('SESSION_COOKIE', 'pos_session'));
        }

        return $next($request);
    }
}
