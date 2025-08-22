<script setup>
import AppLayout from '../../../Layouts/sakai/AppLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  methods: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const toast = useToast();
const dt = ref();

const methodDialog = ref(false);
const deleteMethodDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const method = ref({ id: null, name: '', description: '' });
const methodList = ref([...props.methods]);

function openNew() {
  method.value = { id: null, name: '', description: '' };
  submitted.value = false;
  isEditMode.value = false;
  methodDialog.value = true;
}

function editMethod(data) {
  method.value = { ...data };
  submitted.value = false;
  isEditMode.value = true;
  methodDialog.value = true;
}

function hideDialog() {
  methodDialog.value = false;
  submitted.value = false;
  method.value = { id: null, name: '', description: '' };
}

function saveMethod() {
  submitted.value = true;

  if (!method.value.name?.trim()) {
    toast.add({ severity: 'error', summary: 'Validación', detail: 'El nombre es requerido', life: 3000 });
    return;
  }

  const payload = {
    name: method.value.name.trim(),
    description: method.value.description ?? null,
  };

  isSaving.value = true;

  if (isEditMode.value && method.value.id) {
    // Editar
    axios
      .put(`/methods/${method.value.id}`, payload)
      .then((response) => {
        const res = response.data;
        if (res.success) {
          const index = methodList.value.findIndex(item => item.id === method.value.id);
          if (index !== -1) {
            methodList.value[index] = res.data;
          }
          toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
          methodDialog.value = false;
          method.value = { id: null, name: '', description: '' };
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
    // Crear
    axios
      .post('/methods', payload)
      .then((response) => {
        const res = response.data;
        if (res.success) {
          methodList.value.push(res.data);
          toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
          methodDialog.value = false;
          method.value = { id: null, name: '', description: '' };
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

function confirmDeleteMethod(data) {
  method.value = { ...data };
  deleteMethodDialog.value = true;
}

function deleteMethod() {
  if (!method.value?.id) return;

  axios
    .delete(`/methods/${method.value.id}`)
    .then((response) => {
      const res = response.data;
      if (res.success) {
        methodList.value = methodList.value.filter(item => item.id !== method.value.id);
        toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
        deleteMethodDialog.value = false;
        method.value = { id: null, name: '', description: '' };
      } else {
        toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
      }
    })
    .catch((error) => {
      const msg = error.response?.data?.message || 'Error al eliminar el registro.';
      toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    });
}

function exportCSV() {
  dt.value.exportCSV();
}
</script>

<template>
  <AppLayout title="Métodos del tratamiento">
    <div class="card">
      <Toolbar class="mb-6">
        <template #start>
          <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
        </template>
      </Toolbar>

      <DataTable ref="dt" :value="methodList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
        <template #header>
          <div class="flex flex-wrap gap-2 items-center justify-between">
            <h4 class="m-0">Métodos del tratamiento</h4>
            <div class="flex items-center gap-2">
              <i class="pi pi-search" />
              <InputText v-model="filters.global.value" placeholder="Buscar..." />
            </div>
          </div>
        </template>

        <Column header="#" style="min-width: 6rem">
          <template #body="{ index }">{{ index + 1 }}</template>
        </Column>
        <Column field="name" header="Nombre" style="min-width: 16rem">
          <template #body="{ data }">{{ data.name }}</template>
        </Column>
        <Column :exportable="false" style="min-width: 12rem">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editMethod(data)" />
            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteMethod(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog Crear/Editar -->
    <Dialog v-model:visible="methodDialog" :style="{ width: '450px' }"
      :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
      <div class="flex flex-col gap-6">
        <div>
          <label class="block font-bold mb-2">Nombre<span class="text-red-600">*</span></label>
          <InputText v-model="method.name" required :invalid="submitted && !method.name" class="w-full" />
          <small v-if="submitted && !method.name" class="text-red-500">El nombre es requerido.</small>
        </div>
        <div>
          <label class="block font-bold mb-2">Descripción</label>
          <Textarea v-model="method.description" rows="3" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
        <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
          :loading="isSaving" @click="saveMethod" />
      </template>
    </Dialog>

    <!-- Confirmación eliminar -->
    <Dialog v-model:visible="deleteMethodDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle !text-3xl" />
        <span v-if="method?.name">¿Estás seguro que deseas eliminar este registro <b>{{ method.name
        }}</b>?</span>
        <span v-else>Registro no válido.</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" text @click="deleteMethodDialog = false" />
        <Button label="Sí" icon="pi pi-check" text @click="deleteMethod" />
      </template>
    </Dialog>
  </AppLayout>
</template>
