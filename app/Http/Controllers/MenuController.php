<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'products' => Product::where('product_category_id', $cat->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'description' => $p->description,
                        'price' => (float) $p->price,
                        'image_url' => $p->image_url,
                        'is_available' => $p->is_available,
                    ]),
            ]);

        return Inertia::render('Menu/Index', [
            'categories' => $categories,
            'business_name' => 'Billar',
            'logo_text' => 'B',
        ]);
    }
}
