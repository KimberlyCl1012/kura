<script setup>
import AppLayout from "../../Layouts/Sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import {
  InputText, Toolbar, DataTable, Column,
  Dialog, Button, IconField, InputIcon,
  DatePicker, Select, Tooltip
} from "primevue";
import { router } from "@inertiajs/vue3";

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
  dateOfBirth: null,
  site_id: null,
  health_record_id: null,
  kurator_id: null,
  type: null,
});

const typeOptions = [
  { name: "Valoración" },
  { name: "Seguimiento" },
  { name: "Urgencia" },
];

function openNew() {
  appointment.value = {
    dateOfBirth: null,
    site_id: null,
    health_record_id: null,
    kurator_id: null,
    type: null,
  };
  submitted.value = false;
  dialog.value = true;
}

async function save() {
  submitted.value = true;
  for (const field of ['dateOfBirth','site_id','health_record_id','kurator_id','type']) {
    if (!appointment.value[field]) {
      toast.add({ severity: 'warn', summary: 'Validación', detail: `El campo ${field} es obligatorio.`, life: 3000 });
      return;
    }
  }
  isSaving.value = true;
  try {
    await router.post(route('appointments.store'), appointment.value);
    toast.add({ severity: 'success', summary: 'Consulta creada', life: 3000 });
    dialog.value = false;
    router.reload();
  } catch {
    toast.add({ severity: 'error', summary: 'Error al guardar', life: 3000 });
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
    await router.delete(route('appointments.destroy', selected.value.id));
    toast.add({ severity: 'success', summary: 'Consulta eliminada', life: 3000 });
    deleteDialog.value = false;
    router.reload();
  } catch {
    toast.add({ severity: 'error', summary: 'Error al eliminar', life: 3000 });
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
          <Button label="Nueva consulta" icon="pi pi-plus" severity="secondary" @click="openNew" />
        </template>
        <template #end>
          <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="dt.exportCSV()" />
        </template>
      </Toolbar>

      <DataTable ref="dt" :value="props.appointments" dataKey="id"
                 :paginator="true" :rows="10" :filters="filters"
                 paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                 :rowsPerPageOptions="[5,10,25]"
                 currentPageReportTemplate="Ver {first} al {last} de {totalRecords} consultas">
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
        <Column field="dateOfBirth" header="Fecha" />
        <Column field="site.name" header="Sitio" />
        <Column field="healthRecord.record_uuid" header="Expediente" />
        <Column field="kurator.user_uuid" header="Kurador" />
        <Column field="type" header="Tipo" />
        <Column :exportable="false" header="Acciones" style="min-width: 8rem">
          <template #body="{ data }">
            <Button icon="pi pi-trash" outlined rounded severity="danger"
                    v-tooltip.top="'Eliminar consulta'"
                    @click="confirmDelete(data)" />
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Dialog Nuevo / Editar -->
    <Dialog v-model:visible="dialog" modal :style="{ width: '600px' }" header="Registrar Consulta">
      <form @submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Fecha -->
          <div>
            <label class="block font-bold mb-1">Fecha de la consulta <span class="text-red-500">*</span></label>
            <DatePicker v-model="appointment.dateOfBirth" showIcon class="w-full" />
            <small v-if="submitted && !appointment.dateOfBirth" class="text-red-500">Campo requerido</small>
          </div>

          <!-- Sitio -->
          <div>
            <label class="block font-bold mb-1">Sitio <span class="text-red-500">*</span></label>
            <Select v-model="appointment.site_id" :options="props.sites" optionLabel="name" optionValue="id"
                    filter class="w-full"
                    :class="{ 'p-invalid': submitted && !appointment.site_id }" />
            <small v-if="submitted && !appointment.site_id" class="text-red-500">Campo requerido</small>
          </div>

          <!-- Expediente -->
          <div>
            <label class="block font-bold mb-1">Expediente <span class="text-red-500">*</span></label>
            <Select v-model="appointment.health_record_id" :options="props.patientRecords" optionLabel="full_name"
                    optionValue="health_record_id" filter class="w-full"
                    :class="{ 'p-invalid': submitted && !appointment.health_record_id }" />
            <small v-if="submitted && !appointment.health_record_id" class="text-red-500">Campo requerido</small>
          </div>

          <!-- Kurador -->
          <div>
            <label class="block font-bold mb-1">Kurador <span class="text-red-500">*</span></label>
            <Select v-model="appointment.kurator_id" :options="props.kurators" optionLabel="full_name"
                    optionValue="kurator_id" filter class="w-full"
                    :class="{ 'p-invalid': submitted && !appointment.kurator_id }" />
            <small v-if="submitted && !appointment.kurator_id" class="text-red-500">Campo requerido</small>
          </div>

          <!-- Tipo -->
          <div>
            <label class="block font-bold mb-1">Tipo <span class="text-red-500">*</span></label>
            <Select v-model="appointment.type" :options="typeOptions" optionLabel="name" optionValue="name"
                    filter class="w-full"
                    :class="{ 'p-invalid': submitted && !appointment.type }" />
            <small v-if="submitted && !appointment.type" class="text-red-500">Campo requerido</small>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <Button label="Cancelar" icon="pi pi-times" text @click="dialog = false" :disabled="isSaving" />
          <Button label="Guardar" icon="pi pi-check" type="submit" :loading="isSaving" :disabled="isSaving" />
        </div>
      </form>
    </Dialog>

    <!-- Confirmación -->
    <Dialog v-model:visible="deleteDialog" header="Confirmar" modal style="width:450px">
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
