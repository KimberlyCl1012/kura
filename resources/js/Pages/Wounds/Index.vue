<script setup>
import AppLayout from "../../Layouts/Sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import {
    InputText,
    Select,
    Toolbar,
    DataTable,
    Column,
    Dialog,
    Button,
    IconField,
    InputIcon,
    DatePicker,
} from "primevue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    patients: Array,
    woundsType: Array,
    woundsSubtype: Array,
    grades: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    woundsPhase: Array,
});

const dt = ref();
const toast = useToast();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const woundDialog = ref(false);
const deletePatientDialog = ref(false);
const isEditMode = ref(false);
const isAntecedent = ref(false);
const submitted = ref(false);
const isSaving = ref(false);

const formWound = ref({
    id: null,
    wound_type_id: null,
    grade_foot: null,
    wound_subtype_id: null,
    wound_type_other: "",
    body_location_id: null,
    body_sublocation_id: null,
    wound_phase_id: null,
    woundBeginDate: null,
});

function openNewWound() {
    resetForm();
    isAntecedent.value = false;
    woundDialog.value = true;
}

function openNewAntecedent() {
    resetForm();
    isAntecedent.value = true;
    woundDialog.value = true;
}

function resetForm() {
    formWound.value = {
        id: null,
        wound_type_id: null,
        grade_foot: null,
        wound_subtype_id: null,
        wound_type_other: "",
        body_location_id: null,
        body_sublocation_id: null,
        wound_phase_id: null,
        woundBeginDate: null,
    };
    submitted.value = false;
    isEditMode.value = false;
}

function editUser(data) {
    // Asegúrate que aquí los valores son solo los ids (no objetos completos)
    formWound.value = {
        id: data.id ?? null,
        wound_type_id: data.wound_type_id ?? (data.wound_type?.id ?? null),
        grade_foot: data.grade_foot ?? null,
        wound_subtype_id: data.wound_subtype_id ?? (data.wound_subtype?.id ?? null),
        wound_type_other: data.wound_type_other ?? "",
        body_location_id: data.body_location_id ?? (data.body_location?.id ?? null),
        body_sublocation_id: data.body_sublocation_id ?? (data.body_sublocation?.id ?? null),
        wound_phase_id: data.wound_phase_id ?? (data.wound_phase?.id ?? null),
        woundBeginDate: data.woundBeginDate ?? null,
    };
    submitted.value = false;
    isEditMode.value = true;
    isAntecedent.value = false;
    woundDialog.value = true;
}

function hideDialog() {
    woundDialog.value = false;
    submitted.value = false;
}

async function saveUser() {
    submitted.value = true;

    const requiredFields = [
        { key: "wound_type_id", label: "Tipo de herida" },
        { key: "grade_foot", label: "Grado", condition: () => formWound.value.wound_type_id === 8 },
        { key: "wound_subtype_id", label: "Tipo de herida (Localización secundaria)" },
        {
            key: "wound_type_other",
            label: "Otro tipo de herida",
            condition: () => [7, 25, 33, 46].includes(formWound.value.wound_subtype_id),
        },
        { key: "body_location_id", label: "Ubicación corporal" },
        { key: "body_sublocation_id", label: "Ubicación corporal (Localización secundaria)" },
        { key: "wound_phase_id", label: "Fase de la herida" },
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
                ? "wounds_antecedent.store"
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
        router.reload({ only: ["patients"] });
    } catch (e) {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudo guardar", life: 3000 });
    } finally {
        isSaving.value = false;
    }
}
</script>

<template>
    <AppLayout title="Heridas">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <div class="flex flex-wrap gap-2">
                        <Button label="Nuevo" icon="pi pi-plus" severity="secondary" @click="openNewWound" />
                        <Button label="Antecedente" icon="pi pi-plus" severity="secondary" @click="openNewAntecedent" />
                    </div>
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt.exportCSV()" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="patients" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Heridas</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters.global.value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="patient_name" header="Paciente" />
                <Column field="wound_type_name" header="Tipo" />
                <Column field="wound_subtype_name" header="Subtipo" />
                <Column field="body_location_name" header="Ubicación" />
                <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(data)" />
                        <Button icon="pi pi-trash" severity="danger" outlined rounded
                            @click="deletePatientDialog = true" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Diálogo Crear/Editar -->
        <Dialog v-model:visible="woundDialog" modal style="width: 600px"
            :header="isEditMode ? 'Editar herida' : (isAntecedent ? 'Antecedente' : 'Registrar herida')">
            <form @submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tipo de herida -->
                    <div>
                        <label class="block font-bold mb-1">
                            Tipo de herida <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.wound_type_id" :options="woundsType" optionLabel="name"
                            optionValue="id" filter class="w-full" placeholder="Seleccione un tipo"
                            :class="{ 'p-invalid': submitted && !formWound.wound_type_id }" />
                        <small v-if="submitted && !formWound.wound_type_id" class="text-red-500">
                            Debe seleccionar el tipo de herida.
                        </small>
                    </div>

                    <!-- Grado -->
                    <div v-if="formWound.wound_type_id === 8">
                        <label class="block font-bold mb-1">
                            Grado <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.grade_foot" :options="grades" optionLabel="label" optionValue="value"
                            placeholder="Seleccione un grado" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.grade_foot }" />
                        <small v-if="submitted && !formWound.grade_foot" class="text-red-500">
                            Debe seleccionar el grado.
                        </small>
                    </div>

                    <!-- Otro tipo -->
                    <div v-if="formWound.wound_type_id === 9">
                        <label class="block font-bold mb-1">
                            Indicar tipo de herida <span class="text-red-600">*</span>
                        </label>
                        <InputText v-model="formWound.wound_type_other" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_type_other }" />
                        <small v-if="submitted && !formWound.wound_type_other" class="text-red-500">
                            Debe especificar otro tipo de herida.
                        </small>
                    </div>


                    <!-- Subtipo -->
                    <div>
                        <label class="block font-bold mb-1">
                            Tipo de herida (etiología) <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.wound_subtype_id" :options="woundsSubtype" optionLabel="name"
                            placeholder="Seleccione un tipo" optionValue="id" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_subtype_id }" />
                        <small v-if="submitted && !formWound.wound_subtype_id" class="text-red-500">
                            Debe seleccionar el subtipo.
                        </small>
                    </div>

                    <!-- Otro tipo -->
                    <div v-if="[7, 11, 25, 33, 46].includes(formWound.wound_subtype_id)">
                        <label class="block font-bold mb-1">
                            Indicar tipo de herida (etiología) <span class="text-red-600">*</span>
                        </label>
                        <InputText v-model="formWound.wound_type_other" class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_type_other }" />
                        <small v-if="submitted && !formWound.wound_type_other" class="text-red-500">
                            Debe especificar otro tipo de herida (etiología).
                        </small>
                    </div>

                    <!-- Ubicación corporal primaria -->
                    <div>
                        <label class="block font-bold mb-1">
                            Ubicación corporal primaria <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.body_location_id" :options="bodyLocations" optionLabel="name"
                            placeholder="Seleccione una ubicación primaria" optionValue="id" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.body_location_id }" />
                        <small v-if="submitted && !formWound.body_location_id" class="text-red-500">
                            Debe seleccionar la ubicación corporal.
                        </small>
                    </div>

                    <!-- Sublocalización -->
                    <div>
                        <label class="block font-bold mb-1">
                            Ubicación corporal secundaria <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.body_sublocation_id" :options="bodySublocation" optionLabel="name"
                            placeholder="Seleccione una ubicación secundaria" optionValue="id" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.body_sublocation_id }" />
                        <small v-if="submitted && !formWound.body_sublocation_id" class="text-red-500">
                            Debe seleccionar la sublocalización.
                        </small>
                    </div>

                    <!-- Fase -->
                    <div>
                        <label class="block font-bold mb-1">
                            Fase de la herida <span class="text-red-600">*</span>
                        </label>
                        <Select v-model="formWound.wound_phase_id" :options="woundsPhase" optionLabel="name"
                            placeholder="Seleccione una fase" optionValue="id" filter class="w-full"
                            :class="{ 'p-invalid': submitted && !formWound.wound_phase_id }" />
                        <small v-if="submitted && !formWound.wound_phase_id" class="text-red-500">
                            Debe seleccionar la fase.
                        </small>
                    </div>

                    <!-- Fecha inicio -->
                    <div>
                        <label class="block font-bold mb-1">
                            Fecha que inició la herida <span class="text-red-600">*</span>
                        </label>
                        <DatePicker v-model="formWound.woundBeginDate" class="w-full" inputId="woundBeginDate"
                            :class="{ 'p-invalid': submitted && !formWound.woundBeginDate }" variant="filled" showIcon
                            iconDisplay="input" />
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

        <!-- Confirmación eliminar -->
        <Dialog v-model:visible="deletePatientDialog" header="Eliminar herida" modal style="width: 450px">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle text-2xl" />
                <span>¿Confirma eliminar esta herida?</span>
            </div>
            <template #footer>
                <Button label="No" text @click="deletePatientDialog = false" />
                <Button label="Sí" severity="danger" @click="() => {
                    // Aquí agregar la lógica para eliminar la herida
                }" />
            </template>
        </Dialog>
    </AppLayout>
</template>
