<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, computed, watch, nextTick } from "vue";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode } from "@primevue/core/api";
import {
    InputText,
    Password,
    Toolbar,
    Button,
    Dialog,
    Column,
    DataTable,
    Select
} from "primevue";
import axios from "axios";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    kurators: Array,
    sites: Array,
});

const page = usePage();
const userRole = computed(() => page.props.userRole);
const userPermissions = computed(() => page.props.userPermissions);
const userSite = computed(() => page.props.userSiteId);
const userSiteName = computed(() => page.props.userSiteName);

const toast = useToast();
const dt = ref(null);
const kuratorDialog = ref(false);
const deleteKuratorDialog = ref(false);
const isEditMode = ref(false);
const submitted = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const sex = [
    { label: "Hombre", value: "Hombre" },
    { label: "Mujer", value: "Mujer" },
];

const types = [
    { label: "Enfermería", value: "Enfermería" },
    { label: "Medicina", value: "Medicina" },
];

const specialtyDetailKeyOptions = ["Especialista", "Especialidad", "Subespecialidad", "Maestría", "Doctorado"];

const specialtyMap = {
    "Enfermería": ["Técnico", "Licenciatura", "Especialista", "Maestría", "Doctorado"],
    "Medicina": ["Médico general", "Especialidad", "Subespecialidad", "Maestría", "Doctorado"],
};

const specialtyOptions = computed(() => {
    const type = kurator.value.type_kurator || "";
    return specialtyMap[type] || [];
});

const needsSpecialtyDetail = computed(() =>
    specialtyDetailKeyOptions.includes(kurator.value.specialty || "")
);

const typeIdentificationOptions = ["INE", "CURP", "Cedula Profesional", "Pasaporte", "Visa", "Otro"];

const kurator = ref({});
const isSaving = ref(false);

function openNew() {
    kurator.value = {
        name: "",
        fatherLastName: "",
        motherLastName: "",
        sex: "",
        mobile: "",
        site_id: null,
        specialty: "",
        detail_specialty: "",
        type_kurator: "",
        type_identification: "Cedula profesional",
        identification: "",
        email: "",
        password: "",
    };
    submitted.value = false;
    isEditMode.value = false;
    kuratorDialog.value = true;
}

function editKurator(data) {

    const base = {
        kurator_id: data.kurator_id,
        name: data.name || "",
        fatherLastName: data.fatherLastName || "",
        motherLastName: data.motherLastName || "",
        sex: data.sex || "",
        mobile: data.mobile || "",
        site_id: data.site_id || null,
        type_kurator: data.type_kurator || "",
        type_identification: data.type_identification,
        identification: data.identification || "",
        email: data.email || "",
        password: "",
    };

    let sp = (data.specialty ?? "").trim();
    let det = (data.detail_specialty ?? "").trim();
    if (!det && (sp || "").includes(":")) {
        const [k, d = ""] = sp.split(":", 2).map(s => s.trim());
        sp = k;
        det = d;
    }

    const normalizeKeyForType = (type, key) => {
        if (type === "Medicina" && key === "Especialista") return "Especialidad";
        if (type === "Enfermería" && key === "Especialidad") return "Especialista";
        return key;
    };

    kurator.value = { ...base };
    submitted.value = false;
    isEditMode.value = true;
    kuratorDialog.value = true;

    nextTick(() => {
        const fixedKey = normalizeKeyForType(kurator.value.type_kurator, sp);
        kurator.value.specialty = fixedKey;
        kurator.value.detail_specialty = det;
    });
}


function hideDialog() {
    kuratorDialog.value = false;
    submitted.value = false;
    kurator.value = {};
}

function confirmDeleteKurator(data) {
    kurator.value = { ...data };
    deleteKuratorDialog.value = true;
}

async function deleteKurator() {
    try {
        const response = await axios.delete(route("kurators.destroy", kurator.value.kurator_id));
        toast.add({ severity: "success", summary: "Eliminado", detail: response.data?.message || "Personal sanitario eliminado", life: 3000 });
        router.reload({ only: ["kurators"] });
        deleteKuratorDialog.value = false;
    } catch (error) {
        const status = error?.response?.status;
        const serverMessage =
            error?.response?.data?.message ||
            error?.response?.data?.error ||
            "No se pudo eliminar";

        toast.add({
            severity: status === 422 ? "warn" : "error",
            summary: status === 422 ? "Acción no permitida" : "Error",
            detail: serverMessage,
            life: 4000,
        });
        console.error("Error al eliminar personal sanitario:", error.response || error);
    }
}

async function saveKurator() {
    submitted.value = true;
    isSaving.value = true;

    // Validaciones básicas
    const missingRequired =
        !kurator.value.name ||
        !kurator.value.fatherLastName ||
        !kurator.value.sex ||
        !kurator.value.mobile ||
        !kurator.value.site_id ||
        !kurator.value.email ||
        !kurator.value.type_kurator ||
        !kurator.value.specialty ||
        !kurator.value.identification ||
        (!isEditMode.value && !kurator.value.password);

    const missingDetail = needsSpecialtyDetail.value && !kurator.value.detail_specialty;

    if (missingRequired || missingDetail) {
        isSaving.value = false;
        if (missingDetail) {
            toast.add({
                severity: "warn",
                summary: "Falta información",
                detail: "Indica el nombre de la especialidad/subespecialidad/posgrado.",
                life: 4000,
            });
        }
        return;
    }

    const payload = {
        ...kurator.value,
        specialty: kurator.value.specialty,
        detail_specialty: kurator.value.detail_specialty,
    };
    try {
        if (isEditMode.value) {
            if (!kurator.value.kurator_id) {
                toast.add({ severity: "error", summary: "Error", detail: "ID del personal sanitario no definido." });
                isSaving.value = false;
                return;
            }

            await axios.post(route("kurators.update", kurator.value.kurator_id), {
                ...payload,
                _method: "PUT",
            });

            toast.add({ severity: "success", summary: "Actualizado", detail: "Personal sanitario actualizado", life: 3000 });
        } else {
            await axios.post(route("kurators.store"), payload);
            toast.add({ severity: "success", summary: "Guardado", detail: "Personal sanitario creado", life: 3000 });
        }
        hideDialog();
        router.reload({ only: ["kurators"] });
    } catch (e) {
        if (e.response && e.response.status === 422) {
            const errors = e.response.data.errors;
            for (const field in errors) {
                toast.add({
                    severity: "error",
                    summary: "Error de validación",
                    detail: errors[field][0],
                    life: 4000,
                });
            }
        } else {
            toast.add({ severity: "error", summary: "Error", detail: "Error al guardar", life: 3000 });
        }
    } finally {
        isSaving.value = false;
    }
}


watch(() => kurator.value.type_kurator, () => {
    kurator.value.specialty = "";
    kurator.value.detail_specialty = "";
});

function goToAppointments(kurator) {
    router.visit(route('appointments.byKurator', kurator.crypt_kurator));
}

</script>

<template>
    <AppLayout title="Personal sanitario">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" @click="openNew"
                        v-if="userRole === 'admin' || (userPermissions.includes('create_clinical_staff'))" />
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt?.exportCSV()" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="kurators" dataKey="kurator_id" :paginator="true" :rows="10" :filters="filters"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Personal sanitario</h4>
                        <InputText v-model="filters.global.value" placeholder="Buscar..." />
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="kurator_full_name" header="Nombre" />
                <Column field="contactEmail" header="Correo contacto" />
                <Column field="type_kurator" header="Especialidad" />
                <Column field="siteName" header="Sitio" />
                <Column :exportable="false" header="Acciones" style="min-width: 8rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-calendar-clock" severity="info" outlined rounded class="mr-2"
                            v-if="userRole === 'admin' || (userPermissions.includes('show_appointments'))"
                            @click="goToAppointments(data)" v-tooltip.top="'Ver consultas'" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editKurator(data)"
                            v-if="userRole === 'admin' || (userPermissions.includes('edit_clinical_staff'))"
                            v-tooltip.top="'Editar'" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" v-tooltip.top="'Eliminar'"
                            v-if="userRole === 'admin' || (userPermissions.includes('delete_clinical_staff'))"
                            @click="confirmDeleteKurator(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Dialogo Crear/Editar Kurador -->
        <Dialog v-model:visible="kuratorDialog" :modal="true" :style="{ width: '600px' }"
            :header="isEditMode ? 'Editar registro' : 'Crear registro'">
            <form @submit.prevent="saveKurator" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold mb-1">Nombre<span class="text-red-600">*</span></label>
                        <InputText v-model="kurator.name" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.name }" />
                        <small v-if="submitted && !kurator.name" class="text-red-500">El nombre es obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Apellido paterno<span class="text-red-600">*</span></label>
                        <InputText v-model="kurator.fatherLastName" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.fatherLastName }" />
                        <small v-if="submitted && !kurator.fatherLastName" class="text-red-500">El apellido materno es
                            obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Apellido materno</label>
                        <InputText v-model="kurator.motherLastName" class="w-full" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sexo<span class="text-red-600">*</span></label>
                        <Select v-model="kurator.sex" :options="sex" optionLabel="value" optionValue="value" filter
                            class="w-full" placeholder="Seleccione un sexo"
                            :class="{ 'p-invalid': submitted && !kurator.sex }" />
                        <small v-if="submitted && !kurator.sex" class="text-red-500">El sexo es obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teléfono<span class="text-red-600">*</span></label>
                        <InputText v-model="kurator.mobile" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.mobile }" />
                        <small v-if="submitted && !kurator.mobile" class="text-red-500">El teléfono es
                            obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Sitio<span class="text-red-600">*</span></label>
                        <Select v-model="kurator.site_id" :options="sites" optionLabel="siteName" optionValue="id"
                            filter class="w-full" placeholder="Seleccione un sitio"
                            :class="{ 'p-invalid': submitted && !kurator.site_id }" />
                        <small v-if="submitted && !kurator.site_id" class="text-red-500">El sitio es
                            obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Correo de acceso (login)<span
                                class="text-red-600">*</span></label>
                        <InputText v-model="kurator.email" class="w-full" :disabled="isEditMode"
                            :class="{ 'p-invalid': submitted && !kurator.email }" />
                        <small v-if="submitted && !kurator.email" class="text-red-500">El correo de acceso es
                            obligatorio.</small>
                    </div>
                    <div v-if="!isEditMode">
                        <label class="block font-bold mb-1">Contraseña<span class="text-red-600">*</span></label>
                        <Password v-model="kurator.password" toggleMask :feedback="false" class="w-full"
                            inputClass="w-full" :class="{ 'p-invalid': submitted && !kurator.password }" />
                        <small v-if="submitted && !kurator.password" class="text-red-500">La contraseña es
                            obligatoria.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Tipo de personal santario<span
                                class="text-red-600">*</span></label>
                        <Select v-model="kurator.type_kurator" :options="types" optionLabel="value" optionValue="value"
                            filter class="w-full" placeholder="Seleccione un tipo"
                            :class="{ 'p-invalid': submitted && !kurator.type_kurator }" />
                        <small v-if="submitted && !kurator.type_kurator" class="text-red-500">El tipo de kurador es
                            obligatorio.</small>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">
                            Especialidad<span class="text-red-600">*</span>
                        </label>

                        <Select v-model="kurator.specialty" :options="specialtyOptions"
                            :disabled="!kurator.type_kurator" placeholder="Seleccione una opción" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.specialty }" />

                        <small v-if="!kurator.type_kurator" class="text-gray-500">
                            Primero selecciona el tipo de personal sanitario.
                        </small>
                        <small v-if="submitted && !kurator.specialty" class="text-red-500">
                            La especialidad es obligatoria.
                        </small>
                    </div>
                    <div v-if="needsSpecialtyDetail">
                        <label class="block font-bold mb-1">
                            {{ kurator.specialty === 'Subespecialidad' ? 'Nombre de la subespecialidad' :
                                kurator.specialty ===
                                    'Especialista' || kurator.specialty === 'Especialidad' ? 'Nombre de la especialidad' :
                                    kurator.specialty === 'Maestría' ? 'Nombre de la maestría' :
                                        kurator.specialty === 'Doctorado' ? 'Nombre del doctorado' : 'Detalle' }}
                            <span class="text-red-600">*</span>
                        </label>
                        <InputText v-model="kurator.detail_specialty" class="w-full"
                            :class="{ 'p-invalid': submitted && needsSpecialtyDetail && !kurator.detail_specialty }" />
                        <small v-if="submitted && needsSpecialtyDetail && !kurator.detail_specialty"
                            class="text-red-500">
                            Este campo es obligatorio.
                        </small>
                    </div>
                    <!-- <div>
                        <label class="block font-bold mb-1">Tipo de identificación<span
                                class="text-red-600">*</span></label>
                        <Select v-model="kurator.type_identification" :options="typeIdentificationOptions"
                            placeholder="Seleccione un tipo" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.type_identification }" />
                        <small v-if="submitted && !kurator.type_identification" class="text-red-500">El tipo de
                            identificación
                            es obligatorio.</small>
                    </div> -->
                    <div>
                        <label class="block font-bold mb-1">Cédula profesional<span
                                class="text-red-600">*</span></label>
                        <InputText v-model="kurator.identification" class="w-full"
                            :class="{ 'p-invalid': submitted && !kurator.identification }" />
                        <small v-if="submitted && !kurator.identification" class="text-red-500">El número de
                            identificación es
                            obligatorio.</small>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" :disabled="isSaving" />
                    <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" type="submit"
                        :loading="isSaving" :disabled="isSaving" />
                </div>
            </form>
        </Dialog>

        <!-- Confirmar eliminación -->
        <Dialog v-model:visible="deleteKuratorDialog" header="Confirmar" modal style="width:450px">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle text-3xl" />
                <span>¿Estás seguro que deseas eliminar el registro <b>{{ kurator.name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" text @click="deleteKuratorDialog = false" />
                <Button label="Sí" text @click="deleteKurator" />
            </template>
        </Dialog>
    </AppLayout>
</template>
