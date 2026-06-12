<?php

namespace App\Providers;

use App\Support\PosMenu;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($url = config('app.url')) {
            URL::forceRootUrl($url);
        }

        View::composer(['layouts.dashboard', 'dashboard.*'], function ($view) {
            $view->with('posMenu', PosMenu::items());
            $view->with('shopSetting', auth()->user()?->shopSetting);
        });
    }
}
