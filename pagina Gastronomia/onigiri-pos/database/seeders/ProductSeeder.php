<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $clasicos   = Category::where('slug', 'onigiris-clasicos')->first();
        $premium    = Category::where('slug', 'onigiris-premium')->first();
        $veganos    = Category::where('slug', 'onigiris-veganos')->first();
        $bebidas    = Category::where('slug', 'bebidas')->first();
        $snacks     = Category::where('slug', 'snacks-y-extras')->first();

        $products = [
            // ----------------------------------------------------------------
            // ONIGIRIS CLÁSICOS
            // ----------------------------------------------------------------
            [
                'category_id'       => $clasicos->id,
                'name'              => 'Onigiri de Salmón',
                'short_description' => 'Arroz japonés relleno de salmón fresco con toque de salsa de soja',
                'long_description'  => 'Nuestro clásico favorito. Arroz Koshihikari perfectamente cocido, relleno con salmón fresco marinado y envuelto en crujiente alga nori tostada. Un sabor que transporta directo a Tokio.',
                'price'             => 8.50,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => true,
                'prep_time_minutes' => 5,
                'tags'              => ['clasico', 'proteico'],
                'sort_order'        => 1,
            ],
            [
                'category_id'       => $clasicos->id,
                'name'              => 'Onigiri de Atún',
                'short_description' => 'El más solicitado: atún con mayonesa Kewpie y alga nori',
                'long_description'  => 'Combinación perfecta de atún en aceite con la icónica mayonesa japonesa Kewpie, envuelto en nori. El favorito de Japón, ahora en tu ciudad.',
                'price'             => 7.50,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => true,
                'prep_time_minutes' => 4,
                'tags'              => ['clasico', 'proteico', 'popular'],
                'sort_order'        => 2,
            ],
            [
                'category_id'       => $clasicos->id,
                'name'              => 'Onigiri Umeboshi',
                'short_description' => 'Ciruela japonesa encurtida, el sabor más auténtico',
                'long_description'  => 'La receta más tradicional del Japón feudal. Una sola ciruela umeboshi en el corazón del onigiri, equilibrando perfectamente el umami del arroz. Para los puristas.',
                'price'             => 6.50,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => false,
                'prep_time_minutes' => 3,
                'tags'              => ['clasico', 'vegano', 'tradicional'],
                'sort_order'        => 3,
            ],

            // ----------------------------------------------------------------
            // ONIGIRIS PREMIUM
            // ----------------------------------------------------------------
            [
                'category_id'       => $premium->id,
                'name'              => 'Onigiri Salmón Premium',
                'short_description' => 'Salmón fresco con aguacate y salsa teriyaki artesanal',
                'long_description'  => 'Una fusión de sabores que eleva el onigiri clásico. Salmón fresco combinado con aguacate cremoso y nuestra salsa teriyaki artesanal. Cubierto con furikake premium.',
                'price'             => 12.50,
                'compare_price'     => 15.00,
                'tracks_ingredients'=> true,
                'is_featured'       => true,
                'prep_time_minutes' => 7,
                'tags'              => ['premium', 'proteico', 'especial'],
                'sort_order'        => 1,
            ],
            [
                'category_id'       => $premium->id,
                'name'              => 'Onigiri Tempura',
                'short_description' => 'Camarón tempura crujiente con mayonesa picante',
                'long_description'  => 'Camarón rebozado en tempura perfectamente crujiente, complementado con nuestra mayonesa Kewpie especiada con togarashi. Una experiencia de texturas y sabores únicos.',
                'price'             => 14.00,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => true,
                'prep_time_minutes' => 8,
                'tags'              => ['premium', 'proteico', 'crujiente'],
                'sort_order'        => 2,
            ],
            [
                'category_id'       => $premium->id,
                'name'              => 'Onigiri Pollo Teriyaki',
                'short_description' => 'Pollo marinado en teriyaki casero con pepino fresco',
                'long_description'  => 'Pechuga de pollo marinada 24h en nuestra salsa teriyaki casera, acompañada de pepino japonés refrescante. Un balance perfecto entre dulce y umami.',
                'price'             => 10.50,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => false,
                'prep_time_minutes' => 6,
                'tags'              => ['premium', 'proteico', 'teriyaki'],
                'sort_order'        => 3,
            ],

            // ----------------------------------------------------------------
            // ONIGIRIS VEGANOS
            // ----------------------------------------------------------------
            [
                'category_id'       => $veganos->id,
                'name'              => 'Onigiri Vegano Aguacate',
                'short_description' => 'Aguacate cremoso con pepino y furikake de algas',
                'long_description'  => 'Una opción 100% vegetal que no sacrifica sabor. Aguacate maduro con pepino japonés crujiente y una generosa capa de furikake de algas. Fresco, nutritivo y delicioso.',
                'price'             => 8.00,
                'compare_price'     => null,
                'tracks_ingredients'=> true,
                'is_featured'       => false,
                'prep_time_minutes' => 4,
                'tags'              => ['vegano', 'saludable', 'fresco'],
                'sort_order'        => 1,
            ],

            // ----------------------------------------------------------------
            // BEBIDAS
            // ----------------------------------------------------------------
            [
                'category_id'        => $bebidas->id,
                'name'               => 'Té Verde Matcha Frío',
                'short_description'  => 'Té matcha premium preparado en frío con hielo',
                'long_description'   => 'Matcha de grado ceremonial preparado en frío, sin azúcar añadida. La acompañante perfecta para cualquier onigiri.',
                'price'              => 5.50,
                'compare_price'      => null,
                'tracks_ingredients' => false,  // Stock directo (botellas)
                'direct_stock'       => 50,
                'is_featured'        => false,
                'prep_time_minutes'  => 1,
                'tags'               => ['bebida', 'matcha', 'frio'],
                'sort_order'         => 1,
            ],
            [
                'category_id'        => $bebidas->id,
                'name'               => 'Agua de Coco Natural',
                'short_description'  => 'Agua de coco 100% natural, sin conservantes',
                'long_description'   => 'Refrescante agua de coco natural, la bebida hidratante favorita de Asia. Sin azúcar añadida ni conservantes.',
                'price'              => 4.50,
                'compare_price'      => null,
                'tracks_ingredients' => false,
                'direct_stock'       => 60,
                'is_featured'        => false,
                'prep_time_minutes'  => 1,
                'tags'               => ['bebida', 'natural', 'saludable'],
                'sort_order'         => 2,
            ],
        ];

        foreach ($products as $product) {
            $tags = $product['tags'] ?? [];
            unset($product['tags']);

            Product::create(array_merge($product, [
                'slug'       => Str::slug($product['name']),
                'tags'       => $tags,
                'is_available' => true,
            ]));
        }
    }
}
