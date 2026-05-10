<?php

namespace App\Http\Controllers;

use App\Models\SongRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SongRequestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Menu/Songs', [
            'recent' => SongRequest::where('status', 'approved')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'artist' => $s->artist,
                    'votes' => $s->votes,
                ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'artist' => 'nullable|string|max:200',
        ]);

        $data['status'] = 'pending';
        $data['votes'] = 1;
        $data['ip_address'] = $request->ip();

        $song = SongRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Canción solicitada. Próxima en reproducirse.',
            'song' => $song,
        ]);
    }

    public function vote(Request $request, SongRequest $song): JsonResponse
    {
        $song->increment('votes');

        return response()->json([
            'success' => true,
            'votes' => $song->votes,
        ]);
    }
}
