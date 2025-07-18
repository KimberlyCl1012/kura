<script setup>
import AppLayout from "@/Layouts/Sakai/AppLayout.vue";
import { ref, reactive } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import {
    InputText, Toolbar, DataTable, Column,
    Dialog, Button, InputIcon, IconField,
    DatePicker, Select
} from "primevue";

const props = defineProps({
    sites: Array,
    kurators: Array,
    patientRecords: Array,
    appointments: Array,
    sex: Array,
    states: Array,
    kinshipOptions: Array,
    identificationTypes: Array,
});

const toast = useToast();
const dt = ref();
const filters = ref({
    global: { value: null, matchMode: "contains" },
});

const sex = [
    { label: "Hombre", value: "Hombre" },
    { label: "Mujer", value: "Mujer" },
];

const identificationTypes = ["INE", "CURP", "Pasaporte", "Visa", "Otro"];
const kinshipOptions = ["Padre", "Madre", "Hermano", "Amigo", "Otro"];

const patientDialog = ref(false);
const isEditMode = ref(false);
const patientIdEditing = ref(null);
const submittedPatient = ref(false);
const isSavingPatient = ref(false);

const patient = reactive({
    name: '', fatherLastName: '', motherLastName: '',
    sex: null, site_id: null, mobile: '', email: '',
    dateOfBirth: null, state_id: null, streetAddress: '',
    postalCode: '', relativeName: '', kinship: null,
    relativeMobile: '', type_identification: null,
    identification: '',
});

async function editPatient(patientId) {
    patientIdEditing.value = patientId;
    submittedPatient.value = false;
    isSavingPatient.value = false;
    patientDialog.value = true;

    try {
        const { data } = await axios.get(route("patients.show", patientId));
        Object.assign(patient, data);
    } catch (err) {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudo cargar el paciente", life: 3000 });
        patientDialog.value = false;
    }
}

async function saveUser() {
    submittedPatient.value = true;
    const required = [
        'name', 'fatherLastName', 'sex', 'site_id', 'email',
        'dateOfBirth', 'state_id', 'type_identification', 'identification'
    ];

    for (const field of required) {
        if (!patient[field]) {
            toast.add({ severity: "warn", summary: "Validación", detail: "Complete los campos obligatorios", life: 3000 });
            return;
        }
    }

    isSavingPatient.value = true;
    try {
        await axios.post(route('patients.update', patientIdEditing.value), {
            ...patient,
            _method: 'PUT'
        });
        toast.add({ severity: "success", summary: "Actualizado", detail: "Paciente actualizado", life: 3000 });
        patientDialog.value = false;
    } catch (error) {
        toast.add({ severity: "error", summary: "Error", detail: error.response?.data?.message || "Error al guardar", life: 3000 });
    } finally {
        isSavingPatient.value = false;
    }
}

function hidePatientDialog() {
    patientDialog.value = false;
}

function goToWounds(appointmentId) {
    router.visit(route('wounds.index', appointmentId));
}
</script>

<template>
    <AppLayout title="Mis Consultas">
        <div class="card">
            <Toolbar class="mb-6">
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt.exportCSV()" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="props.appointments" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords}">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Mis Consultas</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters.global.value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="dateStartVisit" header="Fecha consulta" />
                <Column field="health_record_uuid" header="Expediente" />
                <Column field="patient_full_name" header="Paciente" />
                <Column field="typeVisit" header="Tipo" />
                <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-user-edit" outlined rounded severity="warning"
                            v-tooltip.top="'Editar Paciente'" @click="editPatient(data.patient_id)" />
                        <Button icon="pi pi-plus" outlined rounded severity="danger" class="ml-2"
                            v-tooltip.top="'Heridas'" @click="goToWounds(data.id)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Diálogo Editar Paciente -->
        <Dialog v-model:visible="patientDialog" :modal="true" :style="{ width: '600px' }" header="Editar paciente">
            <form @submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block font-bold mb-1">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <InputText id="name" v-model="patient.name" class="w-full"
                            :class="{ 'p-invalid': submittedPatient && !patient.name }" />
                        <small v-if="submittedPatient && !patient.name" class="text-red-500">El nombre es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="fatherLastName" class="block font-bold mb-1">
                            Apellido paterno <span class="text-red-500">*</span>
                        </label>
                        <InputText id="fatherLastName" v-model="patient.fatherLastName" class="w-full"
                            :class="{ 'p-invalid': submittedPatient && !patient.fatherLastName }" />
                        <small v-if="submittedPatient && !patient.fatherLastName" class="text-red-500">El apellido
                            paterno es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="motherLastName" class="block font-bold mb-1">Apellido materno</label>
                        <InputText id="motherLastName" v-model="patient.motherLastName" class="w-full" />
                    </div>

                    <div>
                        <label for="sex" class="block font-bold mb-1">
                            Sexo <span class="text-red-500">*</span>
                        </label>
                        <Select id="sex" v-model="patient.sex" :options="sex" optionLabel="value" optionValue="value"
                            filter class="w-full" placeholder="Seleccione un sexo"
                            :class="{ 'p-invalid': submittedPatient && !patient.sex }" />
                        <small v-if="submittedPatient && !patient.sex" class="text-red-500">El sexo es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="site_id" class="block font-bold mb-1">
                            Sitio <span class="text-red-500">*</span>
                        </label>
                        <Select id="site_id" v-model="patient.site_id" :options="props.sites" optionLabel="siteName"
                            optionValue="id" filter class="w-full" placeholder="Seleccione un sitio"
                            :class="{ 'p-invalid': submittedPatient && !patient.site_id }" />
                        <small v-if="submittedPatient && !patient.site_id" class="text-red-500">El sitio es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="mobile" class="block font-bold mb-1">Teléfono</label>
                        <InputText id="mobile" v-model="patient.mobile" class="w-full" />
                    </div>

                    <div>
                        <label for="email" class="block font-bold mb-1">
                            Correo <span class="text-red-500">*</span>
                        </label>
                        <InputText id="email" v-model="patient.email" class="w-full"
                            :class="{ 'p-invalid': submittedPatient && !patient.email }" />
                        <small v-if="submittedPatient && !patient.email" class="text-red-500">El correo es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="dateOfBirth" class="block font-bold mb-1">
                            Fecha de nacimiento <span class="text-red-500">*</span>
                        </label>
                        <DatePicker class="w-full" inputId="dateOfBirth" showIcon v-model="patient.dateOfBirth"
                            :class="{ 'p-invalid': submittedPatient && !patient.dateOfBirth }" />
                        <small v-if="submittedPatient && !patient.dateOfBirth" class="text-red-500">La fecha es
                            obligatoria.</small>
                    </div>

                    <div>
                        <label for="state_id" class="block font-bold mb-1">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <Select id="state_id" v-model="patient.state_id" :options="props.states" optionLabel="name"
                            optionValue="id" filter class="w-full" placeholder="Seleccione un estado"
                            :class="{ 'p-invalid': submittedPatient && !patient.state_id }" />
                        <small v-if="submittedPatient && !patient.state_id" class="text-red-500">El estado es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="streetAddress" class="block font-bold mb-1">Calle y número</label>
                        <InputText id="streetAddress" v-model="patient.streetAddress" class="w-full" />
                    </div>

                    <div>
                        <label for="postalCode" class="block font-bold mb-1">Código postal</label>
                        <InputText id="postalCode" v-model="patient.postalCode" maxlength="12" class="w-full" />
                    </div>

                    <div class="col-span-1 md:col-span-2">
                        <label class="text-center block mb-5 mt-5"><b>DATOS DEL REPRESENTANTE LEGAL DEL PACIENTE (Si
                                aplica)</b></label>
                    </div>

                    <div>
                        <label for="relativeName" class="block font-bold mb-1">Nombre del familiar</label>
                        <InputText id="relativeName" v-model="patient.relativeName" class="w-full" />
                    </div>

                    <div>
                        <label for="kinship" class="block font-bold mb-1">Parentesco</label>
                        <Select id="kinship" :options="kinshipOptions" v-model="patient.kinship"
                            placeholder="Seleccione parentesco" class="w-full" />
                    </div>

                    <div>
                        <label for="relativeMobile" class="block font-bold mb-1">Teléfono del familiar</label>
                        <InputText id="relativeMobile" v-model="patient.relativeMobile" class="w-full" />
                    </div>

                    <div>
                        <label for="type_identification" class="block font-bold mb-1">
                            Tipo de identificación <span class="text-red-500">*</span>
                        </label>
                        <Select id="type_identification" :options="identificationTypes"
                            v-model="patient.type_identification" placeholder="Seleccione un tipo"
                            :class="{ 'p-invalid': submittedPatient && !patient.type_identification }" class="w-full" />
                        <small v-if="submittedPatient && !patient.type_identification" class="text-red-500">El tipo de
                            identificación es obligatorio.</small>
                    </div>

                    <div>
                        <label for="identification" class="block font-bold mb-1">
                            Número de identificación <span class="text-red-500">*</span>
                        </label>
                        <InputText id="identification" v-model="patient.identification" class="w-full"
                            :class="{ 'p-invalid': submittedPatient && !patient.identification }" />
                        <small v-if="submittedPatient && !patient.identification" class="text-red-500">El número de
                            identificación es obligatorio.</small>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <Button label="Cancelar" outlined severity="danger" @click="hidePatientDialog" />
                    <Button label="Actualizar" icon="pi pi-check" :loading="isSavingPatient" class="ml-2" type="submit" />
                </div>
            </form>
        </Dialog>
    </AppLayout>
</template>
