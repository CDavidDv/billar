<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    public function model(array $row): ?Product
    {
        $categoryName = trim($row['categoria'] ?? '');
        $category = ProductCategory::firstOrCreate(['name' => $categoryName]);

        return Product::updateOrCreate(
            ['name' => trim($row['nombre'])],
            [
                'product_category_id' => $category->id,
                'price' => (float) ($row['precio'] ?? 0),
                'description' => trim($row['descripcion'] ?? ''),
                'is_active' => true,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'precio' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nombre.required' => 'La columna "nombre" es obligatoria.',
            'precio.required' => 'La columna "precio" es obligatoria.',
            'precio.numeric' => 'El precio debe ser un número.',
            'categoria.required' => 'La columna "categoria" es obligatoria.',
        ];
    }
}
