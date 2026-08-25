<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modifica la tabla users para agregar el rol (cliente, cajero, administrador).
 * La tabla base la crea Laravel por defecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol del usuario dentro del sistema
            $table->enum('role', ['customer', 'cashier', 'admin'])
                  ->default('customer')
                  ->after('email');

            // Teléfono para contacto / notificaciones de pedido
            $table->string('phone', 20)->nullable()->after('role');

            // Dirección de envío (opcional para delivery)
            $table->string('address')->nullable()->after('phone');

            // Soft delete: permite desactivar usuarios sin eliminarlos
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'address', 'deleted_at']);
        });
    }
};
