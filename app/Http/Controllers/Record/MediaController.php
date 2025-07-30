<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            ->get(['id', 'content', 'position']);

        return response()->json($media);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'wound_id' => 'required|exists:wounds,id',
                'images' => 'required|array|min:1',
                'images.*' => 'image|max:5120', // Máx. 5MB por imagen
                'rotations' => 'nullable|array',
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

            return response()->json(['message' => 'Imágenes guardadas correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::info('Subir imagenes');
            Log::debug($e);
            Log::error('Error al subir imágenes de herida', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al guardar las imágenes.',
            ], 500);
        }
    }
}
