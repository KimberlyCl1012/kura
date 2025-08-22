<script setup>
import AppLayout from "../../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, Textarea } from "primevue";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    woundsPhases: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const toast = useToast();
const dt = ref();

const woundsPhaseDialog = ref(false);
const deleteWoundsPhaseDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const woundsPhase = ref({});
const woundsPhaseList = ref([...props.woundsPhases]);

function openNew() {
    woundsPhase.value = {};
    submitted.value = false;
    isEditMode.value = false;
    woundsPhaseDialog.value = true;
}

function editWoundsPhase(data) {
    woundsPhase.value = { ...data };
    submitted.value = false;
    isEditMode.value = true;
    woundsPhaseDialog.value = true;
}

function hideDialog() {
    woundsPhaseDialog.value = false;
    submitted.value = false;
    woundsPhase.value = {};
}

function saveWoundsPhase() {
    submitted.value = true;

    if (woundsPhase.value.name?.trim()) {
        const payload = {
            name: woundsPhase.value.name,
            description: woundsPhase.value.description ?? null,
        };

        isSaving.value = true;

        if (isEditMode.value && woundsPhase.value.id) {
            axios
                .put(`/wound_phases/${woundsPhase.value.id}`, payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        const index = woundsPhaseList.value.findIndex(item => item.id === woundsPhase.value.id);
                        if (index !== -1) woundsPhaseList.value[index] = res.data;
                        toast.add({ severity: "success", summary: "Actualizado", detail: res.message, life: 3000 });
                        hideDialog();
                    } else {
                        toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
                    }
                })
                .catch(error => {
                    const msg = error.response?.data?.message || "Error inesperado.";
                    toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
                })
                .finally(() => isSaving.value = false);
        } else {
            axios
                .post("/wound_phases", payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        woundsPhaseList.value.push(res.data);
                        toast.add({ severity: "success", summary: "Creado", detail: res.message, life: 3000 });
                        hideDialog();
                    } else {
                        toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
                    }
                })
                .catch(error => {
                    const msg = error.response?.data?.message || "Error inesperado.";
                    toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
                })
                .finally(() => isSaving.value = false);
        }
    }
}

function confirmDeleteWoundsPhase(data) {
    woundsPhase.value = { ...data };
    deleteWoundsPhaseDialog.value = true;
}

function deleteWoundsPhase() {
    if (woundsPhase.value?.id) {
        axios
            .delete(`/wound_phases/${woundsPhase.value.id}`)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    woundsPhaseList.value = woundsPhaseList.value.filter(item => item.id !== woundsPhase.value.id);
                    toast.add({ severity: "success", summary: "Eliminado", detail: res.message, life: 3000 });
                    deleteWoundsPhaseDialog.value = false;
                    woundsPhase.value = {};
                } else {
                    toast.add({ severity: "error", summary: "Error", detail: res.message, life: 3000 });
                }
            })
            .catch(error => {
                const msg = error.response?.data?.message || "Error al eliminar.";
                toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
            });
    }
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Fases de la herida">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="woundsPhaseList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Fases de la herida</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="name" header="Nombre" style="min-width: 16rem" />
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editWoundsPhase(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteWoundsPhase(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Diálogo Crear/Editar -->
        <Dialog v-model:visible="woundsPhaseDialog" :style="{ width: '450px' }"
            :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">Nombre<span class="text-red-600">*</span></label>
                    <InputText v-model="woundsPhase.name" required :invalid="submitted && !woundsPhase.name"
                        class="w-full" />
                    <small v-if="submitted && !woundsPhase.name" class="text-red-500">El nombre es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold mb-2">Descripción</label>
                    <Textarea v-model="woundsPhase.description" rows="3" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :loading="isSaving"
                    :disabled="isSaving" @click="saveWoundsPhase" />
            </template>
        </Dialog>

        <!-- Diálogo Confirmar Eliminación -->
        <Dialog v-model:visible="deleteWoundsPhaseDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="woundsPhase?.name">¿Deseas eliminar <b>{{ woundsPhase.name }}</b>?</span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteWoundsPhaseDialog = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteWoundsPhase" />
            </template>
        </Dialog>
    </AppLayout>
</template>
