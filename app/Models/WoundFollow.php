<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoundFollow extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'wound_id',
        'appointment_id',
        'wound_phase_id',
        'wound_type_id',
        'wound_subtype_id',
        'body_location_id',
        'body_sublocation_id',
        'grade_foot',
        'valoracion',
        'MESI',
        'borde',
        'edema',
        'dolor',
        'exudado_cantidad',
        'exudado_tipo',
        'olor',
        'piel_perilesional',
        'infeccion',
        'tipo_dolor',
        'duracion_dolor',
        'visual_scale',
        'ITB_izquierdo',
        'pulse_dorsal_izquierdo',
        'pulse_tibial_izquierdo',
        'pulse_popliteo_izquierdo',
        'ITB_derecho',
        'pulse_dorsal_derecho',
        'pulse_tibial_derecho',
        'pulse_popliteo_derecho',
        'monofilamento',
        'blood_glucose',
        'measurementDate',
        'length',
        'width',
        'area',
        'depth',
        'volume',
        'tunneling',
        'undermining',
        'granulation_percent',
        'slough_percent',
        'necrosis_percent',
        'epithelialization_percent',
        'note',
        'description',
    ];

    protected $casts = [
        'piel_perilesional' => 'array',
        'infeccion' => 'array',
    ];
}
