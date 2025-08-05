<script setup>
import AppLayout from "@/Layouts/sakai/AppLayout.vue";
import { ref, reactive } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import {
    InputText, Toolbar, DataTable, Column,
    Dialog, Button, InputIcon, Select, DatePicker, Tooltip
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
const dt = ref(null);

const filters = ref({
    global: { value: null, matchMode: "contains" },
});

const sexOptions = [
    { label: "Hombre", value: "Hombre" },
    { label: "Mujer", value: "Mujer" },
];

const identificationTypesOptions = props.identificationTypes || ["INE", "CURP", "Pasaporte", "Visa", "Otro"];
const kinshipOptionsLocal = props.kinshipOptions || ["Padre", "Madre", "Hermano", "Amigo", "Otro"];

const expandedRows = ref({});

const expandAll = () => {
    const all = {};
    props.appointments.forEach((p) => {
        all[p.patient_id] = true;
    });
    expandedRows.value = all;
};

const collapseAll = () => {
    expandedRows.value = {};
};

const onRowExpand = (event) => {
    toast.add({ severity: "info", summary: "Paciente expandido", detail: event.data.patient_full_name, life: 2000 });
};

const onRowCollapse = (event) => {
    toast.add({ severity: "warn", summary: "Paciente colapsado", detail: event.data.patient_full_name, life: 2000 });
};

// Para editar paciente
const patientDialog = ref(false);
const isEditMode = ref(false);
const patientIdEditing = ref(null);
const submittedPatient = ref(false);
const isSavingPatient = ref(false);

const patient = reactive({
    name: "",
    fatherLastName: "",
    motherLastName: "",
    sex: null,
    site_id: null,
    mobile: "",
    email: "",
    dateOfBirth: null,
    state_id: null,
    streetAddress: "",
    postalCode: "",
    relativeName: "",
    kinship: null,
    relativeMobile: "",
    type_identification: null,
    identification: "",
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

function goToWounds(appointments) {
    router.visit(route('wounds.index', {
        appointmentId: appointments.crypt_appointment_id,
        healthrecordId: appointments.crypt_health_record_id,
    }));
}

//Expediente
function healthRecord(patientId) {
    router.get(route('health_records.create', patientId));
}

</script>

<template>
    <AppLayout title="Mis Consultas">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button text icon="pi pi-plus" label="Expandir Todo" @click="expandAll" class="mr-2" />
                    <Button text icon="pi pi-minus" label="Colapsar Todo" @click="collapseAll" />
                </template>
                <template #end>
                    <IconField>
                        <InputIcon><i class="pi pi-search" /></InputIcon>
                        <InputText v-model="filters.global.value" placeholder="Buscar..." />
                    </IconField>
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="props.appointments" v-model:expandedRows="expandedRows" dataKey="patient_id"
                :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords}"
                @row-expand="onRowExpand" @row-collapse="onRowCollapse"
                tableStyle="min-width: 60rem; table-layout: fixed">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Mis consultas</h4>
                    </div>
                </template>

                <!-- Columnas principales -->
                <Column expander style="width: 5%" />
                <Column header="#" style="width: 5%">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="health_record_uuid" header="Expediente" style="width: 30%" />
                <Column field="patient_full_name" header="Paciente" style="width: 30%" />
                <Column :exportable="false" header="Acciones" style="width: 30%">
                    <template #body="{ data }">
                        <Button icon="pi pi-user-edit" outlined rounded severity="warning" class="mr-2"
                            v-tooltip.top="'Editar Paciente'" @click.stop="editPatient(data.patient_id)" />
                        <Button icon="pi pi-folder-open" outlined rounded severity="info"
                            v-tooltip.top="'Ver expediente'" @click="healthRecord(data.patient_id)" />

                    </template>
                </Column>

                <!-- Fila expandida con estilo -->
                <template #expansion="{ data }">
                    <div class="p-4 border-2 border-primary rounded-lg">
                        <div class="text-primary mb-3">
                            Consulta del paciente: <b>{{ data.patient_full_name }}</b>
                        </div>

                        <DataTable :value="data.appointments" dataKey="id" :paginator="true" :rows="5"
                            tableStyle="min-width: 100%; table-layout: fixed"
                            emptyMessage="No hay consultas registradas para este paciente">
                            <Column header="#" style="width: 10%">
                                <template #body="{ index }">{{ index + 1 }}</template>
                            </Column>
                            <Column field="site_name" header="Sitio" style="width: 30%" />
                            <Column field="dateStartVisit" header="Fecha consulta" style="width: 30%" />
                            <Column field="typeVisit" header="Tipo de visita" style="width: 30%" />
                            <Column :exportable="false" header="Acciones" style="width: 30%">
                                <template #body="{ data }">
                                    <Button icon="pi pi-file-plus" outlined rounded severity="danger" class="ml-2"
                                        v-tooltip.top="'Heridas'" @click.stop="goToWounds(data)" />
                                </template>
                            </Column>

                        </DataTable>
                    </div>
                </template>
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
                        <Select id="sex" v-model="patient.sex" :options="sexOptions" optionLabel="value"
                            optionValue="value" filter class="w-full" placeholder="Seleccione un sexo"
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
                        <Select id="kinship" :options="kinshipOptionsLocal" v-model="patient.kinship"
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
                        <Select id="type_identification" :options="identificationTypesOptions"
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
                    <Button label="Cancelar" text icon="pi pi-times" @click="hidePatientDialog" />
                    <Button label="Actualizar" icon="pi pi-check" :loading="isSavingPatient" class="ml-2"
                        type="submit" />
                </div>
            </form>
        </Dialog>
    </AppLayout>
</template>
