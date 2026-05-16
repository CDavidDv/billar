<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        ProductCategory::query()->delete();
        Product::query()->delete();

        $cats = collect([
            ['name' => 'Whisky',              'icon' => '🥃', 'sort_order' => 1],
            ['name' => 'Ron Bacardí',          'icon' => '🍹', 'sort_order' => 2],
            ['name' => 'Tequila',              'icon' => '🌵', 'sort_order' => 3],
            ['name' => 'Caguamas',             'icon' => '🍺', 'sort_order' => 4],
            ['name' => 'Cervezas individuales','icon' => '🍻', 'sort_order' => 5],
            ['name' => 'Preparadas',           'icon' => '🧃', 'sort_order' => 6],
            ['name' => 'Refrescos y agua',     'icon' => '🥤', 'sort_order' => 7],
            ['name' => 'Botanas',              'icon' => '🍿', 'sort_order' => 8],
            ['name' => 'Cerveza Cero',         'icon' => '0️⃣', 'sort_order' => 9],
            ['name' => 'Promociones',          'icon' => '🎯', 'sort_order' => 10],
            ['name' => 'Cigarros',             'icon' => '🚬', 'sort_order' => 11],
        ])->map(fn($d) => ProductCategory::create($d))->keyBy('name');

        $products = [
            // ── WHISKY ──────────────────────────────────────────────────────
            ['category' => 'Whisky', 'name' => 'Torres 5 (botella)',        'price' => 550, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'Torres 5 (copeo)',          'price' => 50],
            ['category' => 'Whisky', 'name' => 'William Lawsons (botella)', 'price' => 450, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'William Lawsons (copeo)',   'price' => 50],
            ['category' => 'Whisky', 'name' => 'Black & White (botella)',   'price' => 500, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'Black & White (copeo)',     'price' => 55],
            ['category' => 'Whisky', 'name' => 'Red Label (botella)',       'price' => 700, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'Red Label (copeo)',         'price' => 70],
            ['category' => 'Whisky', 'name' => 'Buchanan\'s Deluxe (botella)', 'price' => 1300, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'Buchanan\'s Deluxe (copeo)',   'price' => 120],
            ['category' => 'Whisky', 'name' => 'Absolut (botella)',         'price' => 600, 'description' => 'Botella completa'],
            ['category' => 'Whisky', 'name' => 'Absolut (copeo)',           'price' => 60],

            // ── RON BACARDÍ ──────────────────────────────────────────────────
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Blanco 1L (botella)',   'price' => 580, 'description' => 'Botella completa 1L'],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Blanco 3/4 (botella)',  'price' => 480, 'description' => 'Botella completa 3/4'],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Blanco (copeo)',        'price' => 60],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Mango (botella)',       'price' => 500, 'description' => 'Botella completa'],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Mango (copeo)',         'price' => 60],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Limón (botella)',       'price' => 500, 'description' => 'Botella completa'],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Limón (copeo)',         'price' => 60],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Coco (botella)',        'price' => 500, 'description' => 'Botella completa'],
            ['category' => 'Ron Bacardí', 'name' => 'Bacardí Coco (copeo)',          'price' => 60],

            // ── TEQUILA ──────────────────────────────────────────────────────
            ['category' => 'Tequila', 'name' => 'Jose Cuervo Especial 1L (botella)',    'price' => 580, 'description' => 'Botella completa 1L'],
            ['category' => 'Tequila', 'name' => 'Jose Cuervo Especial 3/4 (botella)',   'price' => 480, 'description' => 'Botella completa 3/4'],
            ['category' => 'Tequila', 'name' => 'Jose Cuervo Especial (copeo)',         'price' => 55],
            ['category' => 'Tequila', 'name' => 'Jose Cuervo Tradicional (botella)',    'price' => 700, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Jose Cuervo Tradicional (copeo)',      'price' => 70],
            ['category' => 'Tequila', 'name' => 'Hornitos 1L (botella)',                'price' => 850, 'description' => 'Botella completa 1L'],
            ['category' => 'Tequila', 'name' => 'Hornitos 3/4 (botella)',               'price' => 700, 'description' => 'Botella completa 3/4'],
            ['category' => 'Tequila', 'name' => 'Hornitos (copeo)',                     'price' => 70],
            ['category' => 'Tequila', 'name' => 'Don Julio Blanco (botella)',            'price' => 1000, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Don Julio Blanco (copeo)',              'price' => 100],
            ['category' => 'Tequila', 'name' => 'Maestro Dobel (botella)',               'price' => 1200, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Maestro Dobel (copeo)',                 'price' => 120],
            ['category' => 'Tequila', 'name' => 'Don Julio 70 (botella)',                'price' => 1300, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Don Julio 70 (copeo)',                  'price' => 120],
            ['category' => 'Tequila', 'name' => 'Azul Tamarindo',                        'price' => 300, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Azul Blu',                              'price' => 300, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Azul Agave',                            'price' => 300, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Azul (copeo)',                          'price' => 40],
            ['category' => 'Tequila', 'name' => 'Tradicional Cristalino (botella)',      'price' => 900, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Tradicional Cristalino (copeo)',        'price' => 90],
            ['category' => 'Tequila', 'name' => 'Centenario Ultra 3/4 (botella)',        'price' => 900, 'description' => 'Botella completa 3/4'],
            ['category' => 'Tequila', 'name' => 'Centenario Plata 1L (botella)',         'price' => 800, 'description' => 'Botella completa 1L'],
            ['category' => 'Tequila', 'name' => 'Centenario Plata 1L (copeo)',           'price' => 80],
            ['category' => 'Tequila', 'name' => 'Centenario Reposado 1L (botella)',      'price' => 800, 'description' => 'Botella completa 1L'],
            ['category' => 'Tequila', 'name' => 'Centenario Reposado 1L (copeo)',        'price' => 60],
            ['category' => 'Tequila', 'name' => 'Centenario Reposado 3/4 (botella)',     'price' => 700, 'description' => 'Botella completa 3/4'],
            ['category' => 'Tequila', 'name' => 'Centenario Reposado 3/4 (copeo)',       'price' => 70],
            ['category' => 'Tequila', 'name' => 'Centenario Plata 3/4 (botella)',        'price' => 700, 'description' => 'Botella completa 3/4'],
            ['category' => 'Tequila', 'name' => 'Centenario Plata 3/4 (copeo)',          'price' => 60],
            ['category' => 'Tequila', 'name' => 'Presidente Pica Piña (botella)',        'price' => 450, 'description' => 'Botella completa'],
            ['category' => 'Tequila', 'name' => 'Presidente Pica Piña (copeo)',          'price' => 45],

            // ── CAGUAMAS ─────────────────────────────────────────────────────
            ['category' => 'Caguamas', 'name' => 'Caguama Heineken',     'price' => 70, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Tecate',       'price' => 70, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Indio',        'price' => 70, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Dos Equis',    'price' => 70, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Dos Equis Ámbar', 'price' => 70, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Corona',       'price' => 80, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Victoria',     'price' => 80, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Modelo',       'price' => 80, 'is_beer_product' => true],
            ['category' => 'Caguamas', 'name' => 'Caguama Modelo Negra', 'price' => 80, 'is_beer_product' => true],

            // ── CERVEZAS INDIVIDUALES ────────────────────────────────────────
            ['category' => 'Cervezas individuales', 'name' => 'Indio 1/4',          'price' => 15],
            ['category' => 'Cervezas individuales', 'name' => 'Dos Equis 1/4',      'price' => 15],
            ['category' => 'Cervezas individuales', 'name' => 'Dos Equis media',    'price' => 30],
            ['category' => 'Cervezas individuales', 'name' => 'Indio Agave',        'price' => 25],
            ['category' => 'Cervezas individuales', 'name' => 'Modelo 473ml',       'price' => 35],
            ['category' => 'Cervezas individuales', 'name' => 'Modelo Negra 473ml', 'price' => 35],
            ['category' => 'Cervezas individuales', 'name' => 'Victoria 473ml',     'price' => 35],
            ['category' => 'Cervezas individuales', 'name' => 'Corona 355ml',       'price' => 35],

            // ── PREPARADAS ───────────────────────────────────────────────────
            ['category' => 'Preparadas', 'name' => 'Viña',    'price' => 35],
            ['category' => 'Preparadas', 'name' => 'Caribe',  'price' => 35],
            ['category' => 'Preparadas', 'name' => 'New Mix', 'price' => 35],

            // ── REFRESCOS Y AGUA ─────────────────────────────────────────────
            ['category' => 'Refrescos y agua', 'name' => 'Fanta Naranja 600ml',   'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Fanta Roja 600ml',      'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Sidral Mundet 600ml',   'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Sprite 600ml',          'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Squirt 600ml',          'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Peñafiel de Sabores',   'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Coca de Vidrio 355ml',  'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Coca Desechable 500ml', 'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Sangría 600ml',         'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Jumex 1L',              'price' => 35],
            ['category' => 'Refrescos y agua', 'name' => 'Jumex Lata',            'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Boing 500ml',           'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Amper Lata',            'price' => 30],
            ['category' => 'Refrescos y agua', 'name' => 'Schweppes',             'price' => 25],
            ['category' => 'Refrescos y agua', 'name' => 'Agua 1L',               'price' => 15],

            // ── BOTANAS ──────────────────────────────────────────────────────
            ['category' => 'Botanas', 'name' => 'Totis Nachis 90g',               'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Papitas Hot Chile',        'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Papitas con Sal',          'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Top Tops Salsa Negra',     'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Papitas Adobadas',         'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Top Tops Hot Chile',       'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Papitas Salsa',            'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Picante',                  'price' => 25],
            ['category' => 'Botanas', 'name' => 'Totis Twisters',                 'price' => 25],
            ['category' => 'Botanas', 'name' => 'Gomitas',                        'price' => 15],
            ['category' => 'Botanas', 'name' => 'Cacahuates',                     'price' => 15],
            ['category' => 'Botanas', 'name' => 'Mazapán Chico',                  'price' => 5],
            ['category' => 'Botanas', 'name' => 'Mazapán Grande',                 'price' => 10],
            ['category' => 'Botanas', 'name' => 'Maruchan Habanero',              'price' => 30],
            ['category' => 'Botanas', 'name' => 'Maruchan Camarón',               'price' => 30],
            ['category' => 'Botanas', 'name' => 'Maruchan Camarón con Piquín',    'price' => 30],

            // ── CERVEZA CERO ─────────────────────────────────────────────────
            ['category' => 'Cerveza Cero', 'name' => 'Modelo 0% Negra',   'price' => 35],
            ['category' => 'Cerveza Cero', 'name' => 'Modelo 0% Dorada',  'price' => 35],
            ['category' => 'Cerveza Cero', 'name' => 'Corona Cero',       'price' => 35],
            ['category' => 'Cerveza Cero', 'name' => 'Heineken 0',        'price' => 35],
            ['category' => 'Cerveza Cero', 'name' => 'Tecate Cero',       'price' => 35],

            // ── PROMOCIONES ──────────────────────────────────────────────────
            ['category' => 'Promociones', 'name' => '2L Indio o Tecate',           'price' => 100, 'description' => 'Michelada 2L — Indio o Tecate'],
            ['category' => 'Promociones', 'name' => '2L Dos Equis o Heineken',     'price' => 120, 'description' => 'Michelada 2L — Dos Equis o Heineken'],
            ['category' => 'Promociones', 'name' => '2L Corona o Victoria',        'price' => 130, 'description' => 'Michelada 2L — Corona o Victoria'],
            ['category' => 'Promociones', 'name' => 'Pitufos 1L',                  'price' => 100],
            ['category' => 'Promociones', 'name' => 'Cuba de a Litro (Torres 5)',  'price' => 140],
            ['category' => 'Promociones', 'name' => 'Cuba Bacardí',                'price' => 140],
            ['category' => 'Promociones', 'name' => 'Sangría Preparada con Alcohol',    'price' => 50],
            ['category' => 'Promociones', 'name' => 'Sangría Preparada sin Alcohol',    'price' => 30],

            // ── CIGARROS ─────────────────────────────────────────────────────
            ['category' => 'Cigarros', 'name' => 'Cigarro (pieza)', 'price' => 10],
        ];

        foreach ($products as $data) {
            $catName = $data['category'];
            unset($data['category']);

            Product::create([
                'product_category_id' => $cats[$catName]->id,
                'name'               => $data['name'],
                'price'              => $data['price'],
                'description'        => $data['description'] ?? null,
                'is_beer_product'    => $data['is_beer_product'] ?? false,
                'is_active'          => true,
            ]);
        }
    }
}
