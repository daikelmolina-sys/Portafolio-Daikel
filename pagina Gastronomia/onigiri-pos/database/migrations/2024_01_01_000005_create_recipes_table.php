<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de Recetas — Relación MUCHOS A MUCHOS entre Productos e Ingredientes.
 *
 * NORMALIZACIÓN 3FN:
 * - Elimina la dependencia transitiva: el producto no almacena ingredientes.
 * - Cada fila representa: "para hacer 1 unidad del producto X, necesito Y cantidad del ingrediente Z".
 * - La columna `quantity_per_unit` es el dato clave: cuánto ingrediente consume 1 unidad del producto.
 *
 * EJEMPLO:
 *   product_id=1 (Onigiri Salmón) | ingredient_id=3 (Salmón) | quantity_per_unit=30 | unit=gr
 *   product_id=1 (Onigiri Salmón) | ingredient_id=1 (Arroz)  | quantity_per_unit=150 | unit=gr
 *   product_id=1 (Onigiri Salmón) | ingredient_id=2 (Nori)   | quantity_per_unit=1   | unit=unit
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            // FK Producto
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();   // Si se elimina el producto, se elimina la receta

            // FK Ingrediente
            $table->foreignId('ingredient_id')
                  ->constrained('ingredients')
                  ->restrictOnDelete(); // No eliminar ingrediente si está en una receta

            // Cantidad de este ingrediente necesaria para 1 unidad del producto
            // DECIMAL para soportar fracciones (ej: 0.5 gr de alga nori)
            $table->decimal('quantity_per_unit', 10, 3);

            // Unidad del ingrediente en esta receta (puede diferir de la del stock)
            // Ej: el stock en kg, pero la receta usa gr → conversión a implementar en la Action
            $table->enum('unit', ['gr', 'kg', 'ml', 'lt', 'unit'])->default('gr');

            // Nota del chef / instrucción especial (opcional)
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            // UNIQUE CONSTRAINT: un producto no puede tener el mismo ingrediente dos veces
            $table->unique(['product_id', 'ingredient_id'], 'unique_product_ingredient');

            // Índice compuesto para consultas de inventario
            $table->index(['product_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
