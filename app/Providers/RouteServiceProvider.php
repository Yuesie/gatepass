<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL; // <-- TAMBAHKAN INI
use App\Http\Middleware\RoleAccessCheck; 

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     */
    public const HOME = '/dashboard'; 

    public function boot(): void
    {
        // LOGIKA UNTUK MEMAKSA SEMUA URL DAN ASSET MENGGUNAKAN HTTPS
        // Ini mengatasi error "Mixed Content" saat menggunakan HTTPS lokal (Laragon)
        if ($this->app->environment('local') && env('APP_URL') !== 'http://localhost') {
            URL::forceScheme('https');
        }
        
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}