<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FloorPlanConfig;
use App\Models\GameTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FloorPlanController extends Controller
{
    public function edit(): Response
    {
        $branchId = session('branch_id');
        $config = FloorPlanConfig::current($branchId);
        $tables = GameTable::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'map_x', 'map_y', 'map_width', 'map_height', 'is_active']);

        return Inertia::render('Admin/FloorPlan/Editor', [
            'config' => [
                'canvas_width' => $config->canvas_width,
                'canvas_height' => $config->canvas_height,
                'background_image_url' => $config->background_image_path
                    ? asset('storage/'.$config->background_image_path)
                    : null,
            ],
            'tables' => $tables,
        ]);
    }

    public function savePositions(Request $request)
    {
        $branchId = session('branch_id');

        $data = $request->validate([
            'tables' => 'required|array',
            'tables.*.id' => 'required|exists:game_tables,id',
            'tables.*.map_x' => 'required|integer|min:0',
            'tables.*.map_y' => 'required|integer|min:0',
            'tables.*.map_width' => 'required|integer|min:20',
            'tables.*.map_height' => 'required|integer|min:20',
        ]);

        foreach ($data['tables'] as $tableData) {
            GameTable::where('id', $tableData['id'])
                ->where('branch_id', $branchId)
                ->update([
                    'map_x' => $tableData['map_x'],
                    'map_y' => $tableData['map_y'],
                    'map_width' => $tableData['map_width'],
                    'map_height' => $tableData['map_height'],
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'canvas_width' => 'required|integer|min:400|max:3000',
            'canvas_height' => 'required|integer|min:300|max:2000',
            'background_image' => 'nullable|file|image|max:5120',
        ]);

        $config = FloorPlanConfig::current(session('branch_id'));
        $config->canvas_width = $data['canvas_width'];
        $config->canvas_height = $data['canvas_height'];
        $config->updated_by = auth()->id();

        if ($request->hasFile('background_image')) {
            if ($config->background_image_path) {
                Storage::disk('public')->delete($config->background_image_path);
            }
            $path = $request->file('background_image')->store('floor-plan', 'public');
            $config->background_image_path = $path;
        }

        $config->save();

        return back()->with('success', 'Configuración del mapa guardada.');
    }
}
