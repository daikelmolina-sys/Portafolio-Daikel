<?php

namespace App\Providers;

use App\Events\OrderConfirmedEvent;
use App\Listeners\InvalidateCatalogCacheListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        // Registrar Events → Listeners
        Event::listen(OrderConfirmedEvent::class, InvalidateCatalogCacheListener::class);
    }
}
