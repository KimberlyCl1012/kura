<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { Button, DataTable, Column, Checkbox, RadioButton, DatePicker } from "primevue";
import axios from 'axios';

const props = defineProps({
    wounds: Array,
    healthRecordId: Number, // <-- nuevo
});

const selectedWounds = ref([]);

const filters = ref({
    includeActive: false,
    includeFollowups: false,
    includeConsultations: false,
    dateRange: null,
    evidenceMode: 'first_last'
});

// "Todo"
const allSelected = computed({
    get() {
        return filters.value.includeActive
            && filters.value.includeFollowups
            && filters.value.includeConsultations;
    },
    set(val) {
        filters.value.includeActive = val;
        filters.value.includeFollowups = val;
        filters.value.includeConsultations = val;
    }
});

// ¿Mostrar tabla? Solo si hay al menos un check de la sección
const showTable = computed(() =>
    filters.value.includeActive ||
    filters.value.includeFollowups ||
    filters.value.includeConsultations
);

// El botón se habilita si hay selección o si hay filtros (o rango de fechas completo)
const hasSection = computed(() =>
    filters.value.includeActive ||
    filters.value.includeFollowups ||
    filters.value.includeConsultations
);

const hasDateRange = computed(() =>
    Array.isArray(filters.value.dateRange) &&
    filters.value.dateRange[0] &&
    filters.value.dateRange[1]
);

const canGenerate = computed(() =>
    selectedWounds.value.length > 0 || hasSection.value || hasDateRange.value
);

const fmt = (d) => d ? new Date(d).toISOString().split('T')[0] : null;

const generatePdf = async () => {
    const ids = selectedWounds.value.map(w => w.id);

    try {
        const payload = {
            health_record_id: props.healthRecordId,
            wound_ids: ids,
            filters: {
                include_active: filters.value.includeActive,
                include_followups: filters.value.includeFollowups,
                include_consultations: filters.value.includeConsultations,
                date_from: filters.value.dateRange?.[0] ? fmt(filters.value.dateRange[0]) : null,
                date_to: filters.value.dateRange?.[1] ? fmt(filters.value.dateRange[1]) : null,
                evidence_mode: filters.value.evidenceMode // 'all' | 'first_last'
            }
        };

        const response = await axios.post(route('records.generate-pdf'), payload, {
            responseType: 'blob'
        });

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        //Vista previa
        // window.open(url, '_blank');
        // setTimeout(() => URL.revokeObjectURL(url), 60 * 1000);
        //Descargar
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reporte_heridas.pdf');
        document.body.appendChild(link);
        link.click();
        URL.revokeObjectURL(url);
        document.body.removeChild(link);
    } catch (error) {
        console.error("Error al generar PDF:", error);
    }
};


</script>

<template>
    <AppLayout title="Listado de Heridas">
        <div class="card space-y-4">
            <!-- Botón generar -->
            <div class="flex justify-end">
                <Button icon="pi pi-file-pdf" label="Generar reporte" severity="danger" @click="generatePdf"
                    :disabled="!canGenerate" />
            </div>
            <div class="p-6 text-center text-gray-500 border rounded-md">
                Selecciona al menos un filtro <strong>para generar el reporte.</strong>
            </div>
            <!-- Filtros -->
            <div class="grid md:grid-cols-3 gap-4">
                <!-- Checkboxes de secciones -->
                <div class="p-4 border rounded-md">
                    <h5 class="font-semibold mb-2">Historia clínica</h5>

                    <div class="flex items-center gap-2 mb-2">
                        <Checkbox v-model="filters.includeActive" :binary="true" inputId="active" />
                        <label for="active">Heridas activas</label>
                    </div>

                    <div class="flex items-center gap-2 mb-2">
                        <Checkbox v-model="filters.includeFollowups" :binary="true" inputId="followups" />
                        <label for="followups">Seguimiento de heridas</label>
                    </div>

                    <div class="flex items-center gap-2 mb-2">
                        <Checkbox v-model="filters.includeConsultations" :binary="true" inputId="consults" />
                        <label for="consults">Consultas</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox v-model="allSelected" :binary="true" inputId="all" />
                        <label for="all">Todo</label>
                    </div>
                </div>

                <!-- Evidencias fotográficas -->
                <div class="p-4 border rounded-md">
                    <h5 class="font-semibold mb-2">Evidencias fotográficas</h5>
                    <div class="flex items-center gap-2 mb-2">
                        <RadioButton inputId="ev-all" name="evidence" value="all" v-model="filters.evidenceMode" />
                        <label for="ev-all">Agregar todas</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <RadioButton inputId="ev-fl" name="evidence" value="first_last"
                            v-model="filters.evidenceMode" />
                        <label for="ev-fl">Primera y última imagen</label>
                    </div>
                </div>

                <!-- Calendario rango de fechas -->
                <div class="p-4 border rounded-md">
                    <h5 class="font-semibold mb-2">Rango de fechas</h5>
                    <DatePicker v-model="filters.dateRange" selectionMode="range" :manualInput="false" showIcon
                        dateFormat="yy-mm-dd" placeholder="Selecciona un rango" class="w-full" />
                    <small class="text-gray-500">Si lo dejas vacío, no filtra por fechas.</small>
                </div>
            </div>

            <!-- Tabla -->
            <!-- <DataTable v-if="showTable" v-model:selection="selectedWounds" :value="wounds" dataKey="id"
                tableStyle="min-width: 70rem" selectionMode="multiple" :paginator="true" :rows="10">
                <Column selectionMode="multiple" headerStyle="width: 3rem" />
                <Column header="Paciente">
                    <template #body="{ data }">
                        {{ data.health_record?.patient?.user_uuid || 'N/A' }}
                        {{ data.health_record?.patient?.user_detail?.name || 'N/A' }}
                        {{ data.health_record?.patient?.user_detail?.fatherLastName || '' }}
                    </template>
</Column>
<Column header="Tipo">
    <template #body="{ data }">
                        {{ data.wound_type?.name || 'N/A' }}
                    </template>
</Column>
<Column header="Subtipo">
    <template #body="{ data }">
                        {{ data.wound_subtype?.name || 'N/A' }}
                    </template>
</Column>
<Column header="Ubicación">
    <template #body="{ data }">
                        {{ data.body_location?.name || 'N/A' }}
                    </template>
</Column>
<Column header="Fecha creación">
    <template #body="{ data }">
                        {{ data.created_at?.substring(0, 10) }}
                    </template>
</Column>
</DataTable> -->


        </div>
    </AppLayout>
</template>
