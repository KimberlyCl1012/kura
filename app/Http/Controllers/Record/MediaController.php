<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $woundId = $request->query('wound_id');

        if (!$woundId) {
            return response()->json(['error' => 'Falta wound_id'], 400);
        }

        $media = \App\Models\Media::where('wound_id', $woundId)
            ->where('type', 1)
            ->get(['id', 'content', 'position']); // Solo columnas necesarias

        return response()->json($media);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'wound_id' => 'required|exists:wounds,id',
        ]);

        $woundId = $request->input('wound_id');
        $rotations = $request->input('rotations', []);

        foreach ($request->file('images') as $i => $image) {
            $path = $image->store('wound_media', 'public');

            $rotation = $rotations[$i] ?? 0;

            Media::create([
                'wound_id' => $woundId,
                'content' => $path,
                'position' => $rotation,
                'type' => 1,
            ]);
        }

        return response()->json(['message' => 'Imágenes guardadas correctamente.']);
    }
}
