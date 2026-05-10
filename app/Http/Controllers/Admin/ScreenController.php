<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreenContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScreenController extends Controller
{
    public function index(): Response
    {
        $screens = ScreenContent::orderByDesc('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'type' => $s->type,
                'content' => $s->content,
                'is_active' => $s->is_active,
                'scheduled_at' => $s->scheduled_at?->toIso8601String(),
                'scheduled_end_at' => $s->scheduled_end_at?->toIso8601String(),
                'sort_order' => $s->sort_order,
            ]);

        return Inertia::render('Admin/Screens', ['screens' => $screens]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:youtube,image,stream,text',
            'content' => 'required|string|max:2000',
            'is_active' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after_or_equal:scheduled_at',
            'sort_order' => 'integer|min:0',
        ]);

        $data['created_by'] = auth()->id();

        ScreenContent::create($data);

        return back()->with('success', 'Contenido creado.');
    }

    public function update(Request $request, ScreenContent $screen)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:youtube,image,stream,text',
            'content' => 'required|string|max:2000',
            'is_active' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'scheduled_end_at' => 'nullable|date|after_or_equal:scheduled_at',
            'sort_order' => 'integer|min:0',
        ]);

        $screen->update($data);

        return back()->with('success', 'Contenido actualizado.');
    }

    public function destroy(ScreenContent $screen)
    {
        $screen->delete();

        return back()->with('success', 'Contenido eliminado.');
    }

    public function activate(ScreenContent $screen)
    {
        $screen->update(['is_active' => true]);

        return back()->with('success', 'Pantalla activada.');
    }

    public function deactivate(ScreenContent $screen)
    {
        $screen->update(['is_active' => false]);

        return back()->with('success', 'Pantalla desactivada.');
    }
}
