<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetSessionPath
{
    public function handle(Request $request, Closure $next): Response
    {
        Config::set('session.path', env('SESSION_PATH', '/pos'));

        return $next($request);
    }
}
