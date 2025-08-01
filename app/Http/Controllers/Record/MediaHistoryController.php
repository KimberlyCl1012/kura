<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\MediaHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaHistoryController extends Controller
{
    public function index(Request $request)
    {
        $woundHistoryId = $request->query('wound_history_id');

        if (!$woundHistoryId) {
            return response()->json(['error' => 'Falta wound_history_id'], 400);
        }

        $media = MediaHistory::where('wound_history_id', $woundHistoryId)
            ->where('type', 'Antecedente')
            ->get(['id', 'content', 'position']);

        return response()->json($media);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'wound_history_id' => 'required|exists:wound_histories,id',
                'images' => 'required|array|min:1',
                'rotations' => 'nullable|array',
            ]);

            $woundHistoryId = $request->input('wound_history_id');
            $rotations = $request->input('rotations', []);

            foreach ($request->file('images') as $i => $image) {
                $path = $image->store('wound_media', 'public');
                $rotation = $rotations[$i] ?? 0;

                MediaHistory::create([
                    'wound_history_id' => $woundHistoryId,
                    'content' => $path,
                    'position' => $rotation,
                    'type' => 'Antecedente',
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
