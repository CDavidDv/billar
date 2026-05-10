<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Inventory::with('product.category')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Categoría',
            'Stock actual',
            'Unidad',
            'Stock mínimo',
            'Estado',
        ];
    }

    public function map($inv): array
    {
        $isLow = $inv->min_stock > 0 && $inv->quantity <= $inv->min_stock;

        return [
            $inv->product?->name ?? '',
            $inv->product?->category?->name ?? '',
            number_format((float) $inv->quantity, 3),
            $inv->unit,
            number_format((float) $inv->min_stock, 3),
            $isLow ? 'BAJO' : 'OK',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
