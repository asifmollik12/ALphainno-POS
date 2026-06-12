<?php

namespace App\Http\Middleware;

use App\Services\ShopBootstrapService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopBootstrapped
{
    public function __construct(private ShopBootstrapService $bootstrap) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $this->bootstrap->ensureDefaults($request->user());
        }

        return $next($request);
    }
}
