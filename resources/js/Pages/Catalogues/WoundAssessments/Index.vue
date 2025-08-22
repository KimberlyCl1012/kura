<script setup>
import AppLayout from '../../../Layouts/sakai/AppLayout.vue';
import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { FilterMatchMode } from '@primevue/core/api';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const props = defineProps({
    assessments: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
});

const toast = useToast();
const dt = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const list = ref((props.assessments ?? []).map(i => ({ ...i, id: String(i.id) })));
watch(() => props.assessments, (val) => {
    list.value = (val ?? []).map(i => ({ ...i, id: String(i.id) }));
});

const typeOptions = computed(() =>
    (props.types ?? []).map(t =>
        typeof t === 'string' ? ({ label: t, value: t }) : t
    )
);

// Filtro local por tipo (no hace peticiones; filtra en cliente)
const selectedFilterType = ref(null);
const filteredList = computed(() => {
    if (!selectedFilterType.value) return list.value;
    return list.value.filter(i => i.type === selectedFilterType.value);
});

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);
const submitted = ref(false);

const row = reactive({
    id: null,
    type: null,
    name: '',
    description: '',
    state: true,
});

const titleDialog = computed(() => (isEditMode.value ? 'Editar registro' : 'Crear registro'));

function resetRow() {
    row.id = null;
    row.type = null;
    row.name = '';
    row.description = '';
    row.state = true;
}

function openNew() {
    resetRow();
    submitted.value = false;
    isEditMode.value = false;
    dialogVisible.value = true;
}

function editRow(data) {
    row.id = data.id;
    row.type = data.type;
    row.name = data.name;
    row.description = data.description ?? '';
    row.state = !!data.state;
    submitted.value = false;
    isEditMode.value = true;
    dialogVisible.value = true;
}

function hideDialog() {
    dialogVisible.value = false;
    submitted.value = false;
    resetRow();
}

function confirmDelete(data) {
    row.id = data.id;
    row.name = data.name;
    deleteDialogVisible.value = true;
}

async function saveRow() {
    submitted.value = true;
    if (!row.type || !row.name?.trim()) return;

    isSaving.value = true;
    try {
        const payload = {
            type: row.type,
            name: row.name.trim(),
            description: row.description?.trim() || null,
            state: !!row.state,
        };

        if (isEditMode.value && row.id) {
            const { data: res } = await axios.put(`/wound_assessment/${row.id}`, payload);
            if (res.success) {
                const idx = list.value.findIndex(i => i.id === String(row.id));
                if (idx !== -1) list.value[idx] = { ...res.data, id: String(res.data.id) };
                toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
                dialogVisible.value = false;
                resetRow();
            } else {
                toast.add({ severity: 'error', summary: 'Error', detail: res.message || 'No se pudo actualizar.', life: 4000 });
            }
        } else {
            const { data: res } = await axios.post('/wound_assessment', payload);
            if (res.success) {
                list.value.push({ ...res.data, id: String(res.data.id) });
                toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
                dialogVisible.value = false;
                resetRow();
            } else {
                toast.add({ severity: 'error', summary: 'Error', detail: res.message || 'No se pudo crear.', life: 4000 });
            }
        }
    } catch (e) {
        const msg = e?.response?.data?.message || 'Error al guardar el registro.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    } finally {
        isSaving.value = false;
    }
}

async function deleteRow() {
    if (!row.id) return;
    try {
        const { data: res } = await axios.delete(`/wound_assessment/${row.id}`);
        if (res.success) {
            list.value = list.value.filter(i => i.id !== String(row.id));
            toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
            deleteDialogVisible.value = false;
            resetRow();
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: res.message || 'No se pudo eliminar.', life: 4000 });
        }
    } catch (e) {
        const msg = e?.response?.data?.message || 'Error al eliminar el registro.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    }
}

function exportCSV() {
    dt.value?.exportCSV?.();
}

</script>

<template>
    <AppLayout title="Evaluciones de la herida">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>
                <template #end>
                    <div class="flex items-center gap-2">
                        <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                    </div>
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="list" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Evaluciones de la herida</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>
                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">
                        {{ index + 1 }}
                    </template>
                </Column>

                <Column field="type" header="Tipo" style="min-width: 12rem">
                    <template #body="{ data }">
                        {{(typeOptions.find(t => t.value === data.type)?.label) || data.type}}
                    </template>
                </Column>
                <Column field="name" header="Nombre" style="min-width: 14rem" />
                <Column :exportable="false" header="Acciones" style="min-width: 10rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editRow(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDelete(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Crear/Editar -->
        <Dialog v-model:visible="dialogVisible" :style="{ width: '520px' }" :header="titleDialog" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">Tipo <span class="text-red-600">*</span></label>
                    <Select v-model="row.type" :options="typeOptions" optionLabel="label" optionValue="value" filter
                        placeholder="Seleccione un tipo" class="w-full"
                        :class="{ 'p-invalid': submitted && !row.type }" />
                    <small v-if="submitted && !row.type" class="text-red-500">El tipo es requerido.</small>
                </div>

                <div>
                    <label class="block font-bold mb-2">Nombre <span class="text-red-600">*</span></label>
                    <InputText v-model="row.name" :invalid="submitted && !row.name" class="w-full" />
                    <small v-if="submitted && !row.name" class="text-red-500">El nombre es requerido.</small>
                </div>

                <div>
                    <label class="block font-bold mb-2">Descripción</label>
                    <Textarea v-model="row.description" rows="3" class="w-full" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
                    :loading="isSaving" @click="saveRow" />
            </template>
        </Dialog>

        <!-- Eliminar -->
        <Dialog v-model:visible="deleteDialogVisible" :style="{ width: '460px' }" header="Confirmar" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="row?.name">
                    ¿Estás seguro que deseas eliminar el registro <b>{{ row.name }}</b>?
                </span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteDialogVisible = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteRow" />
            </template>
        </Dialog>
    </AppLayout>
</template>
