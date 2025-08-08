<script setup>
import AppLayout from '../../../Layouts/sakai/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import { InputText, Textarea } from 'primevue';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    woundsTypes: Array,
    woundsSubtypes: Array
});

const toast = useToast();
const dt = ref();

const woundsSubtypeDialog = ref(false);
const deleteWoundsSubtypeDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const woundsSubtype = ref({});
const woundsSubtypeList = ref([...props.woundsSubtypes]);

function openNew() {
    woundsSubtype.value = {};
    submitted.value = false;
    isEditMode.value = false;
    woundsSubtypeDialog.value = true;
}

function editWoundsSubtype(data) {
    woundsSubtype.value = { ...data };
    submitted.value = false;
    isEditMode.value = true;
    woundsSubtypeDialog.value = true;
}

function hideDialog() {
    woundsSubtypeDialog.value = false;
    submitted.value = false;
    woundsSubtype.value = {};
}

function saveWoundsSubtypes() {
    submitted.value = true;

    if (woundsSubtype.value.name?.trim()) {
        const payload = {
            name: woundsSubtype.value.name,
            description: woundsSubtype.value.description ?? null,
            wound_type_id: woundsSubtype.value.wound_type_id,
        };

        isSaving.value = true;

        if (isEditMode.value && woundsSubtype.value.id) {
            axios
                .put(`/wound_subtypes/${woundsSubtype.value.id}`, payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        const index = woundsSubtypeList.value.findIndex(item => item.id === woundsSubtype.value.id);
                        if (index !== -1) {
                            woundsSubtypeList.value[index] = res.data;
                        }
                        toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
                        woundsSubtypeDialog.value = false;
                        woundsSubtype.value = {};
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
                .post('/wound_subtypes', payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        woundsSubtypeList.value.push(res.data);
                        toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
                        woundsSubtypeDialog.value = false;
                        woundsSubtype.value = {};
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

function confirmDeleteWoundsSubtype(data) {
    woundsSubtype.value = { ...data };
    deleteWoundsSubtypeDialog.value = true;
}

function deleteWoundsSubtype() {
    if (woundsSubtype.value?.id) {
        axios
            .delete(`/wound_subtypes/${woundsSubtype.value.id}`)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    woundsSubtypeList.value = woundsSubtypeList.value.filter(item => item.id !== woundsSubtype.value.id);
                    toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
                    deleteWoundsSubtypeDialog.value = false;
                    woundsSubtype.value = {};
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

            <DataTable ref="dt" :value="woundsSubtypeList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Subtipos de heridas</h4>
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
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editWoundsSubtype(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteWoundsSubtype(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Dialogo Crear/Editar -->
        <Dialog v-model:visible="woundsSubtypeDialog" :style="{ width: '450px' }"
            :header="isEditMode ? 'Editar tipo de herida' : 'Crear tipo de herida'" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">
                        Tipo de herida <span class="text-red-600">*</span>
                    </label>
                    <Select v-model="woundsSubtype.wound_type_id" :options="woundsTypes" optionLabel="name"
                        optionValue="id" filter placeholder="Seleccione un tipo" class="w-full"
                        :class="{ 'p-invalid': submitted && !woundsSubtype.wound_type_id }" />
                    <small v-if="submitted && !woundsSubtype.wound_type_id" class="text-red-500">
                        Debe seleccionar el tipo de herida.
                    </small>
                </div>
                <div>
                    <label class="block font-bold mb-2">Nombre<span class="text-red-600">*</span></label>
                    <InputText v-model="woundsSubtype.name" required :invalid="submitted && !woundsSubtype.name"
                        class="w-full" />
                    <small v-if="submitted && !woundsSubtype.name" class="text-red-500">El nombre es requerido.</small>
                </div>
                <div>
                    <label class="block font-bold mb-2">Descripción</label>
                    <Textarea v-model="woundsSubtype.description" rows="3" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
                    :loading="isSaving" @click="saveWoundsSubtypes" />
            </template>
        </Dialog>

        <!-- Confirmación eliminar -->
        <Dialog v-model:visible="deleteWoundsSubtypeDialog" :style="{ width: '450px' }" header="Confirmar"
            :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="woundsSubtype?.name">
                    ¿Estás seguro que deseas eliminar el tipo de herida <b>{{ woundsSubtype.name }}</b>?
                </span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteWoundsSubtypeDialog = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteWoundsSubtype" />
            </template>
        </Dialog>
    </AppLayout>
</template>
