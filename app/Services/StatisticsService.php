<?php

namespace App\Services;

use App\Models\Caguama;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function getDailySales(?Carbon $date = null): array
    {
        $date = $date ?? today();

        $orders = Order::whereDate('closed_at', $date)
            ->where('status', 'closed')
            ->get();

        $byHour = [];
        for ($h = 0; $h < 24; $h++) {
            $byHour[$h] = 0;
        }

        foreach ($orders as $order) {
            $hour = $order->closed_at->hour;
            $byHour[$hour] += (float) $order->total;
        }

        return [
            'total' => $orders->sum('total'),
            'count' => $orders->count(),
            'avg_ticket' => $orders->count() > 0 ? round($orders->sum('total') / $orders->count(), 2) : 0,
            'by_hour' => array_values($byHour),
        ];
    }

    public function getWeeklySales(): array
    {
        $days = [];
        $totals = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $row = Order::whereDate('closed_at', $date)
                ->where('status', 'closed')
                ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as cnt')
                ->first();

            $days[] = $date->format('D d/m');
            $totals[] = (float) ($row->total ?? 0);
            $counts[] = (int) ($row->cnt ?? 0);
        }

        return ['days' => $days, 'totals' => $totals, 'counts' => $counts];
    }

    public function getTopProducts(int $limit = 10): array
    {
        return OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', fn ($q) => $q->where('status', 'closed'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->product_name,
                'qty' => (int) $r->total_qty,
                'revenue' => (float) $r->total_revenue,
            ])
            ->toArray();
    }

    public function getHeatmapData(): array
    {
        $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $rows = Order::where('status', 'closed')
            ->whereNotNull('closed_at')
            ->selectRaw('DAYOFWEEK(closed_at) - 1 as dow, HOUR(closed_at) as hour, SUM(total) as total_sales')
            ->groupByRaw('DAYOFWEEK(closed_at), HOUR(closed_at)')
            ->get();

        $matrix = [];
        foreach ($days as $i => $dayName) {
            $data = [];
            for ($h = 0; $h < 24; $h++) {
                $row = $rows->first(fn ($r) => (int) $r->dow === $i && (int) $r->hour === $h);
                $data[] = ['x' => (string) $h.'h', 'y' => $row ? (float) $row->total_sales : 0];
            }
            $matrix[] = ['name' => $dayName, 'data' => $data];
        }

        return $matrix;
    }

    public function getTableOccupancy(): array
    {
        return DB::table('table_sessions')
            ->join('game_tables', 'game_tables.id', '=', 'table_sessions.game_table_id')
            ->where('table_sessions.status', 'closed')
            ->selectRaw('game_tables.name, COUNT(*) as sessions, ROUND(AVG(TIMESTAMPDIFF(MINUTE, table_sessions.opened_at, table_sessions.closed_at)), 1) as avg_minutes')
            ->groupBy('game_tables.id', 'game_tables.name')
            ->orderByDesc('sessions')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name,
                'sessions' => (int) $r->sessions,
                'avg_minutes' => (float) $r->avg_minutes,
            ])
            ->toArray();
    }

    public function getSummary(): array
    {
        $today = $this->getDailySales();
        $lowStock = Inventory::with('product')
            ->whereRaw('quantity <= min_stock')
            ->where('min_stock', '>', 0)
            ->count();

        $activeTables = DB::table('table_sessions')
            ->where('status', 'active')
            ->count();

        $activeCaguamas = Caguama::where('status', 'active')->count();

        return [
            'sales_today' => $today['total'],
            'orders_today' => $today['count'],
            'avg_ticket' => $today['avg_ticket'],
            'active_tables' => $activeTables,
            'active_caguamas' => $activeCaguamas,
            'low_stock_count' => $lowStock,
        ];
    }
}
