<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $from,
        private string $to
    ) {}

    public function collection()
    {
        return Order::with(['table', 'createdBy', 'items'])
            ->whereDate('closed_at', '>=', $this->from)
            ->whereDate('closed_at', '<=', $this->to)
            ->where('status', 'closed')
            ->orderBy('closed_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Fecha cierre',
            'Mesa',
            'Subtotal productos',
            'Tiempo',
            'Descuento',
            'Total',
            'Método pago',
            'Cajero',
        ];
    }

    public function map($order): array
    {
        return [
            $order->closed_at?->format('Y-m-d H:i'),
            $order->table?->name ?? 'Barra',
            number_format((float) $order->subtotal, 2),
            number_format((float) $order->time_cost, 2),
            number_format((float) $order->discount, 2),
            number_format((float) $order->total, 2),
            $order->payment_method ?? '',
            $order->createdBy?->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
