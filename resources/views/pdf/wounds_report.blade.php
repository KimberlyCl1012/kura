@php
    $includeActive = (bool) ($filters['include_active'] ?? false);
    $includeFollowups = (bool) ($filters['include_followups'] ?? false);
    $includeConsultations = (bool) ($filters['include_consultations'] ?? false);
    $dateFrom = $filters['date_from'] ?? null;
    $dateTo = $filters['date_to'] ?? null;
    $evidenceMode = $evidenceMode ?? 'first_last';

    $fullName = $hr->full_name ?? null;
    $patientUuid = $hr->patient_uuid ?? null;
    $identification = $hr->identification ?? null;
    $dob = $hr->dateOfBirth ?? null;
    $ageYears = $hr->age_years ?? null;

    $institutionName = $hr->institution_display_name;
    $medicalInsurance = $hr->medicalInsurance ?? null;
    $religion = $hr->religion ?? null;
    $recordUuid = $hr->record_uuid ?? null;

    $today = \Carbon\Carbon::now()->format('Y-m-d H:i');

    function safe($v)
    {
        return $v !== null && $v !== '' ? $v : 'N/A';
    }

    $editorToPlain = function ($html, $withBullets = false) {
        $t = $html ?? '';
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $t = preg_replace('/<\s*br\s*\/@endphp/i', "\n", $t);
    $t = preg_replace('/<\/\s*p\s*>/i', "\n", $t);
    if ($withBullets) {
        $t = preg_replace('/<\s*li[^>]*>/i', '• ', $t);
    } else {
        $t = preg_replace('/<\s*li[^>]*>/i', '', $t);
    }
    $t = preg_replace('/<\/\s*li\s*>/i', "\n", $t);
    $t = strip_tags($t);
    $t = str_replace("\xC2\xA0", ' ', $t);
    $t = preg_replace("/[ \t]+/u", ' ', $t);
    $t = preg_replace("/\n{2,}/u", "\n", $t);
    return trim($t);
};
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte de Heridas</title>
    <style>
        @page {
            margin: 70px 35px 60px 35px;
        }

        /* top right bottom left */

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 6px 0;
        }

        h1 {
            font-size: 18px;
        }

        h2 {
            font-size: 14px;
        }

        h3 {
            font-size: 12.5px;
        }

        .logo {
            max-width: 70%;
            height: auto;
            display: block;
        }

        .muted {
            color: #666;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .row {
            display: flex;
            gap: 12px;
        }

        .col {
            flex: 1;
            min-width: 0;
        }

        .w-40 {
            width: 40%;
        }

        .w-60 {
            width: 60%;
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .grid3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #E43636;
            padding: 5px;
            word-break: break-word;
            border-radius: 5px;
        }

        th {
            background: #fcfcfc;
            text-align: left;
        }

        .section {
            margin-top: 10px;
        }

        .pill {
            display: inline-block;
            border: 1px solid #a0a0a0;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 10px;
        }

        .mb3 {
            margin-bottom: 3px;
        }

        .mb8 {
            margin-bottom: 8px;
        }

        .mb12 {
            margin-bottom: 12px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        /* Encabezado fijo */
        header {
            position: fixed;
            top: -45px;
            left: 0;
            right: 0;
            height: 20px;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #666;
        }

        .brand {
            font-weight: bold;
            color: #111;
        }

        /* Pie con número de página */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 35px;
            color: #666;
            font-size: 11px;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pageno:after {
            content: counter(page) " / " counter(pages);
        }

        /* Salto de página por herida (opcional) */
        .page-break {
            page-break-after: always;
        }

        .label {
            font-weight: bold;
            margin-right: 4px;
        }

        .value {
            font-weight: normal;
            color: #111;
            /* o #333 para un poco más suave */
        }

        .title-heads {
            background-color: #e43636;
            color: #ffffff;
            padding: 4px;
            border-radius: 3px;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- ENCABEZADO --}}
    <header>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%">
                        <img src="{{ public_path('build/img/logos/red/kura_1.svg') }}" class="logo" alt="Logo">
                    </th>
                    <th style="width: 50%; text-align:left;">
                        <div class="mb3">
                            <span class="label"><strong>Sitio:</strong></span>
                            <span class="value">{{ $hr->site_name ?? 'N/A' }}</span>
                        </div>
                        <div class="mb3">
                            <span class="label"><strong>Expediente:</strong></span>
                            <span class="value">{{ safe($recordUuid) }}</span>
                        </div>
                        <div class="mb3">
                            <span class="label"><strong>Paciente:</strong></span>
                            <span class="value">{{ safe($patientUuid) }} </span>
                        </div>
                        <div class="mb3">
                            <span class="label"><strong>Nombre:</strong></span>
                            <span class="value">{{ safe($fullName) }}</span>
                        </div>
                    </th>
                </tr>
            </thead>
        </table>
    </header>

    {{-- Footer --}}
    <footer>
        <div class="footer-inner">
            <div class="muted">Fecha: <span>{{ $today }}</span></div>
            <div class="muted">Generado por: <span>{{ $generatedBy }}</span></div>
            <div class="muted">Página <span class="pageno"></span></div>
        </div>
    </footer>

    {{-- CONTENIDO --}}
    <main>
        {{-- RESUMEN DE FILTROS --}}
        <div class="muted mb8" style="margin-top: 8rem;">

            @if ($includeActive)
                · <span class="pill">Heridas activas</span>
            @endif
            @if ($includeFollowups)
                · <span class="pill">Seguimiento</span>
            @endif
            @if ($includeConsultations)
                · <span class="pill">Consultas</span>
            @endif
            · <span class="pill">
                {{ $evidenceMode === 'all' ? 'Todas las evidencias fotograficas' : 'Primera - Última evidencia fotografica' }}
            </span>
            @if ($dateFrom || $dateTo)
                · <span class="pill">Rango: {{ $dateFrom ?? '—' }} a {{ $dateTo ?? '—' }} </span>
            @endif
        </div>

        {{-- CABECERA: PACIENTE + HR (de tu vista) --}}
        @if ($hr)
            <h2 class="title-heads">DATOS DEL PACIENTE</h2>
            <div class="section box">
                <div class="grid2">
                    <div>
                        <div class="mb3"><strong>Nombre:</strong> {{ safe($fullName) }}</div>
                        @if (isset($identification))
                            <div class="mb3"><strong>Identificación:</strong> {{ safe($identification) }}</div>
                        @endif
                        <div class="mb3"><strong>Fecha de nacimiento:</strong> {{ safe($dob) }}
                            @if ($ageYears !== null)
                                ({{ $ageYears }} años)
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="mb3"><strong>Institución de salud:</strong> {{ safe($institutionName) }}</div>
                        <div class="mb3"><strong>Seguro médico:</strong> {{ safe($medicalInsurance) }}</div>
                        <div class="mb3"><strong>Religión:</strong> {{ safe($religion) }}</div>
                    </div>
                </div>
            </div>

            <h2 class="title-heads">HISTORIA CLÍNICA</h2>
            <div class="box">
                <div class="grid2">
                    <div>
                        <div class="mb3"><strong>Medicamentos:</strong></div>
                        <div class="muted">{!! nl2br(e($editorToPlain($hr->medicines))) !!}</div>
                    </div>
                    <div>
                        <div class="mb3"><strong>Alergias:</strong></div>
                        <div class="muted">{!! nl2br(e($editorToPlain($hr->allergies, true))) !!}</div>
                    </div>
                </div>
                <div class="grid2">
                    <div>
                        <div class="mb3"><strong>Antecedentes Patológicos:</strong></div>
                        <div class="muted">{!! nl2br(e($editorToPlain($hr->pathologicalBackground))) !!}</div>
                    </div>
                    <div>
                        <div class="mb3"><strong>Antecedentes de Laboratorio:</strong></div>
                        <div class="muted">{!! nl2br(e($editorToPlain($hr->laboratoryBackground))) !!}</div>
                    </div>
                </div>
                <div>
                    <div class="mb3"><strong>Antecedentes de Alimentación:</strong></div>
                    <div class="muted">{!! nl2br(e($editorToPlain($hr->nourishmentBackground))) !!}</div>
                </div>
            </div>
        @endif

        {{-- HERIDAS --}}
        @foreach ($wounds as $w)
            <div class="section page-break">
                <h2>HERIDA(S)</h2>
                <table>
                    <tr>
                        <th>Ubicación</th>
                        <td>{{ $w->bodyLocation->name }} - {{ $w->bodySublocation->name }}</td>
                        <th>Tipo</th>
                        <td>{{ $w->woundType->name }} - {{ $w->woundSubtype->name }}</td>
                    </tr>
                    <tr>
                        <th>Fase</th>
                        <th>Fecha creación</th>
                        <td>{{ $w->woundCreationDate ?? $w->created_at->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <th>Fecha inicio</th>
                        <td>{{ $w->woundBeginDate }}</td>
                        <th>Fecha curación</th>
                        <td>{{ $w->woundHealthDate }}</td>
                    </tr>
                    <tr>
                        <th>Notas</th>
                        <td colspan="3">{{ $w->note }}</td>
                    </tr>
                </table>

                {{-- Tratamientos --}}
                @if ($w->treatments->isNotEmpty())
                    <h3>TRATAMIENTO</h3>
                    <table>
                        <tr>
                            <th>Descripción</th>
                            <th>Fecha inicio</th>
                            <th>mmHg</th>
                            <th>Estado</th>
                        </tr>
                        @foreach ($w->treatments as $t)
                            <tr>
                                <td>{{ $t->description }}</td>
                                <td>{{ $t->beginDate }}</td>
                                <td>{{ $t->mmhg }}</td>
                                <td>{{ $t->state ? 'Activo' : 'Inactivo' }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                {{-- Medidas --}}
                @if ($filters['include_followups'] ?? false)
                    <h3>MEDIDAS</h3>
                    <table>
                        <tr>
                            <th>Fecha</th>
                            <th>Largo</th>
                            <th>Ancho</th>
                            <th>Área</th>
                            <th>Prof.</th>
                            <th>Volumen</th>
                            <th>% Rojo</th>
                            <th>% Amarillo</th>
                            <th>% Negro</th>
                        </tr>
                        @foreach ($w->measurements as $m)
                            <tr>
                                <td>{{ $m->measurementDate }}</td>
                                <td>{{ $m->length }}</td>
                                <td>{{ $m->width }}</td>
                                <td>{{ $m->area }}</td>
                                <td>{{ $m->depth }}</td>
                                <td>{{ $m->volume }}</td>
                                <td>{{ $m->granulation_percent }}</td>
                                <td>{{ $m->slough_percent }}</td>
                                <td>{{ $m->necrosis_percent }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                {{-- Consulta --}}
                @if (($filters['include_consultations'] ?? false) && $w->appointment)
                    <h3>CONSULTA</h3>
                    <table>
                        <tr>
                            <th>Fecha</th>
                            <td>{{ $w->appointment->dateStartVisit }}</td>
                            <th>Tipo</th>
                            <td>{{ $w->appointment->typeVisit }}</td>
                        </tr>
                        <tr>
                            <th>Especialista</th>
                            <td colspan="3">{{ $w->appointment->kurator?->userDetail?->name }}
                                {{ $w->appointment->kurator?->userDetail?->fatherLastName }}</td>
                        </tr>
                    </table>
                @endif

                {{-- Evidencias --}}
                @php
                    $photos = $w->media;
                    if (($filters['evidence_mode'] ?? 'first_last') === 'first_last' && $photos->count() > 0) {
                        $photos = collect([$photos->first(), $photos->last()])->unique('id');
                    }
                @endphp
                @if ($photos->isNotEmpty())
                    <h3>EVIDENCIAS</h3>
                    <table>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Imagen</th>
                            <th>Descripción</th>
                            <th>Fecha creación</th>
                        </tr>
                        @foreach ($photos as $ph)
                            <tr>
                                <td>Herida #{{ $w->id }}</td>
                                <td>{{ $ph->type }}</td>
                                <td><img src="{{ public_path('storage/' . $ph->content) }}" class="photo"></td>
                                <td>{{ $ph->position }}</td>
                                <td>{{ $ph->created_at?->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        @endforeach

        {{-- Métodos y Submétodos (página separada) --}}
        <div class="section page-break">
            <h2>Métodos de Tratamiento</h2>
            <table>
                <tr>
                    <th>MÉTODO</th>
                </tr>
                @foreach ($treatmentMethods ?? [] as $m)
                    <tr>
                        <td>{{ $m->name }}</td>
                    </tr>
                @endforeach
            </table>

            <h2>Submétodos de Tratamiento</h2>
            <table>
                <tr>
                    <th>MÉTODO</th>
                    <th>SUBMÉTODO</th>
                </tr>
                @foreach ($treatmentSubmethods ?? [] as $s)
                    <tr>
                        <td>{{ $s->method->name }}</td>
                        <td>{{ $s->name }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </main>
</body>

</html>
