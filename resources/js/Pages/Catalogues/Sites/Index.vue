<script setup>
import AppLayout from "../../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, Textarea, Select } from "primevue";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    sites: {
        type: Array,
        required: true,
        default: () => [],
    },
    addresses: Array,
});

const toast = useToast();
const dt = ref();

const siteDialog = ref(false);
const deleteSiteDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const site = ref({});
const siteList = ref([...props.sites]);

function openNew() {
    site.value = {};
    submitted.value = false;
    isEditMode.value = false;
    siteDialog.value = true;
}

function editSite(data) {
    site.value = { ...data };
    submitted.value = false;
    isEditMode.value = true;
    siteDialog.value = true;
}

function hideDialog() {
    siteDialog.value = false;
    submitted.value = false;
    site.value = {};
}

async function saveSite() {
    submitted.value = true;

    if (!site.value.siteName?.trim() || !site.value.email_admin?.trim() || !site.value.phone?.trim() || !site.value.address_id) {
        toast.add({ severity: "error", summary: "Error", detail: "Por favor completa los campos requeridos", life: 3000 });
        return;
    }

    isSaving.value = true;

    const payload = {
        siteName: site.value.siteName,
        email_admin: site.value.email_admin,
        phone: site.value.phone,
        description: site.value.description ?? null,
        address_id: site.value.address_id
    };

    try {
        if (isEditMode.value && site.value.id) {
            // Actualizar
            const response = await axios.put(`/sites/${site.value.id}`, payload);
            const res = response.data;

            if (res.success) {
                const index = siteList.value.findIndex((s) => s.id === site.value.id);
                if (index !== -1) {
                    siteList.value[index] = res.data;
                }
                toast.add({ severity: "success", summary: "Actualizado", detail: res.message, life: 3000 });
                siteDialog.value = false;
                site.value = {};
            } else {
                toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
            }
        } else {
            // Crear
            const response = await axios.post('/sites', payload);
            const res = response.data;

            if (res.success) {
                siteList.value.push(res.data);
                toast.add({ severity: "success", summary: "Creado", detail: res.message, life: 3000 });
                siteDialog.value = false;
                site.value = {};
            } else {
                toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
            }
        }
    } catch (error) {
        if (error.response?.status === 422 && error.response.data?.errors) {
            const errors = error.response.data.errors;
            const messages = Object.values(errors).flat().join('\n');
            toast.add({ severity: "error", summary: "Errores de validación", detail: messages, life: 6000 });
        } else {
            const msg = error.response?.data?.message || error.response?.data?.error || "Error inesperado.";
            toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
        }
    } finally {
        isSaving.value = false;
    }
}

function confirmDeleteSite(data) {
    site.value = { ...data };
    deleteSiteDialog.value = true;
}

async function deleteSite() {
    if (!site.value?.id) return;

    try {
        const response = await axios.delete(`/sites/${site.value.id}`);
        const res = response.data;

        if (res.success) {
            siteList.value = siteList.value.filter((s) => s.id !== site.value.id);
            toast.add({ severity: "success", summary: "Eliminado", detail: res.message, life: 3000 });
            deleteSiteDialog.value = false;
            site.value = {};
        } else {
            toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
        }
    } catch (error) {
        const msg = error.response?.data?.message || "Error al eliminar el registro.";
        toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
    }
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Sitios">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>

                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="siteList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Sitios</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="siteName" header="Nombre" style="min-width: 16rem">
                    <template #body="{ data }">{{ data.siteName }}</template>
                </Column>
                <Column field="email_admin" header="Correo" style="min-width: 18rem">
                    <template #body="{ data }">{{ data.email_admin }}</template>
                </Column>
                <Column field="phone" header="Teléfono" style="min-width: 18rem">
                    <template #body="{ data }">{{ data.phone }}</template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSite(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteSite(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="siteDialog" :style="{ width: '450px' }"
            :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
            <div class="flex flex-col gap-6">

                <div>
                    <label class="block font-bold">Nombre<span class="text-red-600">*</span></label>
                    <InputText v-model="site.siteName" required :invalid="submitted && !site.siteName" class="w-full" />
                    <small v-if="submitted && !site.siteName" class="text-red-500">El nombre es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold">Correo<span class="text-red-600">*</span></label>
                    <InputText type="email" v-model="site.email_admin" required
                        :invalid="submitted && !site.email_admin" class="w-full" />
                    <small v-if="submitted && !site.email_admin" class="text-red-500">El correo es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold">Teléfono<span class="text-red-600">*</span></label>
                    <InputText v-model="site.phone" required :invalid="submitted && !site.phone" class="w-full" />
                    <small v-if="submitted && !site.phone" class="text-red-500">El teléfono es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold mb-1">
                        Dirección <span class="text-red-600">*</span>
                    </label>
                    <Select v-model="site.address_id" :options="addresses" optionLabel="name" optionValue="id" filter
                        placeholder="Seleccione una ubicación" class="w-full"
                        :class="{ 'p-invalid': submitted && !site.address_id }" />
                    <small v-if="submitted && !site.address_id" class="text-red-500">La Dirección es requerida.</small>
                </div>
                <div>
                    <label class="block font-bold">Descripción</label>
                    <Textarea v-model="site.description" rows="3" class="w-full" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :loading="isSaving"
                    :disabled="isSaving" @click="saveSite" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteSiteDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="site?.siteName">
                    ¿Estás seguro que deseas eliminar este registro <b>{{ site.siteName }}</b>?
                </span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteSiteDialog = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteSite" />
            </template>
        </Dialog>
    </AppLayout>
</template>
