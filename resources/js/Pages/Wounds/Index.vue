<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref, watch, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import {
    InputText,
    Select,
    Toolbar,
    DataTable,
    Column,
    Dialog,
    Button,
    DatePicker,
} from "primevue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    woundsType: Array,
    woundsSubtype: Array,
    woundsPhase: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    wounds: Array,
    appointmentId: String,
    healthRecordId: String,
    followUps: Array,
    patient: Object,
});

const dt = ref();
const toast = useToast();

//Catalogos
const grades = ref([{ id: 1, name: "1" }, { id: 2, name: "2" }, { id: 3, name: "3" }]);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const woundDialog = ref(false);
const isEditMode = ref(false);
const isAntecedent = ref(false);
const submitted = ref(false);
const isSaving = ref(false);

const expandedRows = ref({});

const formWound = ref({
    id: null,
    appointment_id: props.appointmentId,
    health_record_id: props.healthRecordId,
    wound_type_id: null,
    grade_foot: null,
    type_bite: null,
    wound_subtype_id: null,
    body_location_id: null,
    body_sublocation_id: null,
    woundBeginDate: null,
    wound_id: null,
});

const woundSubtypes = ref([]);
const bodySublocations = ref([]);

// Cuando cambia el tipo de herida, cargar subtipos
watch(() => formWound.value.wound_type_id, async (newVal) => {
    formWound.value.wound_subtype_id = null;
    woundSubtypes.value = [];
    if (!newVal) return;

    try {
        const { data } = await axios.get(`/wound_types/${newVal}/subtypes`);
        woundSubtypes.value = data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los subtipos.',
            life: 3000,
        });
    }
});

// Cuando cambia la ubicación corporal, cargar sublocalizaciones
watch(() => formWound.value.body_location_id, async (newVal) => {
    formWound.value.body_sublocation_id = null;
    bodySublocations.value = [];
    if (!newVal) return;

    try {
        const { data } = await axios.get(`/body_locations/${newVal}/sublocations`);
        bodySublocations.value = data;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar las sublocalizaciones.',
            life: 3000,
        });
    }
});

function openNewWound() {
    resetForm();
    isAntecedent.value = false;
    woundDialog.value = true;
}

function openNewAntecedent(data) {
    resetForm();
    isAntecedent.value = true;
    formWound.value.id = null;
    formWound.value.wound_type_id = null;
    formWound.value.wound_subtype_id = null;
    formWound.value.body_location_id = null;
    formWound.value.body_sublocation_id = null;
    formWound.value.wound_id = data.id;

    woundDialog.value = true;
}

// Resetear formulario
function resetForm() {
    formWound.value = {
        id: null,
        appointment_id: props.appointmentId,
        health_record_id: props.healthRecordId,
        wound_type_id: null,
        grade_foot: null,
        type_bite: null,
        wound_subtype_id: null,
        body_location_id: null,
        body_sublocation_id: null,
        woundBeginDate: null,
        wound_id: null,
    };
    submitted.value = false;
    isEditMode.value = false;
}

// Ocultar diálogo
function hideDialog() {
    woundDialog.value = false;
    submitted.value = false;
}

// Guardar herida o antecedente
async function saveUser() {
    submitted.value = true;

    const requiredFields = [
        { key: "wound_type_id", label: "Tipo de herida" },
        { key: "grade_foot", label: "Grado", condition: () => formWound.value.wound_type_id === 8 },
        { key: "type_bite", label: "Tipo de picadura/mordedura", condition: () => formWound.value.wound_subtype_id === 10 },
        { key: "wound_subtype_id", label: "Tipo de herida (Localización secundaria)" },
        { key: "body_location_id", label: "Ubicación corporal" },
        { key: "body_sublocation_id", label: "Ubicación corporal (Localización secundaria)" },
        { key: "woundBeginDate", label: "Fecha que inició la herida" },
    ];

    for (const field of requiredFields) {
        if (field.condition && !field.condition()) continue;
        const val = formWound.value[field.key];
        if (val === null || val === "" || val === undefined) {
            toast.add({
                severity: "warn",
                summary: "Validación",
                detail: `Debe seleccionar ${field.label}`,
                life: 3000,
            });
            return;
        }
    }

    isSaving.value = true;

    try {
        const routeName = isEditMode.value
            ? "wounds.update"
            : isAntecedent.value
                ? "wounds_histories.store"
                : "wounds.store";

        const request = isEditMode.value
            ? axios.put(route(routeName, formWound.value.id), formWound.value)
            : axios.post(route(routeName), formWound.value);

        await request;

        toast.add({
            severity: "success",
            summary: "Guardado",
            detail: isAntecedent.value ? "Antecedente creado" : "Herida creada",
            life: 3000,
        });

        hideDialog();
        router.reload({ only: ["wounds"] });
    } catch (e) {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudo guardar", life: 3000 });
    } finally {
        isSaving.value = false;
    }
}

// Expandir todas filas
const expandAll = () => {
    expandedRows.value = {};
    props.wounds.forEach(w => (expandedRows.value[w.id] = true));
};
// Colapsar todas filas
const collapseAll = () => {
    expandedRows.value = {};
};
// Expandir/colapsar filas
const onRowExpand = (event) => { };
const onRowCollapse = (event) => { };

function goToWound(wound) {
    router.visit(route('wounds.edit', { woundId: wound.wound_id }));
}

function goToWoundHis(wound) {
    router.visit(route('wounds_histories.edit', { woundHisId: wound.wound_history_id }));
}

//Seguimiento
function goToFollow(wound) {
    router.visit(route('wounds_follow.edit', {
        appointmentId: props.appointmentId,
        woundId: wound.wound_id
    }));
}


</script>

<template>
    <AppLayout title="Heridas">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <div class="flex flex-wrap gap-2">
                        <Button label="Nueva" icon="pi pi-plus" severity="secondary" @click="openNewWound" />
                        <Button label="Expandir todo" icon="pi pi-plus" text @click="expandAll" />
                        <Button label="Colapsar todo" icon="pi pi-minus" text @click="collapseAll" />
                    </div>
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt.exportCSV()" />
                </template>
            </Toolbar>

            <DataTable v-if="wounds.length > 0" ref="dt" :value="wounds" dataKey="id" :paginator="true" :rows="10"
                :filters="filters" :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros"
                v-model:expandedRows="expandedRows" @rowExpand="onRowExpand" @rowCollapse="onRowCollapse"
                responsiveLayout="scroll">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Heridas</h4>
                        <InputText v-model="filters.global.value" placeholder="Buscar..." />
                    </div>
                </template>

                <Column expander style="width: 3rem" />
                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column header="Paciente">
                    <template #body>
                        {{ patient.full_name }}
                    </template>
                </Column>
                <Column field="wound_type" header="Tipo" />
                <Column field="wound_subtype" header="Subtipo" />
                <Column field="body_location" header="Ubicación" />
                <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-file-edit" outlined rounded class="mr-2" @click="goToWound(data)"
                            v-tooltip.top="'Configurar herida'" />
                        <Button icon="pi pi-history" outlined rounded class="mr-2" severity="info"
                            @click="() => openNewAntecedent(data)" v-tooltip.top="'Crear antecedente'" />
                    </template>
                </Column>

                <!-- Fila expandida para mostrar antecedentes -->
                <template #expansion="{ data }">
                    <div class="p-4 border-2 border-primary rounded-lg">
                        <h5>Antecedentes de la herida</h5>
                        <DataTable v-if="data.histories && data.histories.length > 0" :value="data.histories"
                            responsiveLayout="scroll" size="small">
                            <Column header="#" style="min-width: 6rem">
                                <template #body="{ index }">{{ index + 1 }}</template>
                            </Column>
                            <Column field="wound_phase_history" header="Fase" />
                            <Column field="wound_type_history" header="Tipo" />
                            <Column field="body_location_history" header="Ubicación" />
                            <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <Button icon="pi pi-file-edit" outlined rounded class="mr-2"
                                        @click="goToWoundHis(data)" v-tooltip.top="'Configurar antecedente'" />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-else class="text-center text-gray-500 italic mt-3">
                            No existen antecedentes de la herida
                        </div>
                    </div>
                </template>
            </DataTable>

            <!-- Tabla de Seguimientos -->
            <div v-if="followUps.length > 0" class="card mt-6">
                <h4 class="mb-4 font-bold">Seguimiento</h4>

                <DataTable :value="followUps" dataKey="id" :paginator="true" :rows="10" responsiveLayout="scroll">
                    <Column header="#" style="min-width: 6rem">
                        <template #body="{ index }">{{ index + 1 }}</template>
                    </Column>
                    <Column field="wound_type" header="Tipo" />
                    <Column field="wound_subtype" header="Subtipo" />
                    <Column field="body_location" header="Ubicación" />
                    <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                        <template #body="{ data }">
                            <Button icon="pi pi-file-edit" outlined rounded class="mr-2" @click="goToFollow(data)"
                                v-tooltip.top="'Configurar seguimiento'" />
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>

        <!-- Diálogo Crear/Editar -->
        <Dialog v-model:visible="woundDialog" modal style="width: 600px"
            :header="isEditMode ? 'Editar herida' : (isAntecedent ? 'Antecedente de la herida' : 'Registrar nueva herida')">
            <form @submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tipo de herida -->
                    <div>
                        <label class="block font-bold mb-1">
                            Tipo de herida <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.wound_type_id" :options="woundsType" optionLabel="name"
                            optionValue="id" filter placeholder="Seleccione un tipo" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_type_id }" />
                        <small v-if="submitted && !formWound.wound_type_id" class="text-red-500">
                            Debe seleccionar el tipo de herida.
                        </small>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">
                            Subtipo de herida <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.wound_subtype_id" :options="woundSubtypes" optionLabel="name"
                            optionValue="id" filter placeholder="Seleccione un subtipo" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_subtype_id }" />
                        <small v-if="submitted && !formWound.wound_subtype_id" class="text-red-500">
                            Debe seleccionar el subtipo.
                        </small>
                    </div>

                    <!-- Grado -->
                    <div v-if="formWound.wound_type_id === 8">
                        <label class="block font-bold mb-1">
                            Grado <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.grade_foot" :options="grades" optionLabel="name" optionValue="id"
                            placeholder="Seleccione un grado" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.grade_foot }" />
                        <small v-if="submitted && !formWound.grade_foot" class="text-red-500">
                            Debe seleccionar el grado.
                        </small>
                    </div>

                    <!-- Picadura -->
                    <div v-if="formWound.wound_subtype_id === 10">
                        <label class="block font-bold mb-1">
                            Tipo de picadura/mordedura <span class="text-red-600">*</span>
                        </label>
                        <InputText id="type_bite" v-model="formWound.type_bite" class="w-full min-w-0" />
                      <small v-if="submitted && !formWound.type_bite" class="text-red-500">
                            Debe escribir el tipo de picadura/mordedura.
                        </small>
                    </div>

                    <!-- Ubicación corporal -->
                    <div>
                        <label class="block font-bold mb-1">
                            Ubicación corporal <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.body_location_id" :options="bodyLocations" optionLabel="name"
                            optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.body_location_id }" />
                        <small v-if="submitted && !formWound.body_location_id" class="text-red-500">
                            Debe seleccionar una ubicación.
                        </small>
                    </div>

                    <!-- Sublocalización corporal -->
                    <div>
                        <label class="block font-bold mb-1">
                            Ubicación corporal secundaria <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.body_sublocation_id" :options="bodySublocations" optionLabel="name"
                            optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.body_sublocation_id }" />
                        <small v-if="submitted && !formWound.body_sublocation_id" class="text-red-500">
                            Debe seleccionar la sublocalización.
                        </small>
                    </div>

                    <!-- Fecha inicio -->
                    <div>
                        <label class="block font-bold mb-1">
                            Fecha que inició la herida <span class="text-red-600">*</span>
                        </label>
                        <DatePicker v-model="formWound.woundBeginDate" class="w-full" inputId="woundBeginDate"
                            placeholder="mm/dd/yyyy" :class="{ 'p-invalid': submitted && !formWound.woundBeginDate }"
                            variant="filled" showIcon iconDisplay="input" />
                        <small v-if="submitted && !formWound.woundBeginDate" class="text-red-500">
                            Debe seleccionar la fecha de inicio.
                        </small>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" :disabled="isSaving" />
                    <Button label="Guardar" icon="pi pi-check" type="submit" :loading="isSaving" :disabled="isSaving" />
                </div>
            </form>
        </Dialog>
    </AppLayout>
</template>
