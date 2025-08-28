<?php

namespace App\Http\Controllers\Record;

use App\Http\Controllers\Controller;
use App\Models\AccessChangeLog;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    protected function logChange(array $data): void
    {
        AccessChangeLog::create([
            'user_id'      => auth()->id(),
            'logType'      => $data['logType'],
            'table'        => $data['table'],
            'primaryKey'   => $data['primaryKey'] ?? null,
            'secondaryKey' => $data['secondaryKey'] ?? null,
            'changeType'   => $data['changeType'],
            'fieldName'    => $data['fieldName'] ?? null,
            'oldValue'     => $data['oldValue'] ?? null,
            'newValue'     => $data['newValue'] ?? null,
        ]);
    }

    public function index(Request $request)
    {
        $woundId = $request->query('wound_id');
        $appointmentId = $request->query('appointment_id');
        $type = $request->query('type');

        if (!$woundId) {
            return response()->json(['error' => 'Falta wound_id'], 400);
        }

        $query = Media::where('wound_id', $woundId);

        if ($appointmentId) {
            $query->where('appointment_id', $appointmentId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $media = $query->get(['id', 'content', 'position']);

        return response()->json($media);
    }


    public function upload(Request $request)
    {
        try {
            $request->validate([
                'wound_id' => 'required|exists:wounds,id',
                'appointment_id' => 'nullable|exists:appointments,id',
                'images' => 'required|array|min:1',
                'rotations' => 'nullable|array',
                'type' => 'required|String',
            ]);

            $woundId = $request->input('wound_id');
            $appointmentId = $request->input('appointment_id');
            $rotations = $request->input('rotations', []);
            $type = $request->input('type');

            DB::beginTransaction();

            $createdRows = [];

            foreach ($request->file('images') as $i => $image) {
                $path = $image->store('wound_media', 'public');
                $rotation = $rotations[$i] ?? 0;

                $media = Media::create([
                    'wound_id'       => $woundId,
                    'appointment_id' => $appointmentId,
                    'content'        => $path,
                    'position'       => $rotation, // tu campo de rotación
                    'type'           => $type,
                ]);

                $createdRows[] = [
                    'id'             => $media->id,
                    'wound_id'       => (int)$woundId,
                    'appointment_id' => $appointmentId ? (int)$appointmentId : null,
                    'content'        => $path,
                    'position'       => $rotation,
                    'type'           => $type,
                    'created_at'     => $media->created_at?->toDateTimeString(),
                ];
            }

            $this->logChange([
                'logType'      => 'Media',
                'table'        => 'media',
                'primaryKey'   => null,
                'secondaryKey' => $woundId,
                'changeType'   => 'bulk-create',
                'newValue'     => json_encode([
                    'count'   => count($createdRows),
                    'items'   => $createdRows,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            DB::commit();

            return response()->json(['message' => 'Imágenes guardadas correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::info('Subir imágenes');
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

    public function destroy(Media $media)
    {
        try {
            DB::beginTransaction();

            $old = [
                'id'             => $media->id,
                'wound_id'       => $media->wound_id,
                'appointment_id' => $media->appointment_id,
                'content'        => $media->content,
                'position'       => $media->position,
                'type'           => $media->type,
                'created_at'     => optional($media->created_at)->toDateTimeString(),
            ];

            if (!empty($media->content)) {
                try {
                    Storage::disk('public')->delete($media->content);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo eliminar el archivo físico de media', [
                        'media_id' => $media->id,
                        'path'     => $media->content,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            $media->delete();

            AccessChangeLog::create([
                'user_id'      => auth()->id(),
                'logType'      => 'Media',
                'table'        => 'media',
                'primaryKey'   => $old['id'],
                'secondaryKey' => $old['wound_id'],
                'changeType'   => 'delete',
                'fieldName'    => null,
                'oldValue'     => json_encode($old, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'newValue'     => null,
            ]);

            DB::commit();

            return response()->json(['message' => 'Imagen eliminada correctamente.'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al eliminar media', [
                'media_id' => $media->id ?? null,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error al eliminar la imagen.',
            ], 500);
        }
    }
}
