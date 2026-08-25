<?php

namespace App\Listeners;

use App\Events\OrderConfirmedEvent;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Invalida la caché del catálogo de productos en Redis cuando se confirma una venta.
 * Implementa ShouldQueue para no bloquear la respuesta HTTP.
 */
class InvalidateCatalogCacheListener implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(private readonly ProductRepository $productRepo) {}

    public function handle(OrderConfirmedEvent $event): void
    {
        $this->productRepo->invalidateCatalogCache();
    }
}
