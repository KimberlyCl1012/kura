<script setup>
import AppLayout from "@/Layouts/sakai/AppLayout.vue";
import { ref, reactive } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { FilterMatchMode } from "@primevue/core/api";
import {
    InputText, Toolbar, DataTable, Column,
    Dialog, Button, InputIcon, Select, DatePicker, Tooltip
} from "primevue";

const props = defineProps({
    appointments: Array
});

const toast = useToast();
const dt = ref(null);

const filters = ref({
    global: { value: null, matchMode: "contains" },
});

const expandedRows = ref({});

const expandAll = () => {
    const all = {};
    props.appointments.forEach((appointment) => {
        all[appointment.id] = true;
    });
    expandedRows.value = all;
};

const collapseAll = () => {
    expandedRows.value = {};
};

const onRowExpand = (event) => {
    const patient = event.data.health_record?.patient;
    const details = patient?.user_detail;
    const fullName = details
        ? `${details.name} ${details.fatherLastName}`
        : 'Paciente desconocido';

    const uuid = patient?.user_uuid || 'UUID no disponible';

    toast.add({
        severity: "info",
        summary: "Paciente expandido",
        detail: `${uuid} - ${fullName}`,
        life: 2000,
    });
};

const onRowCollapse = (event) => {
    const patient = event.data.health_record?.patient;
    const details = patient?.user_detail;
    const fullName = details
        ? `${details.name} ${details.fatherLastName}`
        : 'Paciente desconocido';

    const uuid = patient?.user_uuid || 'UUID no disponible';

    toast.add({
        severity: "warn",
        summary: "Paciente colapsado",
        detail: `${uuid} - ${fullName}`,
        life: 2000,
    });
};

</script>

<template>
    <AppLayout title="Registros">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Exportar" class="mr-4" icon="pi pi-upload" severity="secondary"
                        @click="dt?.exportCSV()" />

                </template>
                <template #end>
                    <Button text icon="pi pi-plus" label="Expandir Todo" @click="expandAll" class="mr-2" />
                    <Button text icon="pi pi-minus" label="Colapsar Todo" @click="collapseAll" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="appointments" v-model:expandedRows="expandedRows" dataKey="id" :paginator="true"
                :rows="10" :filters="filters" :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros" @rowExpand="onRowExpand"
                @rowCollapse="onRowCollapse">

                <template #header>
                    <div class="flex justify-between items-center">
                        <h4 class="m-0">Registros del paciente</h4>
                        <InputText v-model="filters.global.value" placeholder="Buscar..." />
                    </div>
                </template>

                <Column expander style="width: 5rem" />
                <Column header="Paciente">
                    <template #body="{ data }">
                        {{ data.health_record?.patient?.user_uuid }} {{ data.health_record?.patient?.user_detail?.name
                        }} {{
                            data.health_record?.patient?.user_detail?.fatherLastName }}
                    </template>
                </Column>
                <Column field="dateStartVisit" header="Fecha consulta">
                    <template #body="{ data }">
                        {{ data.dateStartVisit }}
                    </template>
                </Column>
                <Column header="Kurador">
                    <template #body="{ data }">
                        {{ data.kurator?.user_uuid }} {{ data.kurator?.user_detail?.name }} {{
                            data.kurator?.user_detail?.fatherLastName }}
                    </template>
                </Column>

                <Column field="typeVisit" header="Tipo de visita">
                    <template #body="{ data }">
                        {{ data.typeVisit }}
                    </template>
                </Column>

                <Column :exportable="false" header="Acciones">
                    <template #body="{ data }">
                        <Button icon="pi pi-eye" outlined rounded severity="info" class="mr-2"
                            v-tooltip.top="'Ver paciente'" @click.stop="editPatient(data.health_record.patient.id)" />
                    </template>
                </Column>
                <template #expansion="slotProps">
                    <div class="p-4 border rounded bg-gray-50">
                        <h5 class="font-bold mb-2">Heridas:</h5>
                        <div v-if="slotProps.data.wounds.length">
                            <DataTable :value="slotProps.data.wounds" responsiveLayout="scroll">
                                <Column header="#" style="min-width: 6rem">
                                    <template #body="{ index }">{{ index + 1 }}</template>
                                </Column>
                                <Column header="Fecha creación">
                                    <template #body="{ data }">
                                        {{ data.created_at?.substring(0, 10) }}
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
                            </DataTable>
                        </div>
                        <div v-else>
                            <p class="text-gray-500">Sin heridas registradas.</p>
                        </div>
                    </div>
                </template>

            </DataTable>
        </div>
    </AppLayout>
</template>
