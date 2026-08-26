<?php

namespace App\Providers;

use App\Events\OrderConfirmedEvent;
use App\Listeners\InvalidateCatalogCacheListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repositories (singleton para reutilizar instancias)
        $this->app->singleton(\App\Repositories\ProductRepository::class);
        $this->app->singleton(\App\Repositories\IngredientRepository::class);
        $this->app->singleton(\App\Repositories\OrderRepository::class);
    }

    public function boot(): void
    {
        if (app()->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        // Registrar Events → Listeners
        Event::listen(OrderConfirmedEvent::class, InvalidateCatalogCacheListener::class);
    }
}
