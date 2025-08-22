<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, watch, computed } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Button from "primevue/button";
import Editor from "primevue/editor";
import { useToast } from "primevue/usetoast";

const page = usePage();
const toast = useToast();

const props = defineProps({
    patient: Object,
    healthInstitutions: Array,
    healthRecord: Object,
    permissions: Object,
});

const form = useForm({
    health_institution_id: props.healthRecord?.health_institution_id ?? null,
    patient_id: props.patient.id,
    medicines: props.healthRecord?.medicines ?? '',
    allergies: props.healthRecord?.allergies ?? '',
    pathologicalBackground: props.healthRecord?.pathologicalBackground ?? '',
    laboratoryBackground: props.healthRecord?.laboratoryBackground ?? '',
    nourishmentBackground: props.healthRecord?.nourishmentBackground ?? '',
    medicalInsurance: props.healthRecord?.medicalInsurance ?? '',
    medical_info: '',
    health_institution: props.healthRecord?.health_institution ?? '',
    religion: props.healthRecord?.religion ?? '',
});

const isSaving = ref(false);
const submitted = ref(false);

watch(() => form.medicalInsurance, (val) => {
    if (val !== 'Sí') form.medical_info = '';
});

watch(() => form.health_institution_id, (val) => {
    if (val !== 5) form.health_institution = '';
});

const editorRestrictDeletion = computed(() => {
    return props.permissions?.can_full_edit !== true; 
});

function handleEditorInput(fieldName, newValue) {
    const oldValue = form[fieldName];

    if (editorRestrictDeletion.value) {
        const cleanOld = normalizeText(stripHtml(oldValue));
        const cleanNew = normalizeText(stripHtml(newValue));
        const isAppendOnly = cleanOld === '' || (cleanNew !== '' && cleanNew.startsWith(cleanOld));

        if (!isAppendOnly) {
            toast.add({
                severity: 'warn',
                summary: 'Edición limitada',
                detail: 'Solo puedes agregar al final; no puedes reemplazar contenido existente.',
                life: 3000,
            });
            form[fieldName] = oldValue;
            return;
        }
    }

    form[fieldName] = '' + newValue;
}


function stripHtml(html) {
    const div = document.createElement("div");
    div.innerHTML = html;
    return div.textContent || div.innerText || "";
}

function normalizeText(text) {
    return text
        .replace(/\s+/g, ' ')
        .replace(/\n/g, ' ')
        .trim()
        .toLowerCase();
}

const submit = async () => {
    if (isSaving.value) return;

    submitted.value = true;

    const requiredFields = [
        form.health_institution_id,
        form.medicines,
        form.allergies,
        form.pathologicalBackground,
        form.laboratoryBackground,
        form.nourishmentBackground,
    ];

    const hasDynamicErrors =
        (form.medicalInsurance === 'Sí' && !form.medical_info) ||
        (form.health_institution_id === 5 && !form.health_institution);

    if (requiredFields.includes(null) || requiredFields.includes('') || hasDynamicErrors) {
        toast.add({
            severity: 'warn',
            summary: 'Campos requeridos',
            detail: 'Completa todos los campos obligatorios.',
            life: 3000,
        });
        return;
    }

    isSaving.value = true;

    try {
        if (props.healthRecord) {
            await form.put(route('health_records.update', props.healthRecord.healthRecordId), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Expediente actualizado.', life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar el expediente.', life: 3000 });
                },
            });
        } else {
            await form.post(route('health_records.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Guardado', detail: 'Expediente creado.', life: 3000 });
                    router.reload();
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo guardar el expediente.', life: 3000 });
                },
            });
        }
    } finally {
        isSaving.value = false;
    }
};

//Records
function goToRecords(healthRecordId) {
    router.get(route('records.index', healthRecordId));
}


</script>

<template>
    <AppLayout title="Historia clínica">
        <div class="card">
            <div class="bg-surface-0 dark:bg-surface-950 px-1 py-3 lg:px-20">
                <div class="grid grid-cols-12 gap-8">
                    <div class="col-span-12 grid grid-cols-12 mb-4">
                        <div class="col-span-6 flex items-center">
                            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                                Historia clínica
                            </h2>
                        </div>

                        <div v-if="props.healthRecord" class="col-span-6 flex items-center justify-end">
                            <Button icon="pi pi-folder-open" outlined severity="danger" class="ml-2"
                                label="Ver registros" v-tooltip.top="'Registro de heridas y antecedentes'"
                                @click.stop="goToRecords(props.healthRecord.cryptHealthRecordId)" />
                        </div>
                    </div>

                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label class="font-medium">Paciente</label>
                                <InputText :modelValue="`${props.patient.uuid} - ${props.patient.name}`" disabled
                                    class="w-full" />
                            </div>

                            <div class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label class="font-medium">Institución de Salud <span
                                        class="text-red-500">*</span></label>
                                <Select v-model="form.health_institution_id" :options="props.healthInstitutions"
                                    optionLabel="name" optionValue="id" placeholder="Selecciona una institución"
                                    class="w-full" :class="{ 'p-invalid': submitted && !form.health_institution_id }" />
                                <small v-if="submitted && !form.health_institution_id" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div v-if="form.health_institution_id === 5"
                                class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label for="health_institution">¿Cuál? <span class="text-red-500">*</span></label>
                                <InputText id="health_institution" v-model="form.health_institution" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.health_institution }" />
                                <small v-if="submitted && !form.health_institution" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label class="font-medium">¿Tiene seguro de gastos médicos?</label>
                                <Select v-model="form.medicalInsurance" :options="['Sí', 'No']" class="w-full"
                                    placeholder="Selecciona una opción" />
                            </div>

                            <div v-if="form.medicalInsurance === 'Sí'"
                                class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label for="medical_info">¿Cuál? <span class="text-red-500">*</span></label>
                                <InputText id="medical_info" v-model="form.medical_info" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.medical_info }" />
                                <small v-if="submitted && !form.medical_info" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 md:col-span-6 flex flex-col gap-2">
                                <label class="font-medium">Religión</label>
                                <InputText v-model="form.religion" class="w-full" />
                            </div>

                            <div class="col-span-12 flex flex-col gap-2">
                                <label class="font-medium">Medicamentos <span class="text-red-500">*</span></label>
                                <Editor :modelValue="form.medicines"
                                    @update:modelValue="(val) => handleEditorInput('medicines', val)"
                                    editorStyle="height: 100px" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.medicines }" />

                                <small v-if="submitted && !form.medicines" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 flex flex-col gap-2">
                                <label class="font-medium">Alergias <span class="text-red-500">*</span></label>
                                <Editor :modelValue="form.allergies"
                                    @update:modelValue="(val) => handleEditorInput('allergies', val)"
                                    editorStyle="height: 100px" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.allergies }" />

                                <small v-if="submitted && !form.allergies" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 flex flex-col gap-2">
                                <label class="font-medium">Antecedentes personales patológicos <span
                                        class="text-red-500">*</span></label>
                                <Editor :modelValue="form.pathologicalBackground"
                                    @update:modelValue="(val) => handleEditorInput('pathologicalBackground', val)"
                                    editorStyle="height: 100px" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.pathologicalBackground }" />

                                <small v-if="submitted && !form.pathologicalBackground" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 flex flex-col gap-2">
                                <label class="font-medium">Laboratorios <span class="text-red-500">*</span></label>
                                <Editor :modelValue="form.laboratoryBackground"
                                    @update:modelValue="(val) => handleEditorInput('laboratoryBackground', val)"
                                    editorStyle="height: 100px" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.laboratoryBackground }" />
                                <small v-if="submitted && !form.laboratoryBackground" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>

                            <div class="col-span-12 flex flex-col gap-2">
                                <label class="font-medium">Alimentación <span class="text-red-500">*</span></label>
                                <Editor :modelValue="form.nourishmentBackground"
                                    @update:modelValue="(val) => handleEditorInput('nourishmentBackground', val)"
                                    editorStyle="height: 100px" class="w-full"
                                    :class="{ 'p-invalid': submitted && !form.nourishmentBackground }" />
                                <small v-if="submitted && !form.nourishmentBackground" class="text-red-500">
                                    Campo obligatorio.
                                </small>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <Button :label="props.healthRecord ? 'Actualizar' : 'Guardar'" icon="pi pi-check"
                                :loading="isSaving" :disabled="isSaving" @click="submit" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
