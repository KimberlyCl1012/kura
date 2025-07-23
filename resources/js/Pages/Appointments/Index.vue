<script setup>
import AppLayout from "@/Layouts/Sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import { router } from "@inertiajs/vue3";

import {
  InputText, Toolbar, DataTable, Column,
  Dialog, Button, IconField, InputIcon,
  DatePicker, Select, Tooltip
} from "primevue";

const props = defineProps({
  sites: Array,
  kurators: Array,
  patientRecords: Array,
  appointments: Array,
});

const dt = ref();
const toast = useToast();

const filters = ref({
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const dialog = ref(false);
const deleteDialog = ref(false);
const submitted = ref(false);
const isSaving = ref(false);
const selected = ref(null);

const appointment = ref({
  dateStartVisit: null,
  site_id: null,
  health_record_id: null,
  kurator_id: null,
  typeVisit: null,
});

const typeOptions = [
  { name: "Valoración" },
  { name: "Urgencia" },
  { name: "Seguimiento" },
];

const fieldLabels = {
  dateStartVisit: "Fecha de la consulta",
  site_id: "Sitio",
  health_record_id: "Expediente",
  kurator_id: "Kurador",
  typeVisit: "Tipo",
};

function openNew() {
  appointment.value = {
    dateStartVisit: null,
    site_id: null,
    health_record_id: null,
    kurator_id: null,
    typeVisit: null,
  };
  submitted.value = false;
  dialog.value = true;
}

const today = new Date(); // Fecha mínima permitida (hoy)

function formatDateToMySQL(date) {
  if (!date) return null;
  const d = new Date(date);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

async function save() {
  submitted.value = true;
  for (const field of ['dateStartVisit', 'site_id', 'health_record_id', 'kurator_id', 'typeVisit']) {
    if (!appointment.value[field]) {
      const label = fieldLabels[field] || field;
      toast.add({
        severity: "warn",
        summary: "Validación",
        detail: `El campo ${label} es obligatorio`,
        life: 3000,
      });
      return;
    }
  }

  isSaving.value = true;
  try {
    const payload = {
      ...appointment.value,
      dateStartVisit: formatDateToMySQL(appointment.value.dateStartVisit),
    };

    await axios.post(route('appointments.store'), payload);
    toast.add({ severity: "success", summary: "Guardado", detail: "Consulta creada", life: 3000 });
    dialog.value = false;
    router.reload();
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error al guardar',
      detail: error.response?.data?.message || 'Ocurrió un error inesperado',
      life: 3000
    });
  } finally {
    isSaving.value = false;
  }
}

function confirmDelete(item) {
  selected.value = item;
  deleteDialog.value = true;
}

async function destroy() {
  isSaving.value = true;
  try {
    await axios.delete(route('appointments.destroy', selected.value.id));

    toast.add({ severity: 'success', summary: 'Eliminado', detail: "Consulta eliminada", life: 3000 });
    deleteDialog.value = false;
    router.reload();
  } catch (error) {
    toast.add({
      severity: 'warn',
      summary: 'Advertencia',
      detail: error.response?.data?.message || 'No se pudo eliminar la consulta',
      life: 4000,
    });
  } finally {
    isSaving.value = false;
  }
}

function clearFilter() {
  filters.value.global.value = null;
}
</script>

<template>
  <AppLayout title="Consultas">
    <div class="card">
      <Toolbar class="mb-6">
        <template #start>
          <Button label="Nueva" icon="pi pi-plus" severity="secondary" @click="openNew" />
        </template>
        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt.exportCSV()" />
        </template>
      </Toolbar>

      <DataTable ref="dt" :value="props.appointments" dataKey="id" :paginator="true" :rows="10" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} consultas">
        <template #header>
          <div class="flex justify-between items-center">
            <h4 class="m-0">Consultas</h4>
            <IconField>
              <InputIcon><i class="pi pi-search" /></InputIcon>
              <InputText v-model="filters.global.value" placeholder="Buscar..." />
            </IconField>
          </div>
        </template>

        <Column header="#" style="min-width: 6rem">
          <template #body="{ index }">{{ index + 1 }}</template>
        </Column>
        <Column field="dateStartVisit" header="Fecha" />
        <Column field="site_name" header="Sitio" />
        <Column field="health_record_uuid" header="Expediente" />
        <Column field="kurator_full_name" header="Kurador" />
        <Column field="typeVisit" header="Tipo" />
        <Column :exportable="false" header="Acciones" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-trash" outlined rounded severity="danger" v-tooltip.top="'Eliminar'"
              @click="confirmDelete(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Diálogo nuevo -->
    <Dialog v-model:visible="dialog" modal :style="{ width: '600px' }" header="Registrar Consulta">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="dateStartVisit" class="block font-bold mb-1">
              Fecha de la consulta
              <span class="text-red-500">*</span>
            </label>
            <DatePicker id="dateStartVisit" inputId="dateStartVisit" v-model="appointment.dateStartVisit" class="w-full"
              variant="filled" showIcon iconDisplay="input" :minDate="today" />
            <small v-if="submitted && !appointment.dateStartVisit" class="text-red-500">Campo requerido</small>
          </div>

          <!-- Sitio -->
          <div>
            <label for="site_id" class="block font-bold mb-1">Sitio <span class="text-red-500">*</span></label>
            <Select id="site_id" v-model="appointment.site_id" :options="props.sites" optionLabel="siteName"
              optionValue="id" filter class="w-full" placeholder="Seleccione un sitio" :class="{
                'p-invalid': submitted && !appointment.site_id,
              }" />
            <small v-if="submitted && !appointment.site_id" class="text-red-500">El sitio es obligatorio.</small>
          </div>

          <!-- Expediente -->
          <div>
            <label for="health_record_id" class="block font-bold mb-1">Expediente
              <span class="text-red-500">*</span></label>
            <Select id="health_record_id" v-model="appointment.health_record_id" :options="props.patientRecords"
              optionLabel="full_name" optionValue="health_record_id" filter class="w-full"
              placeholder="Seleccione un expediente" :class="{
                'p-invalid':
                  submitted && !appointment.health_record_id,
              }" />
            <small v-if="submitted && !appointment.health_record_id" class="text-red-500">El expediente es
              obligatorio.</small>
          </div>

          <!-- Kurador -->
          <div>
            <label for="kurator_id" class="block font-bold mb-1">Kurador <span class="text-red-500">*</span></label>
            <Select id="kurator_id" v-model="appointment.kurator_id" :options="props.kurators" optionLabel="full_name"
              optionValue="kurator_id" filter class="w-full" placeholder="Seleccione un kurador" :class="{
                'p-invalid':
                  submitted && !appointment.kurator_id,
              }" />
            <small v-if="submitted && !appointment.kurator_id" class="text-red-500">El kurador es obligatorio.</small>
          </div>

          <!-- Tipo de Visita -->
          <div>
            <label for="typeVisit" class="block font-bold mb-1">Tipo de visita
              <span class="text-red-500">*</span></label>
            <Select id="typeVisit" v-model="appointment.typeVisit" :options="typeOptions" optionLabel="name"
              optionValue="name" class="w-full" placeholder="Seleccione el tipo" :class="{
                'p-invalid':
                  submitted && !appointment.typeVisit,
              }" />
            <small v-if="submitted && !appointment.typeVisit" class="text-red-500">El tipo de visita es
              obligatorio.</small>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <Button label="Cancelar" icon="pi pi-times" text @click="dialog = false" :disabled="isSaving" />
          <Button label="Guardar" icon="pi pi-check" type="submit" :loading="isSaving" :disabled="isSaving" />
        </div>
      </form>
    </Dialog>

    <!-- Confirmación eliminación -->
    <Dialog v-model:visible="deleteDialog" header="Confirmar" modal style="width: 450px">
      <div class="flex items-center gap-4">
        <i class="pi pi-exclamation-triangle text-3xl" />
        <span>¿Deseas eliminar esta consulta?</span>
      </div>
      <template #footer>
        <Button label="No" text @click="deleteDialog = false" />
        <Button label="Sí" text severity="danger" @click="destroy" />
      </template>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.pi-2x {
  font-size: 16px;
}
</style>
