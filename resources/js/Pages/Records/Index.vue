<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Button, DataTable, Column } from "primevue";
import axios from 'axios';

const props = defineProps({
    wounds: Array
});

const selectedWounds = ref([]);


const generatePdf = async () => {
    const ids = selectedWounds.value.map(w => w.id);
    
    try {
        const response = await axios.post(route('records.generate-pdf'), {
            wound_ids: ids
        }, {
            responseType: 'blob' 
        });

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);

        // Crear un enlace para descargar el archivo
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reporte_heridas.pdf');
        document.body.appendChild(link);
        link.click();

        // Limpieza
        URL.revokeObjectURL(url);
        document.body.removeChild(link);
    } catch (error) {
        console.error("Error al generar PDF:", error);
    }
};

</script>

<template>
    <AppLayout title="Listado de Heridas">
        <div class="card">
            <div class="flex justify-end mb-3">
                <Button icon="pi pi-file-pdf" label="Generar reporte" severity="danger" @click="generatePdf"
                    :disabled="!selectedWounds.length" />
            </div>

            <DataTable v-model:selection="selectedWounds" :value="wounds" dataKey="id" tableStyle="min-width: 70rem"
                selectionMode="multiple" :paginator="true" :rows="10">
                <Column selectionMode="multiple" headerStyle="width: 3rem" />

                <Column header="Paciente">
                    <template #body="{ data }">
                        {{ data.appointment?.kurator?.user_detail?.name }}
                        {{ data.appointment?.kurator?.user_detail?.fatherLastName }}
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
            </DataTable>
        </div>
    </AppLayout>
</template>
