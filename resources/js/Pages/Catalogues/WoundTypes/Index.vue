<script setup>
import AppLayout from '../../../Layouts/Sakai/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import { InputText, Textarea } from 'primevue';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    woundsTypes: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const toast = useToast();
const dt = ref();

const woundsTypeDialog = ref(false);
const deleteWoundsTypeDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const woundsType = ref({});
const woundsTypeList = ref([...props.woundsTypes]);

function openNew() {
    woundsType.value = {};
    submitted.value = false;
    isEditMode.value = false;
    woundsTypeDialog.value = true;
}

function editWoundsType(data) {
    woundsType.value = { ...data };
    submitted.value = false;
    isEditMode.value = true;
    woundsTypeDialog.value = true;
}

function hideDialog() {
    woundsTypeDialog.value = false;
    submitted.value = false;
    woundsType.value = {};
}

function saveWoundsTypes() {
    submitted.value = true;

    if (woundsType.value.name?.trim()) {
        const payload = {
            name: woundsType.value.name,
            description: woundsType.value.description ?? null,
        };

        isSaving.value = true;

        if (isEditMode.value && woundsType.value.id) {
            axios
                .put(`/wound_types/${woundsType.value.id}`, payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        const index = woundsTypeList.value.findIndex(item => item.id === woundsType.value.id);
                        if (index !== -1) {
                            woundsTypeList.value[index] = res.data;
                        }
                        toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
                        woundsTypeDialog.value = false;
                        woundsType.value = {};
                    } else {
                        toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
                    }
                })
                .catch((error) => {
                    const msg = error.response?.data?.message || 'Error inesperado.';
                    toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
                })
                .finally(() => {
                    isSaving.value = false;
                });
        } else {
            axios
                .post('/wound_types', payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        woundsTypeList.value.push(res.data);
                        toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
                        woundsTypeDialog.value = false;
                        woundsType.value = {};
                    } else {
                        toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
                    }
                })
                .catch((error) => {
                    const msg = error.response?.data?.message || 'Error inesperado.';
                    toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
                })
                .finally(() => {
                    isSaving.value = false;
                });
        }
    }
}

function confirmDeleteWoundsType(data) {
    woundsType.value = { ...data };
    deleteWoundsTypeDialog.value = true;
}

function deleteWoundsType() {
    if (woundsType.value?.id) {
        axios
            .delete(`/wound_types/${woundsType.value.id}`)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    woundsTypeList.value = woundsTypeList.value.filter(item => item.id !== woundsType.value.id);
                    toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
                    deleteWoundsTypeDialog.value = false;
                    woundsType.value = {};
                } else {
                    toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
                }
            })
            .catch((error) => {
                const msg = error.response?.data?.message || 'Error al eliminar el registro.';
                toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
            });
    }
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Tipos de heridas">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>

                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                :value="woundsTypeList"
                dataKey="id"
                :paginator="true"
                :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Tipos de heridas</h4>
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
                <Column field="name" header="Nombre" style="min-width: 16rem">
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>
                </Column>
                <Column field="description" header="Descripción" style="min-width: 20rem">
                    <template #body="{ data }">
                        {{ data.description }}
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editWoundsType(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteWoundsType(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Dialogo Crear/Editar -->
        <Dialog v-model:visible="woundsTypeDialog" :style="{ width: '450px' }"
            :header="isEditMode ? 'Editar tipo de herida' : 'Crear tipo de herida'" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">Nombre</label>
                    <InputText v-model="woundsType.name" required :invalid="submitted && !woundsType.name" class="w-full" />
                    <small v-if="submitted && !woundsType.name" class="text-red-500">El nombre es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold mb-2">Descripción</label>
                    <Textarea v-model="woundsType.description" rows="3" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving" :loading="isSaving" @click="saveWoundsTypes" />
            </template>
        </Dialog>

        <!-- Confirmación eliminar -->
        <Dialog v-model:visible="deleteWoundsTypeDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="woundsType?.name">
                    ¿Estás seguro que deseas eliminar el tipo de herida <b>{{ woundsType.name }}</b>?
                </span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteWoundsTypeDialog = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteWoundsType" />
            </template>
        </Dialog>
    </AppLayout>
</template>
