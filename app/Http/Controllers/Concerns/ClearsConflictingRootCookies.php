<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;

trait ClearsConflictingRootCookies
{
    /**
     * POS runs under /pos while SaaS shares erp.alphainno.com at /.
     * Stale root-path session/API cookies break Passport cookie auth.
     */
    protected function redirectToPosDashboard(): RedirectResponse
    {
        $target = rtrim(config('app.url'), '/').'/app/dashboard';
        $domain = config('session.domain');

        $response = redirect()->away($target);

        foreach (['laravel_token', 'XSRF-TOKEN', 'web_session', 'pos_session'] as $name) {
            $response->withoutCookie($name, '/', $domain);
        }

        return $response;
    }
}
