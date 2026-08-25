<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Productos del catálogo (Onigiris, Bebidas, Snacks, etc.)
 *
 * NORMALIZACIÓN 3FN:
 * - El producto NO almacena ingredientes directamente (eso es la tabla "recipes").
 * - El producto solo almacena sus atributos propios: nombre, precio, descripción.
 * - La categoría se referencia por FK, no por nombre embebido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // FK a categoría (ej: "Onigiris Clásicos")
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();   // No eliminar si hay productos

            // Nombre del producto visible al cliente
            $table->string('name', 200);

            // Slug para URLs del frontend
            $table->string('slug', 220)->unique();

            // Descripción corta (lista de productos)
            $table->string('short_description', 255)->nullable();

            // Descripción larga (modal de detalle)
            $table->text('long_description')->nullable();

            // ---------------------------------------------------------------
            // PRECIOS
            // ---------------------------------------------------------------
            $table->decimal('price', 8, 2);                      // Precio de venta
            $table->decimal('compare_price', 8, 2)->nullable();  // Precio tachado (oferta)

            // ---------------------------------------------------------------
            // IMÁGENES
            // ---------------------------------------------------------------
            $table->string('main_image_path')->nullable();        // Imagen principal
            $table->json('gallery_images')->nullable();           // Galería adicional [json array]

            // ---------------------------------------------------------------
            // CONFIGURACIÓN DE VENTA
            // ---------------------------------------------------------------
            // ¿Controla stock via ingredientes o tiene stock propio?
            $table->boolean('tracks_ingredients')->default(true);

            // Stock directo (si tracks_ingredients = false, ej: bebidas embotelladas)
            $table->unsignedInteger('direct_stock')->default(0);

            // ¿Está disponible para venta hoy?
            $table->boolean('is_available')->default(true);

            // ¿Se destaca en la página de inicio?
            $table->boolean('is_featured')->default(false);

            // Orden de aparición dentro de la categoría
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Etiquetas para filtros (json: ["vegano", "picante", "nuevo"])
            $table->json('tags')->nullable();

            // Tiempo de preparación en minutos (para el cajero/POS)
            $table->unsignedTinyInteger('prep_time_minutes')->default(5);

            $table->timestamps();
            $table->softDeletes();

            // Índices para queries frecuentes
            $table->index(['is_available', 'is_featured']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
