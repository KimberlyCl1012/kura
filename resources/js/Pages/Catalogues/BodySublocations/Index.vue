<script setup>
import AppLayout from '../../../Layouts/sakai/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import { FilterMatchMode } from '@primevue/core/api';
import { useToast } from 'primevue/usetoast';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Select from 'primevue/select';

const props = defineProps({
  bodyLocations: { type: Array, default: () => [] },
  bodySublocations: { type: Array, default: () => [] }
});

const toast = useToast();
const dt = ref(null);

const bodySublocationDialog = ref(false);
const deleteBodySublocationDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const bodyLocationsNormalized = computed(() =>
  (props.bodyLocations ?? []).map(l => ({
    ...l,
    id: l?.id != null ? String(l.id) : l.id
  }))
);

function toStr(v) {
  return v != null ? String(v) : v;
}

const submitted = ref(false);

const bodySublocation = ref({});

const bodySublocationsList = ref(
  (props.bodySublocations ?? []).map(s => ({
    ...s,
    body_location_id: toStr(s.body_location_id),
    body_location_name: s.body_location_name ?? getBodyLocationNameById(toStr(s.body_location_id)),
  }))
);

watch(() => props.bodySublocations, (val) => {
  bodySublocationsList.value = (val ?? []).map(s => ({
    ...s,
    body_location_id: toStr(s.body_location_id),
    body_location_name: s.body_location_name ?? getBodyLocationNameById(toStr(s.body_location_id)),
  }));
});

function openNew() {
  bodySublocation.value = {};
  submitted.value = false;
  isEditMode.value = false;
  bodySublocationDialog.value = true;
}

function editBodySublocation(data) {
  bodySublocation.value = {
    ...data,
    body_location_id: toStr(data.body_location_id)
  };
  submitted.value = false;
  isEditMode.value = true;
  bodySublocationDialog.value = true;
}

function hideDialog() {
  bodySublocationDialog.value = false;
  submitted.value = false;
  bodySublocation.value = {};
}

function getBodyLocationNameById(encId) {
  const loc = (bodyLocationsNormalized.value ?? []).find(l => l.id === toStr(encId));
  return loc?.name ?? null;
}

async function saveBodySublocations() {
  submitted.value = true;

  if (bodySublocation.value.name?.trim() && bodySublocation.value.body_location_id) {
    const payload = {
      name: bodySublocation.value.name,
      description: bodySublocation.value.description ?? null,
      body_location_id: toStr(bodySublocation.value.body_location_id),
    };

    isSaving.value = true;
    try {
      if (isEditMode.value && bodySublocation.value.id) {
        const { data: res } = await axios.put(
          `/body_sublocations/${bodySublocation.value.id}`,
          payload
        );

        if (res.success) {
          const idx = bodySublocationsList.value.findIndex(i => i.id === bodySublocation.value.id);
          if (idx !== -1) {
            const current = bodySublocationsList.value[idx];

            const body_location_name = getBodyLocationNameById(current.body_location_id);

            bodySublocationsList.value[idx] = {
              ...current,
              ...res.data,
              body_location_name
            };
          }

          toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
          bodySublocationDialog.value = false;
          bodySublocation.value = {};
        } else {
          toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
        }
      } else {
        const { data: res } = await axios.post('/body_sublocations', payload);

        if (res.success) {
          const newItem = {
            ...res.data,
            id: res.data?.id_encrypted ?? res.data?.id,
            body_location_id: res.data?.body_location_id_encrypted ?? bodySublocation.value.body_location_id,
            body_location_name: getBodyLocationNameById(bodySublocation.value.body_location_id),
          };
          bodySublocationsList.value.push(newItem);

          toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
          bodySublocationDialog.value = false;
          bodySublocation.value = {};
        } else {
          toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
        }
      }
    } catch (error) {
      const msg = error.response?.data?.message || 'Error inesperado.';
      toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    } finally {
      isSaving.value = false;
    }
  }
}

function confirmDeleteBodySublocation(data) {
  bodySublocation.value = { ...data };
  deleteBodySublocationDialog.value = true;
}

async function deleteBodySublocation() {
  if (bodySublocation.value?.id) {
    try {
      const { data: res } = await axios.delete(`/body_sublocations/${bodySublocation.value.id}`);
      if (res.success) {
        bodySublocationsList.value = bodySublocationsList.value.filter(i => i.id !== bodySublocation.value.id);
        toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
        deleteBodySublocationDialog.value = false;
        bodySublocation.value = {};
      } else {
        toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
      }
    } catch (error) {
      const msg = error.response?.data?.message || 'Error al eliminar el registro.';
      toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    }
  }
}

function exportCSV() {
  dt.value?.exportCSV?.();
}
</script>

<template>
  <AppLayout title="Ubicaciones corporales (secundarias)">
    <div class="card">
      <Toolbar class="mb-6">
        <template #start>
          <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>

        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
        </template>
      </Toolbar>
      <DataTable ref="dt" :value="bodySublocationsList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
        <template #header>
          <div class="flex flex-wrap gap-2 items-center justify-between">
            <h4 class="m-0">Ubicaciones corporales</h4>
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

        <Column field="body_location_name" header="Ubicación corporal">
          <template #body="{ data }">
            {{ data.body_location_name || getBodyLocationNameById(data.body_location_id) }}
          </template>
        </Column>

        <Column field="name" header="Ubicación secundaria">
          <template #body="{ data }">
            {{ data.name }}
          </template>
        </Column>

        <Column :exportable="false">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editBodySublocation(data)" />
            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteBodySublocation(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Diálogo Crear/Editar -->
    <Dialog v-model:visible="bodySublocationDialog" :style="{ width: '480px' }"
      :header="isEditMode ? 'Editar registro' : 'Crear registro'"
      :modal="true">
      <div class="flex flex-col gap-6">
        <div>
          <label class="block font-bold mb-2">
            Ubicación corporal <span class="text-red-600">*</span>
          </label>
          <Select :key="isEditMode ? (bodySublocation.id || 'edit') : 'new'" v-model="bodySublocation.body_location_id"
            :options="bodyLocationsNormalized" optionLabel="name" optionValue="id" filter
            placeholder="Seleccione una ubicación" class="w-full"
            :class="{ 'p-invalid': submitted && !bodySublocation.body_location_id }" />
          <small v-if="submitted && !bodySublocation.body_location_id" class="text-red-500">
            Debe seleccionar la ubicación corporal.
          </small>
        </div>

        <div>
          <label class="block font-bold mb-2">Nombre <span class="text-red-600">*</span></label>
          <InputText v-model="bodySublocation.name" required :invalid="submitted && !bodySublocation.name"
            class="w-full" />
          <small v-if="submitted && !bodySublocation.name" class="text-red-500">El nombre es requerido.</small>
        </div>

        <div>
          <label class="block font-bold mb-2">Descripción</label>
          <Textarea v-model="bodySublocation.description" rows="3" class="w-full" />
        </div>
      </div>

      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
        <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
          :loading="isSaving" @click="saveBodySublocations" />
      </template>
    </Dialog>

    <!-- Confirmación eliminar -->
    <Dialog v-model:visible="deleteBodySublocationDialog" :style="{ width: '460px' }" header="Confirmar" :modal="true">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle !text-3xl" />
        <span v-if="bodySublocation?.name">
          ¿Estás seguro que deseas eliminar la ubicación corporal (secundaria)
          <b>{{ bodySublocation.name }}</b>?
        </span>
        <span v-else>Registro no válido.</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" text @click="deleteBodySublocationDialog = false" />
        <Button label="Sí" icon="pi pi-check" text @click="deleteBodySublocation" />
      </template>
    </Dialog>
  </AppLayout>
</template>
