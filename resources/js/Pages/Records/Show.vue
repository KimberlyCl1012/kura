<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";

const page = usePage();
const toast = useToast();

const props = defineProps({
    appointments: Array,
});

</script>

<template>
    <AppLayout title="Expediente Médico">
        <div class="card">
            <div class="bg-surface-0 dark:bg-surface-950 px-1 py-3 lg:px-20">
                <div class="grid grid-cols-12 gap-8">
                    <div class="col-span-12 grid grid-cols-12 mb-4">
                        <div class="col-span-6 flex items-center">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                                Expediente Médico
                            </h2>
                        </div>
                    </div>

                    <div class="col-span-12">
                        <div class="grid grid-cols-6 md:grid-cols-12 gap-6">
                            <div class="col-span-3 md:col-span-4 flex flex-col gap-2">
                                <label class="font-medium">Expediente</label>
                                <p>Juan Pérez</p>
                                <label class="font-medium">Institución de Salud</label>
                                <p>2025-08-04</p>
                                <label class="font-medium">Religión</label>
                                <p>2025-08-04</p>
                            </div>

                            <div class="col-span-3 md:col-span-4 flex flex-col gap-2">
                                <label class="font-medium">Paciente</label>
                                <p>Maria López</p>
                                <label class="font-medium">Seguro de gastos médicos</label>
                                <p>2025-08-04</p>

                            </div>

                            <div class="col-span-3 md:col-span-4 flex flex-col gap-2">
                                <label class="font-medium">Fecha de alta</label>
                                <p>2025-08-04</p>
                                <label class="font-medium">Institución de Salud</label>
                                <p>2025-08-04</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-span-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 flex flex-col gap-2">
                                <label class="font-medium">Alergias</label>
                                <p>Medicina Interna</p>
                                <label class="font-medium">Antecedentes personales patológicos</label>
                                <p>Medicina Interna</p>
                                <label class="font-medium">Alimentación</label>
                                <p>Medicina Interna</p>
                            </div>
                            <div class="col-span-1 flex flex-col gap-2">
                                <label class="font-medium">Medicamentos</label>
                                <p>Úlcera por presión</p>
                                <label class="font-medium">Laboratorios</label>
                                <p>Medicina Interna</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-span-12 grid grid-cols-12 mb-4">
                        <div class="col-span-6 flex items-center">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                                Consulta médica
                            </h2>
                        </div>
                    </div>

                    <div class="col-span-12">
                        <div class="grid grid-cols-6 md:grid-cols-12 gap-6">
                            <div class="col-span-3 md:col-span-3 flex flex-col gap-2">
                                <label class="font-medium">Fecha de la consulta</label>
                                <p>2025-08-01</p>
                            </div>

                            <div class="col-span-3 md:col-span-3 flex flex-col gap-2">
                                <label class="font-medium">Tipo de consulta</label>
                                <p>Seguimiento</p>
                            </div>

                            <div class="col-span-3 md:col-span-3 flex flex-col gap-2">
                                <label class="font-medium">Fecha de término</label>
                                <p>2025-08-04</p>
                            </div>

                            <div class="col-span-3 md:col-span-3 flex flex-col gap-2">
                                <label class="font-medium">Kurador</label>
                                <p>Geriatría</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-span-12 grid grid-cols-12 mb-4">
                        <div class="col-span-6 flex items-center">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                                Heridas
                            </h2>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
