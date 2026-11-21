<?php

namespace App\Providers;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Daftarkan Telescope hanya pada environment lokal saat dev-dependency terpasang
        // agar deployment `composer install --no-dev` di production tidak pernah crash.
        if (
            $this->app->environment('local')
            && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)
        ) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $trustedProxies = config('pemira.trusted_proxies');
        if (is_string($trustedProxies) && trim($trustedProxies) !== '') {
            $normalized = trim($trustedProxies);
            TrustProxies::at(
                $normalized === '*'
                    ? '*'
                    : array_values(array_filter(array_map('trim', explode(',', $normalized))))
            );
        }

        Vite::prefetch(concurrency: 3);
    }
}
