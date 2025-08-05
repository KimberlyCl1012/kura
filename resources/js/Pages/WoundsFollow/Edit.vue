<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, computed, onMounted, watch } from "vue";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import MultiSelect from "primevue/multiselect";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Editor from "primevue/editor";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    woundsType: Array,
    woundsSubtype: Array,
    woundsPhase: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    wound: Object,
    measurement: Object,
    existingImages: Array,
    treatmentMethods: Array,
    treatmentSubmethods: Array,
    woundsInAppointment: Number,
});

const currentStep = ref(1);
function goToStep(step) {
    currentStep.value = step;
}

// Catálogos simples
const bordes = ref([{ name: "Adherido" }, { name: "No adherido" }, { name: "Enrollado" }, { name: "Epitalizado" }]);
const valoracion = ref([{ name: "MESI" }, { name: "No aplica" }]);
const edema = ref([{ name: "+++" }, { name: "++" }, { name: "+" }, { name: "No aplica" }]);
const dolor = ref([{ name: "En reposo" }, { name: "Con movimiento" }, { name: "Ninguno" }]);
const exudado_cantidad = ref([{ name: "Abundante" }, { name: "Moderado" }, { name: "Bajo" }]);
const exudado_tipo = ref([{ name: "Seroso" }, { name: "Purulento" }, { name: "Hemático" }, { name: "Serohemático" }]);
const olor = ref([{ name: "Mal olor" }, { name: "No aplica" }]);
const piel_perisional = ref([
    { name: "Eritema" }, { name: "Escoriación" }, { name: "Maceración" }, { name: "Reseca" },
    { name: "Equimosis" }, { name: "Indurada" }, { name: "Queratosis" }, { name: "Integra" },
    { name: "Hiperpigmentada" }
]);
const infeccion = ref([
    { name: "Celulitis" }, { name: "Pirexia" }, { name: "Aumento del dolor" },
    { name: "Rapida extensión del area ulcerada" }, { name: "Mal olor" }, { name: "Incremento del exudado" },
    { name: "Eritema" }, { name: "No aplica" }
]);
const tipo_dolor = ref([{ name: "Nociceptivo" }, { name: "Neuropático" }]);

const formFollow = ref({
    wound_id: props.wound?.id,
    appointment_id: props.wound?.appointment_id,
    wound_phase_id: null,
    wound_type_id: props.wound?.wound_type_id,
    wound_subtype_id: props.wound?.wound_subtype_id,
    body_location_id: props.wound?.body_location_id,
    body_sublocation_id: props.wound?.body_sublocation_id,
    wound_type_other: props.wound?.wound_type_other,
    grade_foot: props.wound?.grade_foot,
    valoracion: null,
    MESI: null,
    borde: null,
    edema: null,
    dolor: null,
    exudado_cantidad: null,
    exudado_tipo: null,
    olor: null,
    piel_perisional: null,
    infeccion: null,
    tipo_dolor: null,
    visual_scale: null,
    ITB_izquierdo: null,
    pulse_dorsal_izquierdo: null,
    pulse_tibial_izquierdo: null,
    pulse_popliteo_izquierdo: null,
    ITB_derecho: null,
    pulse_dorsal_derecho: null,
    pulse_tibial_derecho: null,
    pulse_popliteo_derecho: null,
    blood_glucose: null,
    measurementDate: null,
    length: null,
    width: null,
    area: null,
    depth: null,
    volume: null,
    tunneling: null,
    undermining: null,
    granulation_percent: null,
    slough_percent: null,
    necrosis_percent: null,
    epithelialization_percent: null,
    note: null,
    description: null,
    methods: [],
    submethodsByMethod: {},
});

const submittedFollow = ref(false);
const isSavingFollow = ref(false);
const errors = ref({});
const showConfirmFinishDialog = ref(false);

const saveFollow = () => {
    submittedFollow.value = true;
    isSavingFollow.value = true;
    errors.value = {};

    // Formatear fecha
    const payload = {
        ...formFollow.value,
        measurementDate: formFollow.value.measurementDate
            ? new Date(formFollow.value.measurementDate).toISOString().split("T")[0]
            : null,
    };

    router.post("/wound_follows", payload, {
        onSuccess: () => {
            isSavingFollow.value = false;
        },
        onError: (err) => {
            errors.value = err;
            isSavingFollow.value = false;
        },
    });
};

// Área y volumen automáticos
watch(() => [formFollow.value.length, formFollow.value.width], ([length, width]) => {
    const l = parseFloat(length);
    const w = parseFloat(width);
    if (!isNaN(l) && !isNaN(w)) {
        formFollow.value.area = (l * w).toFixed(2);
    } else {
        formFollow.value.area = '';
    }
    if (formFollow.value.depth) {
        const d = parseFloat(formFollow.value.depth);
        if (!isNaN(d)) {
            formFollow.value.volume = (l * w * d).toFixed(2);
        }
    }
});

watch(() => formFollow.value.depth, (depth) => {
    const d = parseFloat(depth);
    const a = parseFloat(formFollow.value.area);
    formFollow.value.volume = (!isNaN(d) && !isNaN(a)) ? (a * d).toFixed(2) : '';
});


// Validación de escala visual
function onVisualScaleInput(event) {
    const input = event.target.value.replace("/10", "");
    let number = parseInt(input);
    if (!isNaN(number)) {
        if (number < 1) number = 1;
        if (number > 10) number = 10;
        formFollow.value.visual_scale = `${number}/10`;
    } else {
        formFollow.value.visual_scale = "";
    }
}


// Computed: filtrar métodos seleccionados
const selectedMethodsWithSubmethods = computed(() => {
    return props.treatmentMethods.filter(method =>
        formFollow.value.methods.includes(method.id)
    );
});

</script>


<template>
    <AppLayout title="Heridas - Seguimiento">
        <div class="card max-w-6xl mx-auto min-h-screen flex flex-col">
            <!-- Navegación -->
            <nav class="p-steps" aria-label="Steps" role="list">
                <div class="p-step" :class="{ 'p-step-active': currentStep === 1 }" role="listitem"
                    @click="goToStep(1)">
                    <div class="p-step-number">1</div>
                    <div class="p-step-title">Datos de la herida</div>
                </div>
                <div class="p-step" :class="{ 'p-step-active': currentStep === 2 }" role="listitem"
                    @click="goToStep(2)">
                    <div class="p-step-number">2</div>
                    <div class="p-step-title">Evaluación de la herida</div>
                </div>
            </nav>

            <!-- Contenido Paso 1 -->
            <section class="flex-grow border rounded p-4">
              
                <div v-show="currentStep === 2">
                    <div
                        class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                        <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Seguimiento de la herida</h2>

                        <form @submit.prevent="saveFollow" class="flex flex-col flex-grow overflow-auto">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <!-- Fase -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fase de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formFollow.wound_phase_id" :options="props.woundsPhase"
                                        optionLabel="name" placeholder="Seleccione una fase" optionValue="id" filter
                                        class="w-full min-w-0" :class="{
                                            'p-invalid': submittedFollow && !formFollow.wound_phase_id,
                                        }" />
                                    <small v-if="submittedFollow && !formFollow.wound_phase_id" class="text-red-500">
                                        Debe seleccionar la fase.
                                    </small>
                                </div>
                            </div>


                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Evaluación de la herida
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <template v-if="
                                    [
                                        18,
                                        19,
                                        20,
                                        21,
                                        22,
                                        23,
                                        24,
                                        25,
                                        26,
                                        27,
                                        28,
                                        29,
                                        30,
                                        31,
                                        32,
                                        33,
                                    ].includes(formFollow.body_location_id)
                                ">
                                    <!-- Campos vasculares -->
                                    <div class="col-span-full">
                                        <h3 class="text-lg font-semibold text-gray-700 mb-2">
                                            Valoración vascular (solo aplica en heridas en pierna)
                                        </h3>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Índice tobillo brazo Manual
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <Select id="valoracion" v-model="formFollow.valoracion" :options="valoracion"
                                            filter optionLabel="name" optionValue="name" class="w-full min-w-0"
                                            placeholder="Seleccione una opción" />
                                        <small v-if="errors.valoracion" class="text-red-500">{{
                                            errors.valoracion
                                        }}</small>
                                    </div>

                                    <div v-if="['MESI'].includes(formFollow.valoracion)">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            MESI <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="MESI" v-model="formFollow.MESI" class="w-full min-w-0" />
                                        <small v-if="errors.MESI" class="text-red-500">{{
                                            errors.MESI
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_izquierdo" v-model="formFollow.ITB_izquierdo"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.ITB_izquierdo" class="text-red-500">{{
                                            errors.ITB_izquierdo
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_derecho" v-model="formFollow.ITB_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.ITB_derecho" class="text-red-500">{{
                                            errors.ITB_derecho
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_izquierdo"
                                            v-model="formFollow.pulse_dorsal_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_dorsal_izquierdo" class="text-red-500">{{
                                            errors.pulse_dorsal_izquierdo
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_izquierdo"
                                            v-model="formFollow.pulse_popliteo_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_popliteo_izquierdo" class="text-red-500">{{
                                            errors.pulse_popliteo_izquierdo
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_izquierdo"
                                            v-model="formFollow.pulse_tibial_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_tibial_izquierdo" class="text-red-500">{{
                                            errors.pulse_tibial_izquierdo
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio derecho
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_derecho" v-model="formFollow.pulse_dorsal_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.pulse_dorsal_derecho" class="text-red-500">{{
                                            errors.pulse_dorsal_derecho
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_derecho"
                                            v-model="formFollow.pulse_popliteo_derecho" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_popliteo_derecho" class="text-red-500">{{
                                            errors.pulse_popliteo_derecho
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior derecho
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_derecho" v-model="formFollow.pulse_tibial_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.pulse_tibial_derecho" class="text-red-500">{{
                                            errors.pulse_tibial_derecho
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Monofilamento <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="monofilamento" v-model="formFollow.monofilamento"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.monofilamento" class="text-red-500">{{
                                            errors.monofilamento
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Nivel de glucosa en sangre
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="blood_glucose" v-model="formFollow.blood_glucose"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.blood_glucose" class="text-red-500">{{
                                            errors.blood_glucose
                                        }}</small>
                                    </div>
                                </template>

                                <div class="col-span-full">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">
                                        Información de la herida
                                    </h3>
                                </div>


                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Edema <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="edema" v-model="formFollow.edema" :options="edema" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.edema" class="text-red-500">{{
                                        errors.edema
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="dolor" v-model="formFollow.dolor" :options="dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.dolor" class="text-red-500">{{
                                        errors.dolor
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Tipo de dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="tipo_dolor" v-model="formFollow.tipo_dolor" :options="tipo_dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.tipo_dolor" class="text-red-500">{{
                                        errors.tipo_dolor
                                    }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Escala Visual Analógica (EVA)
                                        <span class="text-red-600">*</span>
                                    </label>
                                    <InputText id="visual_scale" v-model="formFollow.visual_scale"
                                        class="w-full min-w-0" :class="{
                                            'p-invalid': submittedFollow && !formFollow.visual_scale,
                                        }" placeholder="Ej: 3/10" @input="onVisualScaleInput" />
                                    <small v-if="errors.visual_scale" class="text-red-500">{{
                                        errors.visual_scale
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Tipo) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_tipo" v-model="formFollow.exudado_tipo" :options="exudado_tipo"
                                        filter optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_tipo" class="text-red-500">{{
                                        errors.exudado_tipo
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Cantidad) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_cantidad" v-model="formFollow.exudado_cantidad"
                                        :options="exudado_cantidad" filter optionLabel="name" optionValue="name"
                                        class="w-full min-w-0" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_cantidad" class="text-red-500">{{
                                        errors.exudado_cantidad
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Infección <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="infeccion" v-model="formFollow.infeccion" :options="infeccion" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.infeccion" class="text-red-500">{{
                                        errors.infeccion
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Olor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="olor" v-model="formFollow.olor" :options="olor" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.olor" class="text-red-500">{{ errors.olor }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Borde de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="borde" v-model="formFollow.borde" :options="bordes" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.borde" class="text-red-500">{{
                                        errors.borde
                                    }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Piel perisional <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="piel_perisional" v-model="formFollow.piel_perisional"
                                        :options="piel_perisional" filter optionLabel="name" optionValue="name"
                                        class="w-full min-w-0" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.piel_perisional" class="text-red-500">{{
                                        errors.piel_perisional
                                    }}</small>
                                </div>
                            </div>

                            <!-- Nueva sección: Zona de la herida (dimensiones) -->
                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Zona de la herida (dimensiones)
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fecha de medición
                                        <span class="text-red-600">*</span>
                                    </label>
                                    <DatePicker v-model="formFollow.measurementDate" inputId="measurementDate"
                                        class="w-full min-w-0" placeholder="Seleccione una fecha" showIcon />
                                    <small v-if="errors.measurementDate" class="text-red-500">{{
                                        errors.measurementDate
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.length" class="w-full min-w-0" />
                                    <small v-if="errors.length" class="text-red-500">{{
                                        errors.length
                                    }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.width" class="w-full min-w-0" />
                                    <small v-if="errors.width" class="text-red-500">{{
                                        errors.width
                                    }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)</label>
                                    <InputText v-model="formFollow.area" class="w-full min-w-0" disabled />
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.depth" class="w-full min-w-0" />
                                    <small v-if="errors.depth" class="text-red-500">{{
                                        errors.depth
                                    }}</small>
                                </div>
                                <!-- Mostrar campo volumen solo si profundidad tiene valor numérico válido -->
                                <div v-if="parseFloat(formFollow.depth)">
                                    <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)</label>
                                    <InputText v-model="formFollow.volume" class="w-full min-w-0" disabled />
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Tunelización<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.tunneling" class="w-full min-w-0" />
                                    <small v-if="errors.tunneling" class="text-red-500">{{
                                        errors.tunneling
                                    }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.undermining" class="w-full min-w-0" />
                                    <small v-if="errors.undermining" class="text-red-500">{{
                                        errors.undermining
                                    }}</small>
                                </div>


                            </div>


                            <!-- Nueva sección: Evidencia de la herida -->
                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Evidencia de la herida
                                </h3>
                            </div>


                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Establecer tratamiento
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6 px-4">
                                <!-- Selección de métodos -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Seleccionar métodos<span
                                            class="text-red-600">*</span></label>
                                    <MultiSelect v-model="formFollow.methods" :options="treatmentMethods"
                                        optionLabel="name" optionValue="id" display="chip" class="w-full min-w-0"
                                        placeholder="Seleccione uno o varios métodos" />
                                </div>

                                <!-- Submétodos por método -->
                                <div v-for="method in selectedMethodsWithSubmethods" :key="method.id">
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        {{ method.name }}
                                    </label>
                                    <MultiSelect v-model="formFollow.submethodsByMethod[method.id]"
                                        :options="method.submethods" class="w-full min-w-0" optionLabel="name"
                                        optionValue="id" placeholder="Seleccione submétodos" display="chip" />
                                </div>
                            </div>

                            <!-- Descripción final -->
                            <div class="grid grid-cols-3 gap-6 px-4 mt-6">
                                <div class="col-span-3">
                                    <label for="description" class="flex items-center gap-1 mb-1 font-medium">
                                        Descripción final del tratamiento de la herida<span
                                            class="text-red-600">*</span>
                                    </label>
                                    <Editor v-model="formFollow.description" editorStyle="height: 150px"
                                        class="w-full min-w-0" />
                                    <small v-if="errors.description" class="text-red-500">{{ errors.description
                                        }}</small>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row justify-end sm:justify-end gap-2 px-4 py-6">
                                <Button label="Actualizar" icon="pi pi-check" text type="submit"
                                    :loading="isSavingFollow" :disabled="isSavingFollow" />

                                <Button label="Terminar consulta" icon="pi pi-check" severity="danger"
                                    :loading="isSavingFollow" :disabled="isSavingFollow" />

                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.p-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 2rem;
    padding-left: 0;
    list-style: none;
    user-select: none;
}

.p-steps::before {
    content: "";
    position: absolute;
    top: 1.25rem;
    left: 2rem;
    right: 2rem;
    height: 2px;
    background-color: #dcdcdc;
    z-index: 0;
}

.p-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    position: relative;
    flex: 1;
    color: #6c757d;
    font-weight: 600;
    z-index: 1;
}

.p-step-number {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #dee2e6;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
    user-select: none;
}

.p-step-active {
    color: #f10e0e;
    font-weight: 700;
}

.p-step-active .p-step-number {
    background-color: #f10e0e;
    color: white;
    border-color: #f10e0e;
}

.p-step-title {
    font-size: 0.9rem;
    text-align: center;
    user-select: none;
}

.zoom-img {
    margin-top: 0;
    /* Por defecto (móviles y tablets) */
}

/* Solo en pantallas grandes (≥ 1024px) */
@media (min-width: 1024px) {
    .zoom-img {
        margin-top: 5rem;
        /* o 6rem, 100px según necesites */
    }
}
</style>