<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

/**
 * RecipeSeeder — Define qué ingredientes y en qué cantidad necesita cada producto.
 *
 * Cantidades por 1 onigiri de tamaño estándar (~200gr total):
 *  - Arroz:       150gr (base)
 *  - Nori:        1 lámina
 *  - Sal:         2gr
 *  - Vinagre:     5ml
 *  - Relleno:     30-50gr según tipo
 */
class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Helper para obtener IDs
        $product    = fn(string $slug) => Product::where('slug', $slug)->firstOrFail();
        $ingredient = fn(string $sku)  => Ingredient::where('sku', $sku)->firstOrFail();

        // ---------------------------------------------------------------
        // BASE COMÚN (arroz, nori, sal, vinagre) para todos los onigiris
        // ---------------------------------------------------------------
        $baseIngredients = [
            ['sku' => 'ING-BASE-001', 'quantity' => 150,  'unit' => 'gr'],   // Arroz
            ['sku' => 'ING-BASE-002', 'quantity' => 1,    'unit' => 'unit'], // Nori
            ['sku' => 'ING-BASE-003', 'quantity' => 2,    'unit' => 'gr'],   // Sal
            ['sku' => 'ING-BASE-004', 'quantity' => 5,    'unit' => 'ml'],   // Vinagre
        ];

        // ---------------------------------------------------------------
        // RECETAS POR PRODUCTO
        // ---------------------------------------------------------------
        $recipes = [
            'onigiri-de-salmon' => [
                ...$baseIngredients,
                ['sku' => 'ING-PROT-001', 'quantity' => 35,  'unit' => 'gr'],  // Salmón
                ['sku' => 'ING-COND-001', 'quantity' => 5,   'unit' => 'ml'],  // Salsa soja
            ],
            'onigiri-de-atun' => [
                ...$baseIngredients,
                ['sku' => 'ING-PROT-002', 'quantity' => 30,  'unit' => 'gr'],  // Atún
                ['sku' => 'ING-COND-002', 'quantity' => 15,  'unit' => 'gr'],  // Kewpie
            ],
            'onigiri-umeboshi' => [
                ...$baseIngredients,
                ['sku' => 'ING-VEG-003',  'quantity' => 1,   'unit' => 'unit'], // Umeboshi
            ],
            'onigiri-salmon-premium' => [
                ...$baseIngredients,
                ['sku' => 'ING-PROT-001', 'quantity' => 50,  'unit' => 'gr'],  // Salmón
                ['sku' => 'ING-VEG-002',  'quantity' => 30,  'unit' => 'gr'],  // Aguacate
                ['sku' => 'ING-COND-003', 'quantity' => 5,   'unit' => 'gr'],  // Furikake
                ['sku' => 'ING-COND-001', 'quantity' => 5,   'unit' => 'ml'],  // Salsa soja
            ],
            'onigiri-tempura' => [
                ...$baseIngredients,
                ['sku' => 'ING-PROT-004', 'quantity' => 60,  'unit' => 'gr'],  // Camarón tempura
                ['sku' => 'ING-COND-002', 'quantity' => 20,  'unit' => 'gr'],  // Kewpie
            ],
            'onigiri-pollo-teriyaki' => [
                ...$baseIngredients,
                ['sku' => 'ING-PROT-003', 'quantity' => 45,  'unit' => 'gr'],  // Pollo teriyaki
                ['sku' => 'ING-VEG-001',  'quantity' => 20,  'unit' => 'gr'],  // Pepino
                ['sku' => 'ING-COND-001', 'quantity' => 8,   'unit' => 'ml'],  // Salsa soja
            ],
            'onigiri-vegano-aguacate' => [
                ...$baseIngredients,
                ['sku' => 'ING-VEG-002',  'quantity' => 40,  'unit' => 'gr'],  // Aguacate
                ['sku' => 'ING-VEG-001',  'quantity' => 25,  'unit' => 'gr'],  // Pepino
                ['sku' => 'ING-COND-003', 'quantity' => 5,   'unit' => 'gr'],  // Furikake
            ],
        ];

        foreach ($recipes as $productSlug => $recipeItems) {
            $prod = $product($productSlug);

            foreach ($recipeItems as $item) {
                $ing = $ingredient($item['sku']);

                Recipe::create([
                    'product_id'       => $prod->id,
                    'ingredient_id'    => $ing->id,
                    'quantity_per_unit' => $item['quantity'],
                    'unit'             => $item['unit'],
                ]);
            }
        }
    }
}
