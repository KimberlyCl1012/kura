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

            foreach ($request->file('images') as $i => $image) {
                $path = $image->store('wound_media', 'public');
                $rotation = $rotations[$i] ?? 0;

                Media::create([
                    'wound_id' => $woundId,
                    'appointment_id' => $appointmentId,
                    'content' => $path,
                    'position' => $rotation,
                    'type' => $type,
                ]);
            }

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
}
