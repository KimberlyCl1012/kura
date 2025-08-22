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
  bodyLocations: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const toast = useToast();
const dt = ref();

const bodyLocationDialog = ref(false);
const deleteBodyLocationDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const bodyLocation = ref({ id: null, name: '', description: '' });
const bodyLocationList = ref([...props.bodyLocations]);

function openNew() {
  bodyLocation.value = { id: null, name: '', description: '' };
  submitted.value = false;
  isEditMode.value = false;
  bodyLocationDialog.value = true;
}

function editBodyLocation(data) {
  bodyLocation.value = { ...data };
  submitted.value = false;
  isEditMode.value = true;
  bodyLocationDialog.value = true;
}

function hideDialog() {
  bodyLocationDialog.value = false;
  submitted.value = false;
  bodyLocation.value = { id: null, name: '', description: '' };
}

function saveBodyLocation() {
  submitted.value = true;

  if (!bodyLocation.value.name?.trim()) {
    toast.add({ severity: 'error', summary: 'Validación', detail: 'El nombre es requerido', life: 3000 });
    return;
  }

  const payload = {
    name: bodyLocation.value.name.trim(),
    description: bodyLocation.value.description ?? null,
  };

  isSaving.value = true;

  if (isEditMode.value && bodyLocation.value.id) {
    // Editar
    axios
      .put(`/body_locations/${bodyLocation.value.id}`, payload)
      .then((response) => {
        const res = response.data;
        if (res.success) {
          const index = bodyLocationList.value.findIndex(item => item.id === bodyLocation.value.id);
          if (index !== -1) {
            bodyLocationList.value[index] = res.data;
          }
          toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
          bodyLocationDialog.value = false;
          bodyLocation.value = { id: null, name: '', description: '' };
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
      .post('/body_locations', payload)
      .then((response) => {
        const res = response.data;
        if (res.success) {
          bodyLocationList.value.push(res.data);
          toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
          bodyLocationDialog.value = false;
          bodyLocation.value = { id: null, name: '', description: '' };
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

function confirmDeleteBodyLocation(data) {
  bodyLocation.value = { ...data };
  deleteBodyLocationDialog.value = true;
}

function deleteBodyLocation() {
  if (!bodyLocation.value?.id) return;

  axios
    .delete(`/body_locations/${bodyLocation.value.id}`)
    .then((response) => {
      const res = response.data;
      if (res.success) {
        bodyLocationList.value = bodyLocationList.value.filter(item => item.id !== bodyLocation.value.id);
        toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
        deleteBodyLocationDialog.value = false;
        bodyLocation.value = { id: null, name: '', description: '' };
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
  <AppLayout title="Ubicaciones corporales">
    <div class="card">
      <Toolbar class="mb-6">
        <template #start>
          <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
        </template>
      </Toolbar>

      <DataTable ref="dt" :value="bodyLocationList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
        <template #header>
          <div class="flex flex-wrap gap-2 items-center justify-between">
            <h4 class="m-0">Ubicaciones corporales</h4>
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
            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editBodyLocation(data)" />
            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteBodyLocation(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog Crear/Editar -->
    <Dialog v-model:visible="bodyLocationDialog" :style="{ width: '450px' }"
      :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
      <div class="flex flex-col gap-6">
        <div>
          <label class="block font-bold mb-2">Nombre<span class="text-red-600">*</span></label>
          <InputText v-model="bodyLocation.name" required :invalid="submitted && !bodyLocation.name" class="w-full" />
          <small v-if="submitted && !bodyLocation.name" class="text-red-500">El nombre es requerido.</small>
        </div>
        <div>
          <label class="block font-bold mb-2">Descripción</label>
          <Textarea v-model="bodyLocation.description" rows="3" class="w-full" />
        </div>
      </div>
      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
        <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
          :loading="isSaving" @click="saveBodyLocation" />
      </template>
    </Dialog>

    <!-- Confirmación eliminar -->
    <Dialog v-model:visible="deleteBodyLocationDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle !text-3xl" />
        <span v-if="bodyLocation?.name">¿Estás seguro que deseas eliminar este registro <b>{{ bodyLocation.name
            }}</b>?</span>
        <span v-else>Registro no válido.</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" text @click="deleteBodyLocationDialog = false" />
        <Button label="Sí" icon="pi pi-check" text @click="deleteBodyLocation" />
      </template>
    </Dialog>
  </AppLayout>
</template>
