<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     * Crea una orden vacía con sus datos de cabecera.
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Retorna órdenes activas para el panel del cajero (POS).
     * Solo las que están pendientes o en preparación.
     */
    public function getActiveForPos(): Collection
    {
        return Order::with(['items', 'user'])
            ->whereIn('status', ['confirmed', 'preparing'])
            ->orderBy('confirmed_at', 'asc')
            ->get();
    }

    /**
     * Historial de órdenes paginado para el panel de administración.
     */
    public function getPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return Order::with(['user', 'cashier', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Encuentra una orden con todos sus datos relacionados.
     */
    public function findWithRelations(int $id): ?Order
    {
        return Order::with([
            'items.product',
            'user',
            'cashier',
            'invoice',
            'ingredientMovements.ingredient',
        ])->find($id);
    }

    /**
     * Cambia el estado de la orden y registra el timestamp correspondiente.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $timestamps = [
            'confirmed'  => 'confirmed_at',
            'ready'      => 'ready_at',
            'delivered'  => 'delivered_at',
            'cancelled'  => 'cancelled_at',
        ];

        $data = ['status' => $status];

        if (isset($timestamps[$status])) {
            $data[$timestamps[$status]] = now();
        }

        $order->update($data);

        return $order->fresh();
    }
}
