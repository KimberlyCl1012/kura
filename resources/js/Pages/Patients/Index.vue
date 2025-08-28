<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref, watch, computed } from "vue";
import { useToast } from "primevue/usetoast";
import {
    InputText, Checkbox, Select,
    Toolbar, DataTable, Column, Dialog, Button, IconField, InputIcon, DatePicker, Tooltip
} from "primevue";
import axios from "axios";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    patients: Array,
    states: Array,
    sites: Array,
});

const page = usePage();
const userRole = computed(() => page.props.userRole);
const userPermissions = computed(() => page.props.userPermissions);
const userSite = computed(() => page.props.userSiteId);
const userSiteName = computed(() => page.props.userSiteName);

const dt = ref();
const toast = useToast();
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const rows = ref([...props.patients]);
watch(() => props.patients, (v) => {
    rows.value = [...v];
});

const sex = [
    { label: "Hombre", value: "Hombre" },
    { label: "Mujer", value: "Mujer" },
];

const identificationTypes = ["INE", "CURP", "Pasaporte", "Visa", "Otro"];
const kinshipOptions = ["Padre", "Madre", "Hermano", "Hijo"];

const patientDialog = ref(false);
const deletePatientDialog = ref(false);
const isEditMode = ref(false);
const submitted = ref(false);
const isSaving = ref(false);
const patient = ref({});

function openNew() {
    patient.value = {
        name: "", fatherLastName: "", motherLastName: "", email: "",
        dateOfBirth: null, type_identification: "", identification: "",
        type_identification_kinship: "", identification_kinship: "",
        streetAddress: "", city: "", postalCode: "",
        relativeName: "", kinship: "", relativeMobile: "",
        state_id: null, site_id: null, consent: 0,
        sex: null,
    };
    submitted.value = false;
    isEditMode.value = false;
    patientDialog.value = true;
}

function editUser(data) {
    patient.value = {
        ...data,
        sex: data.sex || null,
        site_id: data.site_id || null,
        state_id: data.state_id || null,
        kinship: data.kinship || null,
        type_identification: data.type_identification || null,
        type_identification_kinship: data.type_identification_kinship || null,
        identification_kinship: data.identification_kinship || null,
        consent: data.consent == 1 ? 1 : 0,
    };
    submitted.value = false;
    isEditMode.value = true;
    patientDialog.value = true;
}

function hideDialog() {
    patientDialog.value = false;
    submitted.value = false;
    patient.value = {};
}

async function saveUser() {
    submitted.value = true;

    const requiredFields = [
        "name", "fatherLastName", "email", "dateOfBirth",
        "type_identification", "identification", "state_id", "site_id"
    ];

    for (let field of requiredFields) {
        if (!patient.value[field]) {
            toast.add({
                severity: "warn",
                summary: "Validación",
                detail: `El campo ${field} es obligatorio`,
                life: 3000,
            });
            return;
        }
    }

    if (!isEditMode.value && !patient.value.consent) {
        toast.add({
            severity: "warn",
            summary: "Consentimiento",
            detail: "Debes aceptar el consentimiento",
            life: 3000,
        });
        return;
    }

    if (patient.value.kinship) {
        if (!patient.value.type_identification_kinship || !patient.value.identification_kinship) {
            toast.add({
                severity: "warn",
                summary: "Validación representante",
                detail: "Debe ingresar tipo y número de identificación del familiar",
                life: 3000,
            });
            return;
        }
    }

    isSaving.value = true;

    const payload = { ...patient.value, consent: patient.value.consent ? 1 : 0 };

    try {
        if (isEditMode.value) {
            await axios.post(route('patients.update', patient.value.patient_id), {
                ...payload,
                _method: 'PUT'
            });
            toast.add({ severity: "success", summary: "Actualizado", detail: "Paciente actualizado", life: 3000 });
        } else {
            await axios.post(route('patients.store'), payload);
            toast.add({ severity: "success", summary: "Guardado", detail: "Paciente creado", life: 3000 });
        }
        hideDialog();

        router.reload({ only: ['patients'] });
    } catch (e) {
        if (e.response && e.response.status === 422) {
            const errors = e.response.data.errors;
            for (let field in errors) {
                toast.add({
                    severity: "error",
                    summary: "Error de validación",
                    detail: errors[field][0],
                    life: 4000,
                });
            }
        } else {
            toast.add({ severity: "error", summary: "Error", detail: "No se pudo guardar", life: 3000 });
        }
    } finally {
        isSaving.value = false;
    }
}

function confirmDeleteUser(data) {
    patient.value = { ...data };
    deletePatientDialog.value = true;
}

async function deleteUser() {
    const id = patient.value.patient_id;

    try {
        const { data } = await axios.post(route('patients.destroy', id), { _method: 'DELETE' });

        rows.value = rows.value.filter(p => p.patient_id !== id);

        toast.add({
            severity: "success",
            summary: "Eliminado",
            detail: data?.message || "Paciente eliminado",
            life: 3000,
        });

    } catch (error) {
        const detail = error?.response?.data?.message || "No se pudo eliminar";
        toast.add({ severity: "error", summary: "Error", detail, life: 4000 });
    } finally {
        deletePatientDialog.value = false;
    }
}

function exportCSV() {
    dt.value.exportCSV();
}

// Expediente
function healthRecord(data) {
    router.get(route('health_records.create', data.crypt_patient));
}
</script>


<template>
    <AppLayout title="Pacientes">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" @click="openNew" v-if="userRole === 'admin' || (userPermissions.includes('create_patient'))" />
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="rows" dataKey="patient_id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Pacientes</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters.global.value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Nombre" />
                <Column field="email" header="Correo" />
                <Column field="siteName" header="Sitio" />
                <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button :icon="data.health_record_id ? 'pi pi-folder-open' : 'pi pi-folder-plus'" outlined v-if="userRole === 'admin' || (userPermissions.includes('show_medical_record'))" 
                            rounded class="mr-2" @click="healthRecord(data)"
                            :severity="data.health_record_id ? 'info' : 'secondary'"
                            v-tooltip.top="data.health_record_id ? 'Ver expediente' : 'Crear expediente'" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(data)" v-if="userRole === 'admin' || (userPermissions.includes('edit_patient'))"
                            v-tooltip.top="'Editar'" />
                        <Button v-if="
                            (
                                userRole === 'admin' ||
                                (
                                    ['admin_kura', 'resp_sitio', 'perfil_operativo'].includes(userRole)
                                    && userSiteName === data.siteName
                                )
                            )
                        " icon="pi pi-trash" outlined rounded severity="danger" v-tooltip.top="'Eliminar'" 
                            @click="confirmDeleteUser(data)" />
                    </template>
                </Column>
            </DataTable>

        </div>

        <!-- Dialogo Crear/Editar Paciente -->
        <Dialog v-model:visible="patientDialog" :modal="true" :style="{ width: '600px' }"
            :header="isEditMode ? 'Editar paciente' : 'Crear paciente'">
            <form @submit.prevent="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Campos del formulario -->

                    <div>
                        <label for="name" class="block font-bold mb-1">Nombre<span class="text-red-600">*</span></label>
                        <InputText id="name" v-model="patient.name" class="w-full"
                            :class="{ 'p-invalid': submitted && !patient.name }" />
                        <small v-if="submitted && !patient.name" class="text-red-500">El nombre es obligatorio.</small>
                    </div>

                    <div>
                        <label for="fatherLastName" class="block font-bold mb-1">Apellido paterno<span
                                class="text-red-600">*</span></label>
                        <InputText id="fatherLastName" v-model="patient.fatherLastName" class="w-full"
                            :class="{ 'p-invalid': submitted && !patient.fatherLastName }" />
                        <small v-if="submitted && !patient.fatherLastName" class="text-red-500">El apellido paterno es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="motherLastName" class="block font-bold mb-1">Apellido materno</label>
                        <InputText id="motherLastName" v-model="patient.motherLastName" class="w-full" />
                    </div>

                    <div>
                        <label for="sex" class="block font-bold mb-1">Sexo<span class="text-red-600">*</span></label>
                        <Select id="sex" v-model="patient.sex" :options="sex" optionLabel="value" optionValue="value"
                            filter class="w-full" placeholder="Seleccione un sexo"
                            :class="{ 'p-invalid': submitted && !patient.sex }" />
                        <small v-if="submitted && !patient.sex" class="text-red-500">El sexo es obligatorio.</small>
                    </div>

                    <div>
                        <label for="site_id" class="block font-bold mb-1">Sitio<span
                                class="text-red-600">*</span></label>
                        <Select id="site_id" v-model="patient.site_id" :options="sites" optionLabel="siteName"
                            optionValue="id" filter class="w-full" placeholder="Seleccione un sitio"
                            :class="{ 'p-invalid': submitted && !patient.site_id }" />
                        <small v-if="submitted && !patient.site_id" class="text-red-500">El sitio es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="mobile" class="block font-bold mb-1">Teléfono</label>
                        <InputText id="mobile" v-model="patient.mobile" class="w-full" />
                    </div>

                    <div>
                        <label for="email" class="block font-bold mb-1">Correo<span
                                class="text-red-600">*</span></label>
                        <InputText type="email" id="email" v-model="patient.email" class="w-full"
                            :class="{ 'p-invalid': submitted && !patient.email }" />
                        <small v-if="submitted && !patient.email" class="text-red-500">El correo es obligatorio.</small>
                    </div>

                    <div>
                        <label for="dateOfBirth" class="block font-bold mb-1">Fecha de nacimiento<span
                                class="text-red-600">*</span></label>
                        <DatePicker class="w-full" inputId="in_label" showIcon iconDisplay="input" id="dateOfBirth"
                            placeholder="mm/dd/yyyy" v-model="patient.dateOfBirth" variant="filled" />
                        <small v-if="submitted && !patient.dateOfBirth" class="text-red-500">La fecha es
                            obligatoria.</small>
                    </div>

                    <div>
                        <label for="type_identification" class="block font-bold mb-1">Tipo de identificación<span
                                class="text-red-600">*</span></label>
                        <Select id="type_identification" :options="identificationTypes"
                            v-model="patient.type_identification" placeholder="Seleccione un tipo"
                            :class="{ 'p-invalid': submitted && !patient.type_identification }" class="w-full" />
                        <small v-if="submitted && !patient.type_identification" class="text-red-500">El tipo de
                            identificación
                            es obligatorio.</small>
                    </div>

                    <div>
                        <label for="identification" class="block font-bold mb-1">Número de identificación<span
                                class="text-red-600">*</span></label>
                        <InputText id="identification" v-model="patient.identification" class="w-full"
                            :class="{ 'p-invalid': submitted && !patient.identification }" />
                        <small v-if="submitted && !patient.identification" class="text-red-500">El número de
                            identificación es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="state_id" class="block font-bold mb-1">Estado<span
                                class="text-red-600">*</span></label>
                        <Select id="state_id" v-model="patient.state_id" :options="states" optionLabel="name"
                            optionValue="id" filter class="w-full" placeholder="Seleccione un estado"
                            :class="{ 'p-invalid': submitted && !patient.state_id }" />
                        <small v-if="submitted && !patient.state_id" class="text-red-500">El estado es
                            obligatorio.</small>
                    </div>

                    <div>
                        <label for="streetAddress" class="block font-bold mb-1">Calle</label>
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
                        <label for="type_identification_kinship" class="block font-bold mb-1">Tipo de
                            identificación del representante legal<span class="text-red-600">*</span></label>
                        <Select id="type_identification_kinship" :options="identificationTypes"
                            v-model="patient.type_identification_kinship" placeholder="Seleccione un tipo"
                            class="w-full"
                            :class="{ 'p-invalid': submitted && patient.kinship && !patient.type_identification_kinship }" />
                        <small v-if="submitted && patient.kinship && !patient.type_identification_kinship"
                            class="text-red-500">
                            El tipo de identificación es obligatorio.
                        </small>
                    </div>

                    <div>
                        <label for="identification_kinship" class="block font-bold mb-1">Número de identificación del
                            representante legal<span class="text-red-600">*</span></label>
                        <InputText id="identification_kinship" v-model="patient.identification_kinship" class="w-full"
                            :class="{ 'p-invalid': submitted && patient.kinship && !patient.identification_kinship }" />
                        <small v-if="submitted && patient.kinship && !patient.identification_kinship"
                            class="text-red-500">
                            El número de identificación es obligatorio.
                        </small>
                    </div>
                </div>

                <!-- Checkbox Consentimiento -->
                <template v-if="!isEditMode || patient.consent == 0">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-start gap-2 mt-4">
                            <Checkbox v-model="patient.consent" :binary="true" :true-value="1" :false-value="0"
                                inputId="consentCheckbox" />
                            <label for="consentCheckbox" class="text-sm cursor-pointer select-none">
                                Autorizo el uso de mis datos personales, clínicos y de imagen (fotografías) para fines
                                médicos y de seguimiento, conforme a la normativa de salud vigente. Acepto el
                                tratamiento y resguardo seguro de mi información.
                                <span class="text-red-600">*</span></label>
                        </div>
                        <small v-if="submitted && !patient.consent" class="text-red-500">
                            Debes aceptar el consentimiento.
                        </small>
                    </div>
                </template>

                <div class="mt-6 flex justify-end gap-2">
                    <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" :disabled="isSaving" />
                    <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" type="submit"
                        :loading="isSaving" :disabled="isSaving" />
                </div>
            </form>
        </Dialog>

        <!-- Confirmación eliminar -->
        <Dialog v-model:visible="deletePatientDialog" header="Confirmar" modal style="width:450px">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle text-3xl" />
                <span>¿Estás seguro que deseas eliminar el registro <b>{{ patient.name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" text @click="deletePatientDialog = false" />
                <Button label="Sí" text @click="deleteUser" />
            </template>
        </Dialog>
    </AppLayout>
</template>
