<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [
            // ----------------------------------------------------------------
            // BASES
            // ----------------------------------------------------------------
            [
                'name'            => 'Arroz Koshihikari',
                'sku'             => 'ING-BASE-001',
                'description'     => 'Arroz japonés de grano corto, especial para onigiri',
                'stock_quantity'  => 10000,    // 10 kg en gramos
                'unit'            => 'gr',
                'min_stock_alert' => 2000,
                'unit_cost'       => 0.008,    // S/ 0.008 por gramo
            ],
            [
                'name'            => 'Alga Nori',
                'sku'             => 'ING-BASE-002',
                'description'     => 'Láminas de alga nori tostada premium',
                'stock_quantity'  => 500,
                'unit'            => 'unit',   // Unidades (láminas)
                'min_stock_alert' => 50,
                'unit_cost'       => 0.35,
            ],
            [
                'name'            => 'Sal marina fina',
                'sku'             => 'ING-BASE-003',
                'description'     => 'Sal para sazonar el arroz',
                'stock_quantity'  => 5000,
                'unit'            => 'gr',
                'min_stock_alert' => 500,
                'unit_cost'       => 0.001,
            ],
            [
                'name'            => 'Vinagre de arroz',
                'sku'             => 'ING-BASE-004',
                'description'     => 'Vinagre de arroz para aderezar',
                'stock_quantity'  => 3000,
                'unit'            => 'ml',
                'min_stock_alert' => 500,
                'unit_cost'       => 0.003,
            ],

            // ----------------------------------------------------------------
            // RELLENOS PROTEICOS
            // ----------------------------------------------------------------
            [
                'name'            => 'Salmón fresco',
                'sku'             => 'ING-PROT-001',
                'description'     => 'Filete de salmón fresco, sin piel',
                'stock_quantity'  => 3000,
                'unit'            => 'gr',
                'min_stock_alert' => 500,
                'unit_cost'       => 0.05,
            ],
            [
                'name'            => 'Atún en aceite',
                'sku'             => 'ING-PROT-002',
                'description'     => 'Atún en aceite de oliva, escurrido',
                'stock_quantity'  => 2000,
                'unit'            => 'gr',
                'min_stock_alert' => 400,
                'unit_cost'       => 0.025,
            ],
            [
                'name'            => 'Pollo teriyaki',
                'sku'             => 'ING-PROT-003',
                'description'     => 'Pechuga de pollo marinada en salsa teriyaki',
                'stock_quantity'  => 2500,
                'unit'            => 'gr',
                'min_stock_alert' => 500,
                'unit_cost'       => 0.03,
            ],
            [
                'name'            => 'Camarón tempura',
                'sku'             => 'ING-PROT-004',
                'description'     => 'Camarón rebozado en masa tempura',
                'stock_quantity'  => 1500,
                'unit'            => 'gr',
                'min_stock_alert' => 300,
                'unit_cost'       => 0.06,
            ],

            // ----------------------------------------------------------------
            // RELLENOS VEGANOS
            // ----------------------------------------------------------------
            [
                'name'            => 'Pepino japonés',
                'sku'             => 'ING-VEG-001',
                'description'     => 'Pepino tipo japonés, finamente cortado',
                'stock_quantity'  => 2000,
                'unit'            => 'gr',
                'min_stock_alert' => 300,
                'unit_cost'       => 0.004,
            ],
            [
                'name'            => 'Aguacate',
                'sku'             => 'ING-VEG-002',
                'description'     => 'Aguacate maduro en cubos',
                'stock_quantity'  => 1500,
                'unit'            => 'gr',
                'min_stock_alert' => 300,
                'unit_cost'       => 0.012,
            ],
            [
                'name'            => 'Ciruela umeboshi',
                'sku'             => 'ING-VEG-003',
                'description'     => 'Ciruela japonesa encurtida, ligeramente salada',
                'stock_quantity'  => 1000,
                'unit'            => 'unit',
                'min_stock_alert' => 50,
                'unit_cost'       => 0.40,
            ],

            // ----------------------------------------------------------------
            // SALSAS Y CONDIMENTOS
            // ----------------------------------------------------------------
            [
                'name'            => 'Salsa de soja',
                'sku'             => 'ING-COND-001',
                'description'     => 'Salsa de soja japonesa (shoyu)',
                'stock_quantity'  => 2000,
                'unit'            => 'ml',
                'min_stock_alert' => 300,
                'unit_cost'       => 0.004,
            ],
            [
                'name'            => 'Mayonesa japonesa Kewpie',
                'sku'             => 'ING-COND-002',
                'description'     => 'Mayonesa japonesa de yema de huevo',
                'stock_quantity'  => 1500,
                'unit'            => 'gr',
                'min_stock_alert' => 200,
                'unit_cost'       => 0.012,
            ],
            [
                'name'            => 'Furikake',
                'sku'             => 'ING-COND-003',
                'description'     => 'Mezcla de especias japonesas para arroz',
                'stock_quantity'  => 800,
                'unit'            => 'gr',
                'min_stock_alert' => 100,
                'unit_cost'       => 0.02,
            ],
        ];

        foreach ($ingredients as $ingredient) {
            Ingredient::create(array_merge($ingredient, ['is_active' => true]));
        }
    }
}
