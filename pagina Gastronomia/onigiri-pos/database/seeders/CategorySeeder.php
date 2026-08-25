<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Onigiris Clásicos',
                'description' => 'Los onigiri tradicionales japoneses con rellenos auténticos',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Onigiris Premium',
                'description' => 'Rellenos especiales y combinaciones gourmet',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Onigiris Veganos',
                'description' => 'Sin productos de origen animal, 100% vegetal',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Bebidas',
                'description' => 'Tés, aguas saborizadas y bebidas japonesas',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Snacks y Extras',
                'description' => 'Complementos y aperitivos asiáticos',
                'sort_order'  => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name'        => $category['name'],
                'slug'        => Str::slug($category['name']),
                'description' => $category['description'],
                'sort_order'  => $category['sort_order'],
                'is_active'   => true,
            ]);
        }
    }
}
