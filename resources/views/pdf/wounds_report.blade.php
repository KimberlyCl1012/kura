<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Heridas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        th { background-color: #f0f0f0; text-align: left; width: 30%; }
        h2, h3 { margin: 0 0 10px; }
        .section-title { margin-top: 15px; font-weight: bold; font-size: 13px; }
        hr { margin: 25px 0; border: none; border-top: 1px solid #aaa; }
        img { width: 200px; margin: 5px; }
    </style>
</head>
<body>
    <h2>Reporte de Heridas</h2>

    @foreach($wounds as $index => $wound)
        <h3>Herida #{{ $index + 1 }}</h3>

        {{-- Paciente --}}
        <div class="section-title">Paciente</div>
        <table>
            <tr><th>Nombre</th><td>{{ optional($wound->healthRecord->patient->userDetail)->name }} {{ optional($wound->healthRecord->patient->userDetail)->fatherLastName }}</td></tr>
            <tr><th>Fecha de nacimiento</th><td>{{ $wound->healthRecord->patient->dateOfBirth ?? 'N/A' }}</td></tr>
            <tr><th>Identificación</th><td>{{ $wound->healthRecord->patient->identification ?? 'N/A' }}</td></tr>
        </table>

        {{-- Consulta --}}
        <div class="section-title">Consulta</div>
        <table>
            <tr><th>Fecha</th><td>{{ $wound->appointment->dateStartVisit ?? 'N/A' }}</td></tr>
            <tr><th>Tipo de visita</th><td>{{ $wound->appointment->typeVisit ?? 'N/A' }}</td></tr>
        </table>

        {{-- Kurador --}}
        <div class="section-title">Kurador</div>
        <table>
            <tr><th>Nombre</th><td>{{ optional($wound->appointment->kurator->userDetail)->name }} {{ optional($wound->appointment->kurator->userDetail)->fatherLastName }}</td></tr>
            <tr><th>Especialidad</th><td>{{ $wound->appointment->kurator->specialty ?? 'N/A' }}</td></tr>
            <tr><th>Identificación</th><td>{{ $wound->appointment->kurator->identification ?? 'N/A' }}</td></tr>
        </table>

        {{-- Datos de la herida --}}
        <div class="section-title">Datos de la Herida</div>
        <table>
            <tr><th>Tipo</th><td>{{ $wound->woundType->name ?? 'N/A' }}</td></tr>
            <tr><th>Subtipo</th><td>{{ $wound->woundSubtype->name ?? 'N/A' }}</td></tr>
            <tr><th>Fase</th><td>{{ $wound->woundPhase->name ?? 'N/A' }}</td></tr>
            <tr><th>Ubicación</th><td>{{ $wound->bodyLocation->name ?? 'N/A' }}</td></tr>
            <tr><th>Sububicación</th><td>{{ $wound->bodySublocation->name ?? 'N/A' }}</td></tr>
            <tr><th>Fecha de creación</th><td>{{ $wound->created_at->format('Y-m-d') }}</td></tr>
            <tr><th>Antecedente</th><td>{{ $wound->woundBackground ? 'Sí' : 'No' }}</td></tr>
        </table>

        {{-- Signos clínicos --}}
        <div class="section-title">Signos Clínicos</div>
        <table>
            <tr><th>Edema</th><td>{{ $wound->edema }}</td></tr>
            <tr><th>Dolor</th><td>{{ $wound->dolor }}</td></tr>
            <tr><th>Exudado (cantidad)</th><td>{{ $wound->exudado_cantidad }}</td></tr>
            <tr><th>Exudado (tipo)</th><td>{{ $wound->exudado_tipo }}</td></tr>
            <tr><th>Olor</th><td>{{ $wound->olor }}</td></tr>
            <tr><th>Infección</th><td>{{ $wound->infeccion }}</td></tr>
            <tr><th>Escala EVA</th><td>{{ $wound->visual_scale }}</td></tr>
        </table>

        {{-- Evaluación vascular --}}
        <div class="section-title">Evaluación Vascular</div>
        <table>
            <tr><th>ITB Izquierdo</th><td>{{ $wound->ITB_izquierdo }}</td></tr>
            <tr><th>Pulso dorsal izquierdo</th><td>{{ $wound->pulse_dorsal_izquierdo }}</td></tr>
            <tr><th>Pulso tibial izquierdo</th><td>{{ $wound->pulse_tibial_izquierdo }}</td></tr>
            <tr><th>ITB Derecho</th><td>{{ $wound->ITB_derecho }}</td></tr>
            <tr><th>Pulso dorsal derecho</th><td>{{ $wound->pulse_dorsal_derecho }}</td></tr>
            <tr><th>Pulso tibial derecho</th><td>{{ $wound->pulse_tibial_derecho }}</td></tr>
        </table>

        {{-- Glucosa y nota --}}
        <div class="section-title">Otros</div>
        <table>
            <tr><th>Glucosa</th><td>{{ $wound->blood_glucose }}</td></tr>
            <tr><th>Nota médica</th><td>{{ $wound->note }}</td></tr>
        </table>

        {{-- Última medición --}}
        @if($wound->measurements->isNotEmpty())
            @php $m = $wound->measurements->first(); @endphp
            <div class="section-title">Última Medición ({{ $m->measurementDate }})</div>
            <table>
                <tr><th>Longitud</th><td>{{ $m->length }} cm</td></tr>
                <tr><th>Anchura</th><td>{{ $m->width }} cm</td></tr>
                <tr><th>Área</th><td>{{ $m->area }} cm²</td></tr>
                <tr><th>Profundidad</th><td>{{ $m->depth }} cm</td></tr>
                <tr><th>Volumen</th><td>{{ $m->volume }} cm³</td></tr>
                <tr><th>Tuneleo</th><td>{{ $m->tunneling }}</td></tr>
                <tr><th>Socavamiento</th><td>{{ $m->undermining }}</td></tr>
                <tr><th>Granulación</th><td>{{ $m->granulation_percent }}%</td></tr>
                <tr><th>Esfacelo</th><td>{{ $m->slough_percent }}%</td></tr>
                <tr><th>Necrosis</th><td>{{ $m->necrosis_percent }}%</td></tr>
                <tr><th>Epitelización</th><td>{{ $m->epithelialization_percent }}%</td></tr>
            </table>
        @endif

        {{-- Expediente médico --}}
        <div class="section-title">Expediente Médico</div>
        <table>
            <tr><th>Medicamentos</th><td>{!! $wound->healthRecord->medicines !!}</td></tr>
            <tr><th>Alergias</th><td>{!! $wound->healthRecord->allergies !!}</td></tr>
            <tr><th>Antecedentes patológicos</th><td>{!! $wound->healthRecord->pathologicalBackground !!}</td></tr>
            <tr><th>Laboratorio</th><td>{!! $wound->healthRecord->laboratoryBackground !!}</td></tr>
            <tr><th>Nutrimentales</th><td>{!! $wound->healthRecord->nourishmentBackground !!}</td></tr>
            <tr><th>Seguro médico</th><td>{{ $wound->healthRecord->medicalInsurance }}</td></tr>
            <tr><th>Institución</th><td>{{ $wound->healthRecord->health_institution }}</td></tr>
            <tr><th>Religión</th><td>{{ $wound->healthRecord->religion }}</td></tr>
        </table>


        <hr>
    @endforeach
</body>
</html>
