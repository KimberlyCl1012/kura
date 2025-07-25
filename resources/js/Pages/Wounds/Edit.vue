<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, watch, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";

import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Button from "primevue/button";
import { DatePicker } from "primevue";
import Editor from "primevue/editor";

import WoundTreatment from "./Wounds/WoundTreatment.vue";

const props = defineProps({
    woundsType: Array,
    woundsSubtype: Array,
    woundsPhase: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    grades: Array,
    wound: Object,
    appointmentId: String,
    healthRecordId: String,
});

const toast = useToast();
const currentStep = ref(1);
const submitted = ref(false);
const isSaving = ref(false);
const errors = ref({});

const bordes = ref([{ name: 'Adherido' }, { name: 'No adherido' }, { name: 'Enrollado' }, { name: 'Epitalizado' }]);
const valoracion = ref([{ name: 'MESI' }, { name: 'No aplica' }]);
const edema = ref([{ name: '+++' }, { name: '++' }, { name: '+' }, { name: 'No aplica' }]);
const dolor = ref([{ name: 'En reposo' }, { name: 'Con movimiento' }, { name: 'Ninguno' }]);
const exudado_cantidad = ref([{ name: 'Abundante' }, { name: 'Moderado' }, { name: 'Bajo' }]);
const exudado_tipo = ref([{ name: 'Seroso' }, { name: 'Purulento' }, { name: 'Hemático' }, { name: 'Serohemático' }]);
const olor = ref([{ name: 'Mal olor' }, { name: 'No aplica' }]);
const piel_perisional = ref([
    { name: 'Eritema' }, { name: 'Escoriación' }, { name: 'Maceración' }, { name: 'Reseca' },
    { name: 'Equimosis' }, { name: 'Indurada' }, { name: 'Queratosis' }, { name: 'Integra' }, { name: 'Hiperpigmentada' }
]);
const infeccion = ref([
    { name: 'Celulitis' }, { name: 'Pirexia' }, { name: 'Aumento del dolor' },
    { name: 'Rapida extensión del area ulcerada' }, { name: 'Mal olor' },
    { name: 'Incremento del exudado' }, { name: 'Eritema' }, { name: 'No aplica' }
]);
const tipo_dolor = ref([{ name: 'Nociceptivo' }, { name: 'Neuropático' }]);

const formWound = ref({
    id: null,
    appointment_id: props.appointmentId,
    health_record_id: props.healthRecordId,
    wound_type_id: null,
    grade_foot: null,
    wound_subtype_id: null,
    wound_type_other: "",
    body_location_id: null,
    body_sublocation_id: null,
    wound_phase_id: null,
    woundBeginDate: null,
    woundHealthDate: null,
    woundBackground: "",
    MESI: "",
    tunneling: "",
    borde: null,
    edema: null,
    dolor: null,
    exudado_cantidad: null,
    exudado_tipo: null,
    olor: null,
    piel_perisional: null,
    infeccion: null,
    tipo_dolor: null,
    visual_scale: "",
    blood_glucose: "",
    note: "",
    ITB_izquierdo: "",
    pulse_dorsal_izquierdo: "",
    pulse_tibial_izquierdo: "",
    pulse_popliteo_izquierdo: "",
    ITB_derecho: "",
    pulse_dorsal_derecho: "",
    pulse_tibial_derecho: "",
    pulse_popliteo_derecho: "",
});

const woundSubtypes = ref([]);
const bodySublocations = ref([]);
const isInitialLoadType = ref(true);
const isInitialLoadLocation = ref(true);

onMounted(() => {
    if (props.wound) {
        Object.assign(formWound.value, {
            ...props.wound,
            id: props.wound.id || props.wound.wound_id,
            woundBeginDate: props.wound.woundBeginDate ? new Date(props.wound.woundBeginDate) : null,
            woundHealthDate: props.wound.woundHealthDate ? new Date(props.wound.woundHealthDate) : null,
        });

        if (formWound.value.wound_type_id) loadSubtypes(formWound.value.wound_type_id);
        if (formWound.value.body_location_id) loadSublocations(formWound.value.body_location_id);
    }
});

async function loadSubtypes(typeId) {
    try {
        const { data } = await axios.get(`/wound_types/${typeId}/subtypes`);
        woundSubtypes.value = data;
    } catch {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudieron cargar los subtipos.", life: 5000 });
    }
}

async function loadSublocations(locationId) {
    try {
        const { data } = await axios.get(`/body_locations/${locationId}/sublocations`);
        bodySublocations.value = data;
    } catch {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudieron cargar las sublocalizaciones.", life: 5000 });
    }
}

watch(() => formWound.value.wound_type_id, (newVal) => {
    if (isInitialLoadType.value) {
        isInitialLoadType.value = false;
        return;
    }
    formWound.value.wound_subtype_id = null;
    woundSubtypes.value = [];
    if (newVal) loadSubtypes(newVal);
});

watch(() => formWound.value.body_location_id, (newVal) => {
    if (isInitialLoadLocation.value) {
        isInitialLoadLocation.value = false;
        return;
    }
    formWound.value.body_sublocation_id = null;
    bodySublocations.value = [];
    if (newVal) loadSublocations(newVal);
});

async function saveUser() {
    submitted.value = true;
    errors.value = {};
    isSaving.value = true;

    try {
        const payload = {
            ...formWound.value,
            woundBeginDate: formWound.value.woundBeginDate?.toISOString().slice(0, 10),
            woundHealthDate: formWound.value.woundHealthDate?.toISOString().slice(0, 10),
        };

        const response = await axios.put(`/wounds/${formWound.value.id}`, payload);
        toast.add({ severity: "success", summary: "Guardado", detail: response.data.message, life: 5000 });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        }
        toast.add({
            severity: "error",
            summary: "Error",
            detail: error.response?.data?.message || "No se pudo guardar la herida",
            life: 5000
        });
    } finally {
        isSaving.value = false;
    }
}

function goToStep(step) {
    currentStep.value = step;
}

const showVascularFields = computed(() => {
    return formWound.value.body_location_id === 3 || formWound.value.body_location_id === 5;
});

function onVisualScaleInput(event) {
    const input = event.target.value.replace('/10', '');
    let number = parseInt(input);

    if (!isNaN(number)) {
        // Limita entre 1 y 10
        if (number < 1) number = 1;
        if (number > 10) number = 10;

        formWound.value.visual_scale = `${number}/10`;
    } else {
        formWound.value.visual_scale = '';
    }
}

//Form measurement
async function saveMeasurement() {
    submitted.value = true;
    isSaving.value = true;
    errors.value = {};

    try {
        const payload = {
            wound_id: formWound.value.id, // Asegúrate de encriptarlo si el backend lo requiere
            appointment_id: props.appointmentId,
            measurementDate: formWound.value.wound_zone_date,
            lenght: formWound.value.length,
            width: formWound.value.width,
            area: formWound.value.area,
            depth: formWound.value.depth,
            volume: formWound.value.volume,
            redPercentaje: formWound.value.granulation_percent,
            yellowPercentaje: formWound.value.slough_percent,
            blackPercentaje: formWound.value.necrosis_percent,
            maxDepth: formWound.value.maxDepth, // Asegúrate de tenerlos en tu formulario
            avgDepth: formWound.value.avgDepth,
        };

        await axios.post('/measurements', payload);

        toast.add({ severity: 'success', summary: 'Dimensiones guardadas', detail: 'Medición registrada', life: 3000 });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        }

        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'No se pudo guardar la medición',
            life: 5000
        });
    } finally {
        isSaving.value = false;
    }
}


</script>

<template>
    <AppLayout title="Heridas">
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
                <div class="p-step" :class="{ 'p-step-active': currentStep === 3 }" role="listitem"
                    @click="goToStep(3)">
                    <div class="p-step-number">3</div>
                    <div class="p-step-title">Abordaje de la herida</div>
                </div>
            </nav>

            <!-- Contenido -->
            <section class="flex-grow overflow-auto border rounded p-4">
                <div v-show="currentStep === 1">
                    <div
                        class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                        <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Datos de la herida</h2>

                        <form @submit.prevent="saveUser" class="flex flex-col flex-grow overflow-auto">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <!-- Tipo de herida -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Tipo de herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.wound_type_id" :options="props.woundsType"
                                        optionLabel="name" optionValue="id" filter placeholder="Seleccione un tipo"
                                        class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.wound_type_id }" />
                                    <small v-if="submitted && !formWound.wound_type_id" class="text-red-500">
                                        Debe seleccionar el tipo de herida.
                                    </small>
                                </div>

                                <!-- Grado (condicional) -->
                                <div v-if="formWound.wound_type_id === 8">
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Grado <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.grade_foot" :options="props.grades" optionLabel="label"
                                        optionValue="value" placeholder="Seleccione un grado" filter
                                        class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.grade_foot }" />
                                    <small v-if="submitted && !formWound.grade_foot" class="text-red-500">
                                        Debe seleccionar el grado.
                                    </small>
                                </div>

                                <!-- Otro tipo (condicional) -->
                                <div v-if="
                                    formWound.wound_type_id === 9 ||
                                    [7, 11, 25, 33, 46].includes(formWound.wound_subtype_id)
                                ">
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Indicar tipo de herida <span class="text-red-600">*</span>
                                    </label>
                                    <InputText v-model="formWound.wound_type_other" class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.wound_type_other }" />
                                    <small v-if="submitted && !formWound.wound_type_other" class="text-red-500">
                                        Debe especificar otro tipo de herida.
                                    </small>
                                </div>

                                <!-- Subtipo de herida -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Subtipo de herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.wound_subtype_id" :options="woundSubtypes"
                                        optionLabel="name" optionValue="id" filter placeholder="Seleccione un subtipo"
                                        class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.wound_subtype_id }" />
                                    <small v-if="submitted && !formWound.wound_subtype_id" class="text-red-500">
                                        Debe seleccionar el subtipo.
                                    </small>
                                </div>

                                <!-- Ubicación corporal -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Ubicación corporal <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.body_location_id" :options="props.bodyLocations"
                                        optionLabel="name" optionValue="id" filter
                                        placeholder="Seleccione una ubicación" class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.body_location_id }" />
                                    <small v-if="submitted && !formWound.body_location_id" class="text-red-500">
                                        Debe seleccionar una ubicación.
                                    </small>
                                </div>

                                <!-- Sublocalización corporal -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Ubicación corporal secundaria <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.body_sublocation_id" :options="bodySublocations"
                                        optionLabel="name" optionValue="id" filter
                                        placeholder="Seleccione una ubicación" class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.body_sublocation_id }" />
                                    <small v-if="submitted && !formWound.body_sublocation_id" class="text-red-500">
                                        Debe seleccionar la sublocalización.
                                    </small>
                                </div>

                                <!-- Fase -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fase de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formWound.wound_phase_id" :options="props.woundsPhase"
                                        optionLabel="name" placeholder="Seleccione una fase" optionValue="id" filter
                                        class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.wound_phase_id }" />
                                    <small v-if="submitted && !formWound.wound_phase_id" class="text-red-500">
                                        Debe seleccionar la fase.
                                    </small>
                                </div>

                                <!-- Fecha inicio -->
                                <div>
                                    <label>Fecha que inició la herida <span class="text-red-600">*</span></label>
                                    <DatePicker v-model="formWound.woundBeginDate" class="w-full min-w-0"
                                        inputId="woundBeginDate"
                                        :class="{ 'p-invalid': submitted && !formWound.woundBeginDate }"
                                        placeholder="Seleccione una fecha" showIcon dateFormat="yy-mm-dd" />
                                    <small v-if="submitted && !formWound.woundBeginDate" class="text-red-500">
                                        Debe seleccionar la fecha de inicio.
                                    </small>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2 p-10">
                                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSaving"
                                    :disabled="isSaving" />
                            </div>
                        </form>
                    </div>
                </div>
                <div v-show="currentStep === 2">
                    <div
                        class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                        <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Evaluación de la herida</h2>

                        <form @submit.prevent="saveUser" class="flex flex-col flex-grow overflow-auto">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">


                                <template
                                    v-if="[18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33].includes(formWound.body_location_id)">

                                    <!-- Campos vasculares -->
                                    <div class="col-span-full">
                                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Valoración vascular (solo
                                            aplica en heridas en pierna)</h3>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Índice tobillo brazo Manual <span class="text-red-600">*</span>
                                        </label>
                                        <Select id="valoracion" v-model="formWound.valoracion" :options="valoracion"
                                            filter optionLabel="name" optionValue="name" class="w-full min-w-0"
                                            placeholder="Seleccione una opción" />
                                        <small v-if="errors.valoracion" class="text-red-500">{{ errors.valoracion
                                            }}</small>
                                    </div>

                                    <div v-if="['MESI'].includes(formWound.valoracion)">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            MESI <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="MESI" v-model="formWound.MESI" class="w-full min-w-0" />
                                        <small v-if="errors.MESI" class="text-red-500">{{ errors.MESI }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_izquierdo" v-model="formWound.ITB_izquierdo"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.ITB_izquierdo" class="text-red-500">{{ errors.ITB_izquierdo
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_derecho" v-model="formWound.ITB_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.ITB_derecho" class="text-red-500">{{ errors.ITB_derecho
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_izquierdo"
                                            v-model="formWound.pulse_dorsal_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_dorsal_izquierdo" class="text-red-500">{{
                                            errors.pulse_dorsal_izquierdo }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_izquierdo"
                                            v-model="formWound.pulse_popliteo_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_popliteo_izquierdo" class="text-red-500">{{
                                            errors.pulse_popliteo_izquierdo }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_izquierdo"
                                            v-model="formWound.pulse_tibial_izquierdo" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_tibial_izquierdo" class="text-red-500">{{
                                            errors.pulse_tibial_izquierdo }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_derecho" v-model="formWound.pulse_dorsal_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.pulse_dorsal_derecho" class="text-red-500">{{
                                            errors.pulse_dorsal_derecho }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_derecho"
                                            v-model="formWound.pulse_popliteo_derecho" class="w-full min-w-0" />
                                        <small v-if="errors.pulse_popliteo_derecho" class="text-red-500">{{
                                            errors.pulse_popliteo_derecho }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_derecho" v-model="formWound.pulse_tibial_derecho"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.pulse_tibial_derecho" class="text-red-500">{{
                                            errors.pulse_tibial_derecho }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Monofilamento <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="monofilamento" v-model="formWound.monofilamento"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.monofilamento" class="text-red-500">{{ errors.monofilamento
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Nivel de glucosa en sangre <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="blood_glucose" v-model="formWound.blood_glucose"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.blood_glucose" class="text-red-500">{{ errors.blood_glucose
                                            }}</small>
                                    </div>
                                </template>

                                <div class="col-span-full">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Información de la herida</h3>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fecha de primera valoración <span class="text-red-600">*</span>
                                    </label>
                                    <DatePicker v-model="formWound.woundHealthDate" inputId="woundHealthDate"
                                        class="w-full min-w-0" placeholder="Seleccione una fecha" showIcon
                                        dateFormat="yy-mm-dd" />
                                    <small v-if="errors.woundHealthDate" class="text-red-500">{{ errors.woundHealthDate
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Edema <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="edema" v-model="formWound.edema" :options="edema" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.edema" class="text-red-500">{{ errors.edema }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="dolor" v-model="formWound.dolor" :options="dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.dolor" class="text-red-500">{{ errors.dolor }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Tipo de dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="tipo_dolor" v-model="formWound.tipo_dolor" :options="tipo_dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.tipo_dolor" class="text-red-500">{{ errors.tipo_dolor }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Escala Visual Analógica (EVA) <span class="text-red-600">*</span>
                                    </label>
                                    <InputText id="visual_scale" v-model="formWound.visual_scale" class="w-full min-w-0"
                                        :class="{ 'p-invalid': submitted && !formWound.visual_scale }"
                                        placeholder="Ej: 3/10" @input="onVisualScaleInput" />
                                    <small v-if="errors.visual_scale" class="text-red-500">{{ errors.visual_scale
                                    }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Tipo) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_tipo" v-model="formWound.exudado_tipo" :options="exudado_tipo"
                                        filter optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_tipo" class="text-red-500">{{ errors.exudado_tipo
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Cantidad) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_cantidad" v-model="formWound.exudado_cantidad"
                                        :options="exudado_cantidad" filter optionLabel="name" optionValue="name"
                                        class="w-full min-w-0" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_cantidad" class="text-red-500">{{
                                        errors.exudado_cantidad }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Infección <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="infeccion" v-model="formWound.infeccion" :options="infeccion" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.infeccion" class="text-red-500">{{ errors.infeccion }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Olor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="olor" v-model="formWound.olor" :options="olor" filter optionLabel="name"
                                        optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.olor" class="text-red-500">{{ errors.olor }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Borde de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="borde" v-model="formWound.borde" :options="bordes" filter
                                        optionLabel="name" optionValue="name" class="w-full min-w-0"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.borde" class="text-red-500">{{ errors.borde }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Piel perisional <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="piel_perisional" v-model="formWound.piel_perisional"
                                        :options="piel_perisional" filter optionLabel="name" optionValue="name"
                                        class="w-full min-w-0" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.piel_perisional" class="text-red-500">{{ errors.piel_perisional
                                        }}</small>
                                </div>
                            </div>

                            <!-- Botón para guardar evaluación -->
                            <div class="mt-6 flex justify-end gap-2 p-10">
                                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSaving"
                                    :disabled="isSaving" />
                            </div>
                        </form>

                        <!-- Nueva sección: Zona de la herida (dimensiones) -->
                        <div class="col-span-full mt-10">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Zona de la herida (dimensiones)</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Fecha<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.wound_zone_date" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.length" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.width" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.area" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.depth" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.volume" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tunelización<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.tunnel" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Dirección túnel<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.tunnel_direction" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Centímetros túnel<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.tunnel_cm" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.undermining" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Dirección socavamiento<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.undermining_direction" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Centímetros socavamiento<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.undermining_cm" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tejido de granulación (%)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.granulation_percent" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tejido de esfácelo (%)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.slough_percent" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Necrosis (%)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.necrosis_percent" class="w-full min-w-0" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Epitelización (%)<span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="formWound.epithelialization_percent" class="w-full min-w-0" />
                            </div>
                        </div>

                        <Button label="Guardar dimensiones" icon="pi pi-save" @click="saveMeasurement"
                            :loading="isSaving" />


                    </div>
                </div>

                <div v-show="currentStep === 3">
                    <WoundTreatment :wound-id="formWound.id" />
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
</style>
