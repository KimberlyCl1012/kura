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
  methods: { type: Array, default: () => [] },
  submethods: { type: Array, default: () => [] }
});

const toast = useToast();
const dt = ref(null);

const submethodsDialog = ref(false);
const deleteSubmethodsDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const methodsNormalized = computed(() =>
  (props.methods ?? []).map(l => ({
    ...l,
    id: l?.id != null ? String(l.id) : l.id
  }))
);

function toStr(v) {
  return v != null ? String(v) : v;
}

const submitted = ref(false);

const submethod = ref({});

const submethodsList = ref(
  (props.submethods ?? []).map(s => ({
    ...s,
    treatment_method_id: toStr(s.treatment_method_id),
    method_name: s.method_name ?? getSubmethodNameById(toStr(s.treatment_method_id)),
  }))
);

watch(() => props.submethods, (val) => {
  submethodsList.value = (val ?? []).map(s => ({
    ...s,
    treatment_method_id: toStr(s.treatment_method_id),
    method_name: s.method_name ?? getSubmethodNameById(toStr(s.treatment_method_id)),
  }));
});

function openNew() {
  submethod.value = {};
  submitted.value = false;
  isEditMode.value = false;
  submethodsDialog.value = true;
}

function editSubmethod(data) {
  submethod.value = {
    ...data,
    treatment_method_id: toStr(data.treatment_method_id)
  };
  submitted.value = false;
  isEditMode.value = true;
  submethodsDialog.value = true;
}

function hideDialog() {
  submethodsDialog.value = false;
  submitted.value = false;
  submethod.value = {};
}

function getSubmethodNameById(id) {
  const loc = (methodsNormalized.value ?? []).find(l => l.id === toStr(id));
  return loc?.name ?? null;
}

async function saveSubmethods() {
  submitted.value = true;

  if (submethod.value.name?.trim() && submethod.value.treatment_method_id) {
    const payload = {
      name: submethod.value.name,
      description: submethod.value.description ?? null,
      treatment_method_id: Number(submethod.value.treatment_method_id),
    };

    isSaving.value = true;
    try {
      if (isEditMode.value && submethod.value.id) {
        const { data: res } = await axios.put(
          `/submethods/${submethod.value.id}`,
          payload
        );

        if (res.success) {
          const idx = submethodsList.value.findIndex(i => i.id === submethod.value.id);
          if (idx !== -1) {
            const updated = {
              ...submethodsList.value[idx],
              ...res.data,
              id: String(res.data.id),
              treatment_method_id: String(res.data.treatment_method_id),
            };
            updated.method_name = getSubmethodNameById(updated.treatment_method_id);
            submethodsList.value[idx] = updated;
          }

          toast.add({ severity: 'success', summary: 'Actualizado', detail: res.message, life: 3000 });
          submethodsDialog.value = false;
          submethod.value = {};
        } else {
          toast.add({ severity: 'error', summary: 'Error', detail: res.message, life: 3000 });
        }
      } else {
        const { data: res } = await axios.post('/submethods', payload);

        if (res.success) {
          const newItem = {
            ...res.data,
            id: String(res.data.id),
            treatment_method_id: String(res.data.treatment_method_id),
            method_name: getSubmethodNameById(String(res.data.treatment_method_id)),
          };
          submethodsList.value.push(newItem);

          toast.add({ severity: 'success', summary: 'Creado', detail: res.message, life: 3000 });
          submethodsDialog.value = false;
          submethod.value = {};
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

function confirmDeleteSubmethod(data) {
  submethod.value = { ...data };
  deleteSubmethodsDialog.value = true;
}

async function deleteSubmethod() {
  if (submethod.value?.id) {
    try {
      const { data: res } = await axios.delete(`/submethods/${submethod.value.id}`);
      if (res.success) {
        submethodsList.value = submethodsList.value.filter(i => i.id !== submethod.value.id);
        toast.add({ severity: 'success', summary: 'Eliminado', detail: res.message, life: 3000 });
        deleteSubmethodsDialog.value = false;
        submethod.value = {};
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
  <AppLayout title="Submetodos del tratamiento">
    <div class="card">
      <Toolbar class="mb-6">
        <template #start>
          <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>

        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
        </template>
      </Toolbar>
      <DataTable ref="dt" :value="submethodsList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
        <template #header>
          <div class="flex flex-wrap gap-2 items-center justify-between">
            <h4 class="m-0">Submetodos del tratamiento</h4>
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

        <Column field="method_name" header="Método del tratamiento">
          <template #body="{ data }">
            {{ data.method_name || getSubmethodNameById(data.treatment_method_id) }}
          </template>
        </Column>

        <Column field="name" header="Submetodo del tratamiento">
          <template #body="{ data }">
            {{ data.name }}
          </template>
        </Column>

        <Column :exportable="false">
          <template #body="{ data }">
            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editSubmethod(data)" />
            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteSubmethod(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Diálogo Crear/Editar -->
    <Dialog v-model:visible="submethodsDialog" :style="{ width: '480px' }"
      :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
      <div class="flex flex-col gap-6">
        <div>
          <label class="block font-bold mb-2">
            Método del tratamiento <span class="text-red-600">*</span>
          </label>
          <Select :key="isEditMode ? (submethod.id || 'edit') : 'new'" v-model="submethod.treatment_method_id"
            :options="methodsNormalized" optionLabel="name" optionValue="id" filter placeholder="Seleccione una opción"
            class="w-full" :class="{ 'p-invalid': submitted && !submethod.treatment_method_id }" />
          <small v-if="submitted && !submethod.treatment_method_id" class="text-red-500">
            Debe seleccionar el Método del tratamiento.
          </small>
        </div>

        <div>
          <label class="block font-bold mb-2">Nombre <span class="text-red-600">*</span></label>
          <InputText v-model="submethod.name" required :invalid="submitted && !submethod.name" class="w-full" />
          <small v-if="submitted && !submethod.name" class="text-red-500">El nombre es requerido.</small>
        </div>

        <div>
          <label class="block font-bold mb-2">Descripción</label>
          <Textarea v-model="submethod.description" rows="3" class="w-full" />
        </div>
      </div>

      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
        <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
          :loading="isSaving" @click="saveSubmethods" />
      </template>
    </Dialog>

    <!-- Confirmación eliminar -->
    <Dialog v-model:visible="deleteSubmethodsDialog" :style="{ width: '460px' }" header="Confirmar" :modal="true">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle !text-3xl" />
        <span v-if="submethod?.name">
          ¿Estás seguro que deseas eliminar el Submetodo del tratamiento
          <b>{{ submethod.name }}</b>?
        </span>
        <span v-else>Registro no válido.</span>
      </div>
      <template #footer>
        <Button label="No" icon="pi pi-times" text @click="deleteSubmethodsDialog = false" />
        <Button label="Sí" icon="pi pi-check" text @click="deleteSubmethod" />
      </template>
    </Dialog>
  </AppLayout>
</template>
