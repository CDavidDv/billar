<?php

namespace App\Http\Controllers;

use App\Models\ScreenContent;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Display/Kiosk', [
            'initial' => $this->getActiveContent(),
        ]);
    }

    public function content(): JsonResponse
    {
        return response()->json($this->getActiveContent());
    }

    private function getActiveContent(): array
    {
        $item = ScreenContent::currentlyActive()
            ->orderByDesc('sort_order')
            ->first();

        if (! $item) {
            return ['type' => 'text', 'content' => 'Billar', 'title' => 'Bienvenido'];
        }

        return [
            'id' => $item->id,
            'type' => $item->type,
            'content' => $item->content,
            'title' => $item->title,
        ];
    }
}
