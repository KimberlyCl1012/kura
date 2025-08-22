<!-- resources/js/Pages/Permissions/Index.vue -->
<script setup>
import AppLayout from "@/Layouts/sakai/AppLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { FilterMatchMode } from "@primevue/core/api";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Toolbar from "primevue/toolbar";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import { Select } from "primevue";

const props = defineProps({
  teams: Array,
  selectedTeamId: Number,
  permissions: Array,
  enabledIds: Array,
  canEdit: Boolean,
});

const page = usePage();
const toast = useToast();

const dt = ref(null);
const filters = ref({ global: { value: null, matchMode: FilterMatchMode.CONTAINS } });

const selectedTeamId = ref(props.selectedTeamId ?? props.teams?.[0]?.id ?? null);
const rows = ref([...props.permissions]);

const originalEnabled = ref(new Set(props.enabledIds ?? []));
const currentEnabled = ref(new Set(props.enabledIds ?? []));
const canEditLocal = ref(!!props.canEdit);

const isDirty = computed(() => {
  if (originalEnabled.value.size !== currentEnabled.value.size) return true;
  for (const id of originalEnabled.value) if (!currentEnabled.value.has(id)) return true;
  return false;
});

function toggleCheck(row) {
  if (!canEditLocal.value) return;
  row.enabled_in_team = !row.enabled_in_team;
  if (row.enabled_in_team) currentEnabled.value.add(row.id);
  else currentEnabled.value.delete(row.id);
}

async function onChangeTeam() {
  if (!selectedTeamId.value) return;

  try {
    const { data } = await axios.get(route('permissions.show', selectedTeamId.value));
    const enabled = new Set(data.enabledIds || []);
    currentEnabled.value = enabled;
    originalEnabled.value = new Set(enabled);
    canEditLocal.value = !!data.canEdit;

    rows.value = rows.value.map(r => ({ ...r, enabled_in_team: enabled.has(r.id) }));
  } catch (e) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar permisos del equipo.', life: 4000 });
  }
}

async function save() {
  if (!selectedTeamId.value) return;
  try {
    const payload = { permission_ids: Array.from(currentEnabled.value) };
    const { data } = await axios.post(route('permissions.sync', selectedTeamId.value), payload);
    const enabled = new Set(data.enabledIds || []);
    originalEnabled.value = new Set(enabled);
    currentEnabled.value = new Set(enabled);
    rows.value = rows.value.map(r => ({ ...r, enabled_in_team: enabled.has(r.id) }));

    toast.add({ severity: 'success', summary: 'Éxito', detail: data.message || 'Permisos actualizados.', life: 2500 });
  } catch (e) {
    const msg = e?.response?.data?.message || 'No se pudieron guardar los cambios.';
    toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 4500 });
  }
}

function exportCSV() {
  dt.value?.exportCSV();
}

onMounted(() => {
  if (selectedTeamId.value) onChangeTeam();
});
</script>

<template>
  <AppLayout title="Permisos por equipo (rol)">
    <Toast />

    <div class="card">
      <Toolbar class="mb-4">
        <template #start>
          <div class="flex items-center gap-4">
            <h4 class="m-0">Rol:</h4>
            <Select v-model="selectedTeamId" :options="teams" optionLabel="description" optionValue="id"
              placeholder="Selecciona un equipo" class="min-w-64" @change="onChangeTeam" />
            <Button label="Actualizar" icon="pi pi-check" :disabled="!isDirty || !canEditLocal"
              :severity="isDirty ? 'success' : 'secondary'" @click="save" />
          </div>
        </template>
        <template #end>
          <div class="flex gap-2">
            <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
          </div>
        </template>
      </Toolbar>

      <DataTable ref="dt" :key="selectedTeamId" :value="rows" dataKey="id" :paginator="false" :rows="20"
        :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]" currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
        <template #header>
          <div class="flex flex-wrap gap-2 items-center justify-between">
            <div class="text-lg font-semibold">Catálogo de permisos</div>
            <IconField>
              <InputIcon><i class="pi pi-search" /></InputIcon>
              <InputText v-model="filters['global'].value" placeholder="Buscar..." />
            </IconField>
          </div>
        </template>

        <Column header="#" style="min-width: 6rem">
          <template #body="{ index }">{{ index + 1 }}</template>
        </Column>

        <Column field="name" header="Nombre" style="min-width: 16rem" />

        <Column header="Permisos" style="min-width: 12rem">
          <template #body="{ data }">
            <Checkbox :modelValue="data.enabled_in_team" :binary="true" :disabled="!canEditLocal"
              @click.prevent="toggleCheck(data)" />
          </template>
        </Column>
      </DataTable>
    </div>
  </AppLayout>
</template>
