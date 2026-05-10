<?php

namespace App\Http\Controllers;

use App\Models\FloorPlanConfig;
use App\Models\GameTable;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class FloorPlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('FloorPlan/Index', [
            'config' => $this->getConfig(),
            'tables' => $this->getTables(),
        ]);
    }

    public function poll(): JsonResponse
    {
        return response()->json([
            'tables' => $this->getTables(),
        ]);
    }

    private function getConfig(): array
    {
        $config = FloorPlanConfig::current(session('branch_id'));

        return [
            'canvas_width' => $config->canvas_width,
            'canvas_height' => $config->canvas_height,
            'background_image_path' => $config->background_image_path
                ? asset('storage/'.$config->background_image_path)
                : null,
        ];
    }

    private function getTables(): array
    {
        return GameTable::with(['activeSession'])
            ->where('is_active', true)
            ->where('branch_id', session('branch_id'))
            ->get()
            ->map(function (GameTable $t) {
                $session = $t->activeSession;

                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'type' => $t->type,
                    'map_x' => $t->map_x ?? 50,
                    'map_y' => $t->map_y ?? 50,
                    'map_width' => $t->map_width,
                    'map_height' => $t->map_height,
                    'status' => $t->is_active ? ($session ? 'occupied' : 'available') : 'inactive',
                    'session_id' => $session?->id,
                    'opened_at' => $session?->opened_at->toIso8601String(),
                    'minutes_open' => $session ? (int) $session->opened_at->diffInMinutes(now()) : null,
                ];
            })
            ->toArray();
    }
}
