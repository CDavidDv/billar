<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use App\Services\StatisticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private StatisticsService $stats,
        private InventoryService $inventory,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'summary' => $this->stats->getSummary(),
            'weekly' => $this->stats->getWeeklySales(),
            'topProducts' => $this->stats->getTopProducts(10),
            'heatmap' => $this->stats->getHeatmapData(),
            'tableOccupancy' => $this->stats->getTableOccupancy(),
            'lowStock' => $this->inventory->getLowStockProducts()->map(fn ($inv) => [
                'product' => $inv->product->name,
                'category' => $inv->product->category?->name,
                'quantity' => (float) $inv->quantity,
                'min_stock' => (float) $inv->min_stock,
                'unit' => $inv->unit,
            ]),
        ]);
    }
}
