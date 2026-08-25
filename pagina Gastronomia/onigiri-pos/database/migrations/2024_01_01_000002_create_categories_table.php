<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categorías de productos (Onigiris, Bebidas, Snacks, etc.)
 * Permite agrupar el menú y filtrar en el frontend.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Nombre visible al cliente (ej: "Onigiris Clásicos")
            $table->string('name', 100);

            // Slug para URLs amigables (ej: "onigiris-clasicos")
            $table->string('slug', 120)->unique();

            // Descripción corta para el frontend
            $table->string('description', 255)->nullable();

            // Ruta de imagen/ícono representativo de la categoría
            $table->string('image_path')->nullable();

            // Orden de aparición en el menú
            $table->unsignedTinyInteger('sort_order')->default(0);

            // Estado activo/inactivo (sin eliminar el registro)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
