<script setup>
import AppLayout from "../../Layouts/Sakai/AppLayout.vue";
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import {
    InputText,
    Select,
    Button,
    DatePicker,
    Stepper,
    StepList,
    StepPanels,
    StepPanel,
    Step
} from "primevue";

const props = defineProps({
    woundsType: Array,
    woundsSubtype: Array,
    woundsPhase: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    grades: Array,
    wounds: Array,
    appointmentId: String,
    healthRecordId: String,
    patient: Object,
});

const toast = useToast();

const formWound = ref({
    id: null,
    appointment_id: props.appointmentId,
    health_record_id: props.healthRecordId,
    wound_type_id: null,
    grade_foot: null,
    wound_subtype_id: null,
    wound_type_other: "",
    body_location_id: null,
    body_sublocation_id: null,
    wound_phase_id: null,
    woundBeginDate: null,
    wound_id: null,
});

const submitted = ref(false);
const isSaving = ref(false);

function hideDialog() {
    // Lógica para ocultar diálogo si la tienes
}

function saveUser() {
    submitted.value = true;
    // Aquí valida el form y guarda
    // Por ejemplo si hay campos obligatorios, muestra toast o similar
    if (!formWound.value.wound_type_id) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Seleccione tipo de herida' });
        return;
    }
    // Simulación guardado
    isSaving.value = true;
    setTimeout(() => {
        isSaving.value = false;
        toast.add({ severity: 'success', summary: 'Guardado', detail: 'Datos guardados correctamente' });
    }, 1000);
}
</script>

<template>
    <AppLayout title="Heridas">
        <div class="card max-w-6xl mx-auto h-[40rem] flex flex-col">
            <Stepper value="1" linear class="flex flex-col flex-grow">
                <StepList>
                    <Step value="1">Datos de la herida</Step>
                    <Step value="2">Evaluación de la herida</Step>
                    <Step value="3">Abordaje de la herida</Step>
                </StepList>
                <StepPanels class="flex flex-col flex-grow overflow-hidden">
                    <StepPanel v-slot="{ activateCallback }" value="1" class="flex flex-col h-full">

                        <div
                            class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 ">
                            <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Datos de la herida</h2>

                            <form @submit.prevent="saveUser">
                                <div class="grid grid-cols-3 gap-6 flex-grow overflow-auto p-4">
                                    <!-- Tipo de herida -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Tipo de herida <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.wound_type_id" :options="woundsType"
                                            optionLabel="name" optionValue="id" filter placeholder="Seleccione un tipo"
                                            class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.wound_type_id }" />
                                        <small v-if="submitted && !formWound.wound_type_id" class="text-red-500">
                                            Debe seleccionar el tipo de herida.
                                        </small>
                                    </div>

                                    <!-- Subtipo de herida -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Subtipo de herida <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.wound_subtype_id" :options="woundsSubtype"
                                            optionLabel="name" optionValue="id" filter
                                            placeholder="Seleccione un subtipo" class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.wound_subtype_id }" />
                                        <small v-if="submitted && !formWound.wound_subtype_id" class="text-red-500">
                                            Debe seleccionar el subtipo.
                                        </small>
                                    </div>

                                    <!-- Grado (condicional) -->
                                    <div v-if="formWound.wound_type_id === 8">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Grado <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.grade_foot" :options="grades" optionLabel="label"
                                            optionValue="value" placeholder="Seleccione un grado" filter class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.grade_foot }" />
                                        <small v-if="submitted && !formWound.grade_foot" class="text-red-500">
                                            Debe seleccionar el grado.
                                        </small>
                                    </div>

                                    <!-- Otro tipo (condicional) -->
                                    <div
                                        v-if="formWound.wound_type_id === 9 || [7, 11, 25, 33, 46].includes(formWound.wound_subtype_id)">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Indicar tipo de herida <span class="text-red-600">*</span>
                                        </label>
                                        <InputText v-model="formWound.wound_type_other" class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.wound_type_other }" />
                                        <small v-if="submitted && !formWound.wound_type_other" class="text-red-500">
                                            Debe especificar otro tipo de herida.
                                        </small>
                                    </div>

                                    <!-- Ubicación corporal -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Ubicación corporal <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.body_location_id" :options="bodyLocations"
                                            optionLabel="name" optionValue="id" filter
                                            placeholder="Seleccione una ubicación" class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.body_location_id }" />
                                        <small v-if="submitted && !formWound.body_location_id" class="text-red-500">
                                            Debe seleccionar una ubicación.
                                        </small>
                                    </div>

                                    <!-- Sublocalización corporal -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Ubicación corporal secundaria <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.body_sublocation_id" :options="bodySublocation"
                                            optionLabel="name" optionValue="id" filter
                                            placeholder="Seleccione una ubicación" class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.body_sublocation_id }" />
                                        <small v-if="submitted && !formWound.body_sublocation_id" class="text-red-500">
                                            Debe seleccionar la sublocalización.
                                        </small>
                                    </div>

                                    <!-- Fase -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Fase de la herida <span class="text-red-600">*</span>
                                        </label>
                                        <Select v-model="formWound.wound_phase_id" :options="woundsPhase"
                                            optionLabel="name" placeholder="Seleccione una fase" optionValue="id" filter
                                            class="w-full"
                                            :class="{ 'p-invalid': submitted && !formWound.wound_phase_id }" />
                                        <small v-if="submitted && !formWound.wound_phase_id" class="text-red-500">
                                            Debe seleccionar la fase.
                                        </small>
                                    </div>

                                    <!-- Fecha inicio -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Fecha que inició la herida <span class="text-red-600">*</span>
                                        </label>
                                        <DatePicker v-model="formWound.woundBeginDate" class="w-full"
                                            inputId="woundBeginDate"
                                            :class="{ 'p-invalid': submitted && !formWound.woundBeginDate }"
                                            variant="filled" showIcon iconDisplay="input" />
                                        <small v-if="submitted && !formWound.woundBeginDate" class="text-red-500">
                                            Debe seleccionar la fecha de inicio.
                                        </small>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end gap-2 p-10">
                                    <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog"
                                        :disabled="isSaving" />
                                    <Button label="Guardar" icon="pi pi-check" type="submit" :loading="isSaving"
                                        :disabled="isSaving" />
                                </div>

                            </form>
                        </div>
                        <div class="flex pt-6 justify-end px-4">
                            <Button label="Siguiente" severity="contrast" icon="pi pi-arrow-right"
                                @click="activateCallback('2')" />
                        </div>
                    </StepPanel>

                    <StepPanel v-slot="{ activateCallback }" value="2" class="flex flex-col h-full">
                        <div
                            class="flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex justify-center items-center font-medium">
                            Content II
                        </div>
                        <div class="flex pt-6 justify-between px-4">
                            <Button label="Atrás" severity="secondary" icon="pi pi-arrow-left"
                                @click="activateCallback('1')" />
                            <Button label="Siguiente" icon="pi pi-arrow-right" iconPos="right"
                                @click="activateCallback('3')" />
                        </div>
                    </StepPanel>

                    <StepPanel v-slot="{ activateCallback }" value="3" class="flex flex-col h-full">
                        <div
                            class="flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex justify-center items-center font-medium">
                            Content III
                        </div>
                        <div class="pt-6 px-4">
                            <Button label="Atrás" severity="secondary" icon="pi pi-arrow-left"
                                @click="activateCallback('2')" />
                        </div>
                    </StepPanel>
                </StepPanels>
            </Stepper>
        </div>
    </AppLayout>
</template>
