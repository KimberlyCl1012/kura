<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\MediaHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function destroy(MediaHistory $mediaHistory)
    {
        try {
            DB::beginTransaction();

            $old = [
                'id'               => $mediaHistory->id,
                'wound_history_id' => $mediaHistory->wound_history_id,
                'description'      => $mediaHistory->description,
                'content'          => $mediaHistory->content,
                'position'         => $mediaHistory->position,
                'type'             => $mediaHistory->type,
                'created_at'       => optional($mediaHistory->created_at)->toDateTimeString(),
            ];

            if (!empty($mediaHistory->content)) {
                try {
                    Storage::disk('public')->delete($mediaHistory->content);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo eliminar el archivo físico de media_history', [
                        'media_history_id' => $mediaHistory->id,
                        'path'             => $mediaHistory->content,
                        'error'            => $e->getMessage(),
                    ]);
                }
            }

            $mediaHistory->delete();

            AccessChangeLog::create([
                'user_id'      => auth()->id(),
                'logType'      => 'Media Antecedentes',
                'table'        => 'media_histories',
                'primaryKey'   => $old['id'],
                'secondaryKey' => $old['wound_history_id'],
                'changeType'   => 'delete',
                'fieldName'    => null,
                'oldValue'     => json_encode($old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'newValue'     => null,
            ]);

            DB::commit();

            return response()->json(['message' => 'Imagen eliminada correctamente.'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar media_history', [
                'media_history_id' => $mediaHistory->id ?? null,
                'error'            => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Ocurrió un error al eliminar la imagen.'], 500);
        }
    }
}
