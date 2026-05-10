<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConfigController extends Controller
{
    public function index(): Response
    {
        $configs = AppConfiguration::orderBy('group')->orderBy('label')->get()
            ->groupBy('group')
            ->map(fn ($group) => $group->map(fn ($c) => [
                'id' => $c->id,
                'key' => $c->key,
                'value' => $c->value,
                'type' => $c->type,
                'label' => $c->label,
                'description' => $c->description,
            ]));

        return Inertia::render('Admin/Config/Index', ['configs' => $configs]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'configs' => 'required|array',
            'configs.*.key' => 'required|string|exists:app_configurations,key',
            'configs.*.value' => 'required|string|max:500',
        ]);

        foreach ($data['configs'] as $item) {
            AppConfiguration::set($item['key'], $item['value']);
        }

        return back()->with('success', 'Configuración guardada.');
    }
}
