<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, computed, onMounted, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import DatePicker from "primevue/datepicker";
import MultiSelect from "primevue/multiselect";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Editor from "primevue/editor";
import { router } from "@inertiajs/vue3";
import FileUpload from 'primevue/fileupload';
import Badge from "primevue/badge";
import ProgressBar from "primevue/progressbar";
import axios from "axios";

const props = defineProps({
    follow: Object,
    woundsType: Array,
    woundsSubtype: Array,
    woundsPhase: Array,
    bodyLocations: Array,
    bodySublocation: Array,
    wound: Object,
    woundId: String,
    appointmentId: String,
    measurement: Object,
    treatmentFollow: Object,
    existingImagesHistory: Array,
    existingImagesFollow: Array,
    treatmentMethods: Array,
    treatmentSubmethods: Array,
    treatmentsHistory: Array,
    woundsInAppointment: Number,
});

const toast = useToast();

// Catálogos 
const bordes = ref([{ name: "Adherido" }, { name: "No adherido" }, { name: "Enrollado" }, { name: "Epitalizado" }]);
const valoracion = ref([{ name: "Manual" }, { name: "MESI" }, { name: "No aplica" }]);
const edema = ref([{ name: "+++" }, { name: "++" }, { name: "+" }, { name: "No aplica" }]);
const dolor = ref([{ name: "En reposo" }, { name: "Con movimiento" }, { name: "Ninguno" }]);
const exudado_cantidad = ref([{ name: "Abundante" }, { name: "Moderado" }, { name: "Bajo" }]);
const exudado_tipo = ref([{ name: "Seroso" }, { name: "Purulento" }, { name: "Hemático" }, { name: "Serohemático" }]);
const olor = ref([{ name: "Mal olor" }, { name: "No aplica" }]);
const piel_perilesional = ref([
    { label: "Eritema", value: "Eritema" },
    { label: "Escoriación", value: "Escoriación" },
    { label: "Maceración", value: "Maceración" },
    { label: "Reseca", value: "Reseca" },
    { label: "Equimosis", value: "Equimosis" },
    { label: "Indurada", value: "Indurada" },
    { label: "Queratosis", value: "Queratosis" },
    { label: "Integra", value: "Integra" },
    { label: "Hiperpigmentada", value: "Hiperpigmentada" },
]);
const infeccion = ref([
    { label: "Celulitis", value: "Celulitis" },
    { label: "Pirexia", value: "Pirexia" },
    { label: "Aumento del dolor", value: "Aumento del dolor" },
    { label: "Rapida extensión del area ulcerada", value: "Rapida extensión del area ulcerada" },
    { label: "Mal olor", value: "Mal olor" },
    { label: "Incremento del exudado", value: "Incremento del exudado" },
    { label: "Eritema", value: "Eritema" },
    { label: "No aplica", value: "No aplica" },
]);
const tipo_dolor = ref([{ name: "Nociceptivo" }, { name: "Neuropático" }]);

const currentStep = ref(1);
function goToStep(step) {
    currentStep.value = step;
}

const errors = ref({});
const hasFollow = ref(!!props.follow);
const formWound = ref({ ...props.wound });
const formMeasurement = ref({ ...props.measurement });

// Carga de datos iniciales
const woundSubtypes = ref([]);
const bodySublocations = ref([]);
const isInitialLoadType = ref(true);
const isInitialLoadLocation = ref(true);


onMounted(() => {
    if (props.wound) {
        formWound.value.wound_type_id = null;
        formWound.value.body_location_id = null;

        Object.assign(formWound.value, {
            ...props.wound,
            id: props.wound.id || props.wound.wound_id,
            woundBeginDate: props.wound.woundBeginDate ? new Date(props.wound.woundBeginDate) : null,
            woundHealthDate: props.wound.woundHealthDate ? new Date(props.wound.woundHealthDate) : null,
            grade_foot: props.wound.grade_foot ? parseInt(props.wound.grade_foot) : null,
        });

        if (props.wound.wound_type_id) {
            formWound.value.wound_type_id = parseInt(props.wound.wound_type_id);
            loadSubtypes(formWound.value.wound_type_id);
        }

        if (props.wound.body_location_id) {
            formWound.value.body_location_id = parseInt(props.wound.body_location_id);
            loadSublocations(formWound.value.body_location_id);
        }
    }
});

// Función para cargar subtipos desde el backend
async function loadSubtypes(typeId) {
    try {
        const { data } = await axios.get(`/wound_types/${typeId}/subtypes`);
        woundSubtypes.value = data;
    } catch (error) {
        console.error('Error al cargar subtipos:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los subtipos.',
            life: 5000,
        });
    }
}

// Función para cargar sublocalizaciones desde el backend
async function loadSublocations(locationId) {
    try {
        const { data } = await axios.get(`/body_locations/${locationId}/sublocations`);
        bodySublocations.value = data;
    } catch (error) {
        console.error('Error al cargar sublocalizaciones:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar las sublocalizaciones.',
            life: 5000,
        });
    }
}

// Watcher para cambios en tipo de herida
watch(() => formWound.value.wound_type_id, (newVal) => {
    if (isInitialLoadType.value) {
        isInitialLoadType.value = false;
        return;
    }

    const typeId = parseInt(newVal);
    formWound.value.wound_subtype_id = null;
    woundSubtypes.value = [];

    if (typeId) {
        loadSubtypes(typeId);
    }
});

// Watcher para cambios en ubicación corporal
watch(() => formWound.value.body_location_id, (newVal) => {
    if (isInitialLoadLocation.value) {
        isInitialLoadLocation.value = false;
        return;
    }

    const locationId = parseInt(newVal);
    formWound.value.body_sublocation_id = null;
    bodySublocations.value = [];

    if (locationId) {
        loadSublocations(locationId);
    }
});


// Actualiza la barra de colores
const totalPercentage = computed(() => {
    const g = parseFloat(formMeasurement.value.granulation_percent) || 0;
    const s = parseFloat(formMeasurement.value.slough_percent) || 0;
    const n = parseFloat(formMeasurement.value.necrosis_percent) || 0;
    const e = parseFloat(formMeasurement.value.epithelialization_percent) || 0;
    return g + s + n + e;
});

function percentWidth(field) {
    const value = parseFloat(formMeasurement.value[field]) || 0;
    const total = totalPercentage.value;
    if (total === 0) return 0;

    if (total > 100) {
        return ((value / total) * 100).toFixed(2);
    }

    return value.toFixed(2);
}

function percentOffset(...fields) {
    const total = totalPercentage.value;
    let sum = 0;
    for (const field of fields) {
        const val = parseFloat(formMeasurement.value[field]) || 0;
        sum += val;
    }
    if (total > 100) {
        return ((sum / total) * 100).toFixed(2) + '%';
    }
    return sum + '%';
}


//Evidencia de la herida
const loadExistingImages = async () => {
    const woundId = props.wound?.id
    const appointmentId = props.appointmentId

    if (!woundId || !appointmentId) return

    try {
        const { data } = await axios.get('/media', {
            params: {
                wound_id: woundId,
                appointment_id: appointmentId,
                type: 'Seg',
            }
        })
        existingImagesFollow.value = data
    } catch (error) {
        console.error('Error al cargar imágenes:', error)
    }
}

// carga inicial de imágenes
watch(
    () => props.appointmentId,
    (newVal) => {
        if (newVal && props.wound?.id) {
            loadExistingImages()
        }
    },
    { immediate: true }
)

//Seguimiento de la herida
const formFollow = ref({
    wound_id: props.wound?.id || null,
    appointment_id: props.appointmentId || null,
    wound_phase_id: props.follow?.wound_phase_id || null,
    wound_type_id: props.wound?.wound_type_id || null,
    wound_subtype_id: props.wound?.wound_subtype_id || null,
    body_location_id: props.wound?.body_location_id || null,
    body_sublocation_id: props.wound?.body_sublocation_id || null,
    edema: props.follow?.edema || '',
    dolor: props.follow?.dolor || '',
    tipo_dolor: props.follow?.tipo_dolor || '',
    visual_scale: props.follow?.visual_scale || '',
    exudado_tipo: props.follow?.exudado_tipo || '',
    exudado_cantidad: props.follow?.exudado_cantidad || '',
    infeccion: props.follow?.infeccion ? JSON.parse(props.follow.infeccion) : [],
    olor: props.follow?.olor || '',
    borde: props.follow?.borde || '',
    piel_perilesional: props.follow?.piel_perilesional ? JSON.parse(props.follow.piel_perilesional) : [],
    measurementDate: props.follow?.measurementDate || null,
    length: props.follow?.length || null,
    width: props.follow?.width || null,
    undermining: props.follow?.undermining || '',
    granulation_percent: props.follow?.granulation_percent || 0,
    slough_percent: props.follow?.slough_percent || 0,
    necrosis_percent: props.follow?.necrosis_percent || 0,
    epithelialization_percent: props.follow?.epithelialization_percent || 0,
    valoracion: props.follow?.valoracion || '',
    MESI: props.follow?.MESI || '',
    ITB_izquierdo: props.follow?.ITB_izquierdo || '',
    ITB_derecho: props.follow?.ITB_derecho || '',
    pulse_dorsal_izquierdo: props.follow?.pulse_dorsal_izquierdo || '',
    pulse_dorsal_derecho: props.follow?.pulse_dorsal_derecho || '',
    pulse_popliteo_izquierdo: props.follow?.pulse_popliteo_izquierdo || '',
    pulse_popliteo_derecho: props.follow?.pulse_popliteo_derecho || '',
    pulse_tibial_izquierdo: props.follow?.pulse_tibial_izquierdo || '',
    pulse_tibial_derecho: props.follow?.pulse_tibial_derecho || '',
    monofilamento: props.follow?.monofilamento || '',
    blood_glucose: props.follow?.blood_glucose || '',
    depth: props.follow?.depth || null,
    area: props.follow?.area || null,
    volume: props.follow?.volume || null,
    tunneling: props.follow?.tunneling || '',
    note: props.follow?.note || '',
});

const submittedFollow = ref(false);
const isSavingFollow = ref(false);

const fieldLabels = {
    wound_phase_id: 'Fase de la herida',
    edema: 'Edema',
    dolor: 'Dolor',
    tipo_dolor: 'Tipo de dolor',
    visual_scale: 'Escala visual (EVA)',
    exudado_tipo: 'Exudado (tipo)',
    exudado_cantidad: 'Exudado (cantidad)',
    infeccion: 'Infección',
    olor: 'Olor',
    borde: 'Borde de la herida',
    piel_perilesional: 'Piel perilesional',
    measurementDate: 'Fecha de medición',
    length: 'Longitud',
    width: 'Anchura',
    undermining: 'Socavamiento',
    granulation_percent: 'Granulación (%)',
    slough_percent: 'Esfacelo (%)',
    necrosis_percent: 'Necrosis (%)',
    epithelialization_percent: 'Epitelización (%)',
    valoracion: 'Índice tobillo brazo',
    MESI: 'Valor MESI / Manual',
    ITB_izquierdo: 'ITB izquierdo',
    ITB_derecho: 'ITB derecho',
    pulse_dorsal_izquierdo: 'Pulso dorsal pedio izquierdo',
    pulse_dorsal_derecho: 'Pulso dorsal pedio derecho',
    pulse_popliteo_izquierdo: 'Pulso poplíteo izquierdo',
    pulse_popliteo_derecho: 'Pulso poplíteo derecho',
    pulse_tibial_izquierdo: 'Pulso tibial posterior izquierdo',
    pulse_tibial_derecho: 'Pulso tibial posterior derecho',
    monofilamento: 'Monofilamento',
    blood_glucose: 'Glucosa en sangre',
};

const saveFollow = () => {
    submittedFollow.value = true;
    isSavingFollow.value = true;
    errors.value = {};

    const requiredFields = [
        'wound_phase_id',
        'edema',
        'dolor',
        'tipo_dolor',
        'visual_scale',
        'exudado_tipo',
        'exudado_cantidad',
        'infeccion',
        'olor',
        'borde',
        'piel_perilesional',
        'measurementDate',
        'length',
        'width',
        'undermining',
        'granulation_percent',
        'slough_percent',
        'necrosis_percent',
        'epithelialization_percent',
    ];

    const vascularLocationIds = [
        18, 19, 20, 21, 22, 23, 24, 25,
        26, 27, 28, 29, 30, 31, 32, 33
    ];

    const requiredVascularFields = [
        'valoracion',
        'ITB_izquierdo',
        'ITB_derecho',
        'pulse_dorsal_izquierdo',
        'pulse_dorsal_derecho',
        'pulse_popliteo_izquierdo',
        'pulse_popliteo_derecho',
        'pulse_tibial_izquierdo',
        'pulse_tibial_derecho',
        'monofilamento',
        'blood_glucose',
    ];

    if (['Manual'].includes(formFollow.value.valoracion)) {
        requiredVascularFields.push('MESI');
    }

    if (vascularLocationIds.includes(formFollow.value.body_location_id)) {
        requiredFields.push(...requiredVascularFields);
    }

    // Detectar campos vacíos
    const missingFields = requiredFields.filter(
        (field) => !isNonEmpty(formFollow.value[field])
    );


    if (missingFields.length > 0) {
        const firstField = missingFields[0];
        missingFields.forEach((field) => {
            errors.value[field] = `El campo ${fieldLabels[field] || field} es requerido.`;
        });

        toast.add({
            severity: 'error',
            summary: 'Campo requerido',
            detail: `El campo ${fieldLabels[firstField] || firstField} es requerido.`,
            life: 4000,
        });

        isSavingFollow.value = false;
        return;
    }

    // Payload y envío
    const payload = {
        ...formFollow.value,
        measurementDate: formFollow.value.measurementDate
            ? new Date(formFollow.value.measurementDate).toISOString().split("T")[0]
            : null,
    };

    router.put(route('wounds_follow.update', props.woundId), payload, {
        onSuccess: () => {
            isSavingFollow.value = false;

            if (!hasFollow.value) {
                hasFollow.value = true;
            }

            toast.add({
                severity: 'success',
                summary: hasFollow.value ? 'Seguimiento actualizado' : 'Seguimiento guardado',
                detail: hasFollow.value
                    ? 'Los datos han sido actualizados correctamente.'
                    : 'Los datos han sido guardados correctamente.',
                life: 4000,
            });
        },
        onError: (errBag) => {
            errors.value = errBag;
            isSavingFollow.value = false;

            const firstKey = Object.keys(errBag)[0];
            const firstMsg = firstKey ? (Array.isArray(errBag[firstKey]) ? errBag[firstKey][0] : errBag[firstKey]) : 'Error de validación';

            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: firstMsg,
                life: 5000,
            });
        },
        onFinish: () => {
            isSavingFollow.value = false;

            const serverError = page.props?.errors?.error;
            if (serverError) {
                toast.add({
                    severity: 'error',
                    summary: 'Error del servidor',
                    detail: Array.isArray(serverError) ? serverError[0] : serverError,
                    life: 6000,
                });
            }

            const flash = page.props?.flash;
            if (flash?.error) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: flash.error,
                    life: 6000,
                });
            }
        },
    });

};


// Área y volumen automáticos
watch(() => [formFollow.value.length, formFollow.value.width], ([length, width]) => {
    const l = parseFloat(length);
    const w = parseFloat(width);
    if (!isNaN(l) && !isNaN(w)) {
        formFollow.value.area = (l * w).toFixed(2);
    } else {
        formFollow.value.area = '';
    }
    if (formFollow.value.depth) {
        const d = parseFloat(formFollow.value.depth);
        if (!isNaN(d)) {
            formFollow.value.volume = (l * w * d).toFixed(2);
        }
    }
});

watch(() => formFollow.value.depth, (depth) => {
    const d = parseFloat(depth);
    const a = parseFloat(formFollow.value.area);
    formFollow.value.volume = (!isNaN(d) && !isNaN(a)) ? (a * d).toFixed(2) : '';
});

// Validación de escala visual
function onVisualScaleInputFollow(event) {
    const input = event.target.value.replace("/10", "");
    let number = parseInt(input);
    if (!isNaN(number)) {
        if (number < 1) number = 1;
        if (number > 10) number = 10;
        formFollow.value.visual_scale = `${number}/10`;
    } else {
        formFollow.value.visual_scale = "";
    }
}

// Actualiza la barra de colores
function adjustProgressFollow() { }

const totalPercentageFollow = computed(() => {
    const g = parseFloat(formFollow.value.granulation_percent) || 0;
    const s = parseFloat(formFollow.value.slough_percent) || 0;
    const n = parseFloat(formFollow.value.necrosis_percent) || 0;
    const e = parseFloat(formFollow.value.epithelialization_percent) || 0;
    return g + s + n + e;
});

function percentWidthFollow(field) {
    const value = parseFloat(formFollow.value[field]) || 0;
    const total = totalPercentageFollow.value;
    if (total === 0) return 0;

    if (total > 100) {
        return ((value / total) * 100).toFixed(2);
    }

    return value.toFixed(2);
}

function percentOffsetFollow(...fields) {
    const total = totalPercentageFollow.value;
    let sum = 0;
    for (const field of fields) {
        const val = parseFloat(formFollow.value[field]) || 0;
        sum += val;
    }
    if (total > 100) {
        return ((sum / total) * 100).toFixed(2) + '%';
    }
    return sum + '%';
}


//Evidencia de la herida
const MAX_FILES = 4

const showLimitModalFollow = ref(false)
const existingImagesFollow = ref([])
const uploadFilesFollow = ref([])
const totalSizeFollow = ref(0)
const totalSizePercentFollow = ref(0)

const zoomImageUrl = ref('')
const zoomRotation = ref(0)
const showZoomModal = ref(false)
const selectedImage = ref('')
const selectedImageRotation = ref(0)
// funciones auxiliares
const getImageStyle = (rotation) => ({ transform: `rotate(${rotation}deg)` })


const openZoomModal = (src, rotation) => {
    zoomImageUrl.value = src
    zoomRotation.value = rotation
    showZoomModal.value = true
}

const selectImage = (img) => {
    selectedImage.value = `/storage/${img.content}`
    selectedImageRotation.value = img.position || 0
}

const downloadSelectedImage = () => {
    const link = document.createElement('a')
    link.href = selectedImage.value
    link.download = selectedImage.value.split('/').pop()
    link.click()
}

const updateTotalSizeFollow = () => {
    totalSizeFollow.value = uploadFilesFollow.value.reduce((acc, f) => acc + f.size, 0)
    totalSizePercentFollow.value = totalSizeFollow.value / 10
}

const resetUploadsFollow = () => {
    uploadFilesFollow.value = []
    totalSizeFollow.value = 0
    totalSizePercentFollow.value = 0
}

const isLimitReachedFollow = (incomingCount) =>
    existingImagesFollow.value.length + uploadFilesFollow.value.length + incomingCount > MAX_FILES


const closeLimitModal = () => {
    showLimitModalFollow.value = false
}

const onSelectedFilesFollow = (event) => {
    const incoming = event.files
    if (isLimitReachedFollow(incoming.length)) {
        showLimitModalFollow.value = true
        return
    }

    incoming.forEach(file => {
        uploadFilesFollow.value.push({
            raw: file,
            name: file.name,
            size: file.size,
            type: file.type,
            objectURL: URL.createObjectURL(file),
            rotation: 0,
        })
    })

    updateTotalSizeFollow()
}

const rotateImageFollow = (index, direction) => {
    const file = uploadFilesFollow.value[index]
    file.rotation = direction === 'left'
        ? (file.rotation - 5 + 360) % 360
        : (file.rotation + 5) % 360
}

const removeFileFollow = (index) => {
    uploadFilesFollow.value.splice(index, 1)
    updateTotalSizeFollow()
}

const clearTemplatedUploadFollow = (clear) => {
    clear()
    resetUploadsFollow()
}

const uploadEventFollow = async () => {
    if (!props.wound.id) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'Debe guardar la herida antes de subir imágenes.', life: 4000 })
        return
    }

    if (!uploadFilesFollow.value.length) {
        toast.add({ severity: 'warn', summary: 'Sin archivos', detail: 'Debes seleccionar imágenes para subir.', life: 3000 })
        return
    }

    const formData = new FormData()
    uploadFilesFollow.value.forEach((file, index) => {
        formData.append('images[]', file.raw, file.name)
        formData.append(`rotations[${index}]`, file.rotation || 0)
    })

    formData.append('type', 'Seg')
    formData.append('wound_id', props.wound.id)
    formData.append('appointment_id', props.appointmentId)

    try {
        await axios.post('/media/upload', formData)
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Imágenes subidas.', life: 3000 })
        resetUploadsFollow()
        await loadExistingImagesFollow()
    } catch (err) {
        console.error(err)
        toast.add({ severity: 'error', summary: 'Error', detail: 'Fallo al subir.', life: 4000 })
    }
}

watch(existingImagesFollow, (imgs) => {
    if (imgs.length > 0 && !selectedImage.value) {
        selectImage(imgs[0])
    }
})

const loadExistingImagesFollow = async () => {
    const woundId = props.wound?.id
    const appointmentId = props.appointmentId

    if (!woundId || !appointmentId) return

    try {
        const { data } = await axios.get('/media', {
            params: {
                wound_id: woundId,
                appointment_id: appointmentId,
                type: 'Seg',
            }
        })
        existingImagesFollow.value = data
    } catch (error) {
        console.error('Error al cargar imágenes:', error)
    }
}

// carga inicial de imágenes
watch(
    () => props.appointmentId,
    (newVal) => {
        if (newVal && props.wound?.id) {
            loadExistingImagesFollow()
        }
    },
    { immediate: true }
)


const fileUploadClearCallback = ref(null)

const fileUploadRefFollow = ref(null)

//Confirmación de carga de imagenes
const showConfirmUploadModalFollow = ref(false)

const confirmUploadFollow = async () => {
    showConfirmUploadModalFollow.value = false
    await uploadEventFollow()

    // Limpiar componente y estado después de subida exitosa
    resetUploadsFollow()
    if (fileUploadRefFollow.value) {
        fileUploadRefFollow.value.clear()
    }
}


//Eliminar imagenes
// Confirmación para eliminar
const showConfirmDeleteModalFollow = ref(false)
const imageToDeleteFollow = ref(null)

const openConfirmDeleteSelectedFollow = () => {
    if (!selectedImage.value) return
    // busca el objeto en existingImagesFollow que coincide con selectedImage
    const img = existingImagesFollow.value.find(
        i => `/storage/${i.content}` === selectedImage.value
    )
    if (!img) return
    imageToDeleteFollow.value = img
    showConfirmDeleteModalFollow.value = true
}

const openConfirmDeleteByThumbFollow = (img) => {
    imageToDeleteFollow.value = img
    showConfirmDeleteModalFollow.value = true
}

const deleteImageFollow = async () => {
    if (!imageToDeleteFollow.value?.id) return

    try {
        await axios.delete(`/media/${imageToDeleteFollow.value.id}`)

        // Quitarla del arreglo local sin recargar todo
        existingImagesFollow.value = existingImagesFollow.value.filter(
            i => i.id !== imageToDeleteFollow.value.id
        )

        // Si la imagen borrada era la seleccionada, seleccionar otra
        if (selectedImage.value === `/storage/${imageToDeleteFollow.value.content}`) {
            if (existingImagesFollow.value.length > 0) {
                selectImage(existingImagesFollow.value[0])
            } else {
                selectedImage.value = ''
                selectedImageRotation.value = 0
            }
        }

        toast.add({ severity: 'success', summary: 'Eliminada', detail: 'Imagen eliminada correctamente.', life: 3000 })
    } catch (err) {
        console.error(err)
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo eliminar la imagen.', life: 4000 })
    } finally {
        showConfirmDeleteModalFollow.value = false
        imageToDeleteFollow.value = null
    }
}

//Tratamiento 
const hasTreatment = ref(!!props.treatmentFollow);

const formTreat = ref({
    methods: props.treatmentFollow?.methods.map(m => m.treatment_method_id) || [],
    submethodsByMethod: props.treatmentFollow
        ? (() => {
            const map = {};
            props.treatmentFollow.submethods.forEach(sub => {
                const methodId = sub.treatment_method_id;
                map[methodId] = map[methodId] || [];
                map[methodId].push(sub.treatment_submethod_id);
            });
            return map;
        })()
        : {},
    description: props.treatmentFollow?.description || "",
});


const isSavingTreatment = ref(false);
const submittedTreatment = ref(false);

// Computed: filtrar métodos seleccionados
const selectedMethodsWithSubmethods = computed(() => {
    return props.treatmentMethods.filter(method =>
        formTreat.value.methods.includes(method.id)
    );
});

watch(
    () => formTreat.value.methods,
    (newMethods) => {
        const validMap = {};
        newMethods.forEach(methodId => {
            if (Array.isArray(formTreat.value.submethodsByMethod[methodId])) {
                validMap[methodId] = formTreat.value.submethodsByMethod[methodId];
            } else {
                validMap[methodId] = [];
            }
        });
        formTreat.value.submethodsByMethod = validMap;
    },
    { deep: true }
);

const storeTreatment = async () => {
    submittedTreatment.value = true;
    errors.value = {};

    const missingSubmethods = formTreat.value.methods.filter(
        methodId =>
            !formTreat.value.submethodsByMethod[methodId] ||
            formTreat.value.submethodsByMethod[methodId].length === 0
    );

    if (missingSubmethods.length > 0) {
        toast.add({
            severity: "error",
            summary: "Validación",
            detail: "Cada método debe tener al menos un submétodo seleccionado.",
            life: 4000,
        });
        return;
    }

    isSavingTreatment.value = true;

    const payload = {
        treatment_id: props.treatmentFollow?.id,
        appointment_id: props.appointmentId,
        wound_id: formWound.value.id,
        description: formTreat.value.description,
        method_ids: formTreat.value.methods,
        submethodsByMethod: formTreat.value.submethodsByMethod,
    };

    try {
        await axios.post("/treatments", payload);
        toast.add({
            severity: "success",
            summary: "Éxito",
            detail: hasTreatment.value ? "Tratamiento actualizado." : "Tratamiento guardado.",
            life: 3000,
        });

        hasTreatment.value = true;
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};

            const firstErrorKey = Object.keys(errors.value)[0];
            const firstMessage = errors.value[firstErrorKey][0];

            toast.add({
                severity: "error",
                summary: "Error de validación",
                detail: firstMessage,
                life: 4000,
            });
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "No se pudo guardar el tratamiento.",
                life: 4000,
            });
        }
    } finally {
        isSavingTreatment.value = false;
    }
};

onMounted(() => {
    if (props.treatmentFollow) {
        formTreat.value.description = props.treatmentFollow.description;
        formTreat.value.methods = props.treatmentFollow.methods.map(m => m.treatment_method_id);

        const map = {};
        props.treatmentFollow.submethods.forEach(sub => {
            const methodId = sub.treatment_method_id;
            if (methodId) {
                map[methodId] = map[methodId] || [];
                map[methodId].push(sub.treatment_submethod_id);
            }
        });
        formTreat.value.submethodsByMethod = map;
    }
});

//Terminal consulta 
const isNonEmpty = (v) => {
    if (v === null || v === undefined) return false;
    if (typeof v === 'string') return v.trim() !== '';
    if (Array.isArray(v)) return v.length > 0;
    return true;
};

const requiredFieldsForFollow = computed(() => {
    const base = [
        'wound_phase_id',
        'edema',
        'dolor',
        'tipo_dolor',
        'visual_scale',
        'exudado_tipo',
        'exudado_cantidad',
        'infeccion',
        'olor',
        'borde',
        'piel_perilesional',
        'measurementDate',
        'length',
        'width',
        'undermining',
        'granulation_percent',
        'slough_percent',
        'necrosis_percent',
        'epithelialization_percent',
    ];

    const vascularLocationIds = [
        18, 19, 20, 21, 22, 23, 24, 25,
        26, 27, 28, 29, 30, 31, 32, 33
    ];

    if (vascularLocationIds.includes(formFollow.value.body_location_id)) {
        const vascular = [
            'valoracion',
            'ITB_izquierdo',
            'ITB_derecho',
            'pulse_dorsal_izquierdo',
            'pulse_dorsal_derecho',
            'pulse_popliteo_izquierdo',
            'pulse_popliteo_derecho',
            'pulse_tibial_izquierdo',
            'pulse_tibial_derecho',
            'monofilamento',
            'blood_glucose',
        ];
        if (['Manual'].includes(formFollow.value.valoracion)) {
            vascular.push('MESI');
        }
        base.push(...vascular);
    }

    return base;
});

const areFollowFieldsComplete = computed(() => {
    const fields = requiredFieldsForFollow.value;
    const allFilled = fields.every((f) => isNonEmpty(formFollow.value[f]));
    const percentagesOk = totalPercentageFollow.value <= 100;
    return allFilled && percentagesOk;
});

const canFinishConsultation = computed(() => {
    return hasTreatment.value && hasFollow.value && areFollowFieldsComplete.value;
});

const woundsInAppointment = ref(0);
const showConfirmFinishDialog = ref(false);

const confirmFinishConsultation = async () => {
    if (!canFinishConsultation.value) {
        toast.add({
            severity: "warn",
            summary: "Campos incompletos",
            detail: "Aún faltan datos requeridos del seguimiento o no hay tratamiento guardado.",
            life: 4000,
        });
        return;
    }

    const appointmentId = props.wound.appointment_id;
    if (!appointmentId) {
        toast.add({
            severity: "warn",
            summary: "Advertencia",
            detail: "No se pudo obtener el ID de la consulta.",
            life: 4000,
        });
        return;
    }

    try {
        const { data } = await axios.get(`/appointments/${appointmentId}/wounds/count`);
        woundsInAppointment.value = data.count;

        if (data.count === 0) {
            toast.add({
                severity: "warn",
                summary: "Sin heridas",
                detail: "Esta consulta no tiene heridas asociadas.",
                life: 4000,
            });
            return;
        }

        showConfirmFinishDialog.value = true;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "No se pudo verificar el número de heridas.",
            life: 5000,
        });
    }
};


const onConfirmFinishConsultation = () => {
    showConfirmFinishDialog.value = false;
    finishConsultation();
};

const finishConsultation = async () => {
    isSavingTreatment.value = true;

    try {
        const response = await axios.put('/appointments/finish', {
            appointment_id: props.wound.appointment_id,
        });

        toast.add({
            severity: "success",
            summary: "Consulta finalizada",
            detail: response.data.message || "El estado fue actualizado.",
            life: 3000,
        });

        if (response.data.redirect_to) {
            setTimeout(() => {
                window.location.href = response.data.redirect_to;
            }, 1500);
        }

    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error al finalizar",
            detail: error.response?.data?.message || error.message || "Ocurrió un error inesperado.",
            life: 6000,
        });
    } finally {
        isSavingTreatment.value = false;
    }
};


</script>
<template>
    <AppLayout title="Heridas - Seguimiento">
        <div class="card max-w-6xl mx-auto min-h-screen flex flex-col">
            <!-- Navegación -->
            <nav class="p-steps" aria-label="Steps" role="list">
                <div class="p-step" :class="{ 'p-step-active': currentStep === 1 }" role="listitem"
                    @click="goToStep(1)">
                    <div class="p-step-number">1</div>
                    <div class="p-step-title">Datos de la herida</div>
                </div>
                <div class="p-step" :class="{ 'p-step-active': currentStep === 2 }" role="listitem"
                    @click="goToStep(2)">
                    <div class="p-step-number">2</div>
                    <div class="p-step-title">Evaluación de la herida</div>
                </div>
            </nav>

            <!-- Contenido Paso 1 -->
            <section class="flex-grow overflow-auto border rounded p-4">
                <div v-show="currentStep === 1">
                    <div
                        class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                        <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Datos de la herida</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tipo de herida</label>
                                <Select v-model="formWound.wound_type_id" :options="props.woundsType" optionLabel="name"
                                    optionValue="id" disabled class="w-full" />
                            </div>
                            <div v-if="formWound.wound_type_id === 8">
                                <label class="flex items-center gap-1 mb-1 font-medium">Grado</label>
                                <InputText v-model="formWound.grade_foot" disabled class="w-full" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Subtipo</label>
                                <Select v-model="formWound.wound_subtype_id" :options="woundSubtypes" optionLabel="name"
                                    optionValue="id" disabled class="w-full" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Ubicación corporal</label>
                                <Select v-model="formWound.body_location_id" :options="props.bodyLocations"
                                    optionLabel="name" optionValue="id" disabled class="w-full" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Sublocalización</label>
                                <Select v-model="formWound.body_sublocation_id" :options="bodySublocations"
                                    optionLabel="name" optionValue="id" disabled class="w-full" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Fase</label>
                                <Select v-model="formWound.wound_phase_id" :options="props.woundsPhase"
                                    optionLabel="name" optionValue="id" disabled class="w-full" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Fecha de inicio</label>
                                <DatePicker v-model="formWound.woundBeginDate" disabled showIcon class="w-full" />
                            </div>
                        </div>

                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                Evaluación de la herida
                            </h3>
                        </div>

                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold px-4">
                                Valoración vascular
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Índice tobillo brazo
                                    Manual</label>
                                <Select v-model="formWound.valoracion" :options="valoracion" optionLabel="name"
                                    optionValue="name" class="w-full" disabled />
                            </div>

                            <div v-if="formWound.valoracion === 'MESI'">
                                <label class="flex items-center gap-1 mb-1 font-medium">MESI</label>
                                <InputText v-model="formWound.MESI" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">ITB izquierdo</label>
                                <InputText v-model="formWound.ITB_izquierdo" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">ITB derecho</label>
                                <InputText v-model="formWound.ITB_derecho" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso dorsal pedio
                                    izquierdo</label>
                                <InputText v-model="formWound.pulse_dorsal_izquierdo" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso poplíteo
                                    izquierdo</label>
                                <InputText v-model="formWound.pulse_popliteo_izquierdo" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso tibial posterior
                                    izquierdo</label>
                                <InputText v-model="formWound.pulse_tibial_izquierdo" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso dorsal pedio
                                    derecho</label>
                                <InputText v-model="formWound.pulse_dorsal_derecho" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso poplíteo
                                    derecho</label>
                                <InputText v-model="formWound.pulse_popliteo_derecho" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Pulso tibial posterior
                                    derecho</label>
                                <InputText v-model="formWound.pulse_tibial_derecho" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Monofilamento</label>
                                <InputText v-model="formWound.monofilamento" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Nivel de glucosa en
                                    sangre</label>
                                <InputText v-model="formWound.blood_glucose" class="w-full" disabled />
                            </div>

                        </div>

                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold px-4">
                                Información de la herida
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Fecha de primera
                                    valoración</label>
                                <DatePicker v-model="formWound.woundHealthDate" inputId="woundHealthDate" class="w-full"
                                    placeholder="Seleccione una fecha" showIcon disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Edema</label>
                                <Select v-model="formWound.edema" :options="edema" optionLabel="name" optionValue="name"
                                    class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Dolor</label>
                                <Select v-model="formWound.dolor" :options="dolor" optionLabel="name" optionValue="name"
                                    class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tipo de dolor</label>
                                <Select v-model="formWound.tipo_dolor" :options="tipo_dolor" optionLabel="name"
                                    optionValue="name" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Escala Visual Analógica
                                    (EVA)</label>
                                <InputText v-model="formWound.visual_scale" class="w-full" disabled
                                    placeholder="Ej: 3/10" />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Exudado (Tipo)</label>
                                <Select v-model="formWound.exudado_tipo" :options="exudado_tipo" optionLabel="name"
                                    optionValue="name" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Exudado (Cantidad)</label>
                                <Select v-model="formWound.exudado_cantidad" :options="exudado_cantidad"
                                    optionLabel="name" optionValue="name" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Infección</label>
                                <MultiSelect id="infeccion" v-model="formWound.infeccion" :options="infeccion"
                                    optionLabel="label" optionValue="value" class="w-full" disabled filter
                                    placeholder="Selecciona una o más opciones" />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Olor</label>
                                <Select v-model="formWound.olor" :options="olor" optionLabel="name" optionValue="name"
                                    class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Borde de la herida</label>
                                <Select v-model="formWound.borde" :options="bordes" optionLabel="name"
                                    optionValue="name" class="w-full" disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Piel perisional</label>
                                <MultiSelect id="piel_perilesional" v-model="formWound.piel_perilesional"
                                    :options="piel_perilesional" filter optionLabel="label" optionValue="value"
                                    class="w-full" disabled placeholder="Selecciona una o más opciones" />
                            </div>
                        </div>

                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold px-4">
                                Zona de la herida (dimensiones)
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Fecha de medición</label>
                                <DatePicker v-model="formMeasurement.measurementDate" inputId="measurementDate"
                                    class="w-full min-w-0" placeholder="Seleccione una fecha" showIcon disabled />
                            </div>

                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)</label>
                                <InputText v-model="formMeasurement.length" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)</label>
                                <InputText v-model="formMeasurement.width" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)</label>
                                <InputText v-model="formMeasurement.area" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)</label>
                                <InputText v-model="formMeasurement.depth" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)</label>
                                <InputText v-model="formMeasurement.volume" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Tunelización</label>
                                <InputText v-model="formMeasurement.tunneling" class="w-full min-w-0" disabled />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento</label>
                                <InputText v-model="formMeasurement.undermining" class="w-full min-w-0" disabled />
                            </div>

                            <!-- Granulación -->
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Granulación (%)</label>
                                <InputText v-model="formMeasurement.granulation_percent" class="w-full" type="number"
                                    disabled />
                            </div>

                            <!-- Esfacelo -->
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Esfacelo (%)</label>
                                <InputText v-model="formMeasurement.slough_percent" class="w-full" type="number"
                                    disabled />
                            </div>

                            <!-- Necrosis -->
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Necrosis (%)</label>
                                <InputText v-model="formMeasurement.necrosis_percent" class="w-full" type="number"
                                    disabled />
                            </div>

                            <!-- Epitelización -->
                            <div>
                                <label class="flex items-center gap-1 mb-1 font-medium">Epitelización (%)</label>
                                <InputText v-model="formMeasurement.epithelialization_percent" class="w-full"
                                    type="number" disabled />
                            </div>
                        </div>

                        <!-- Barra visual -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5 mt-5">
                            <div class="md:col-span-3">
                                <div
                                    class="relative w-full h-6 bg-gray-200 rounded overflow-hidden shadow-inner border border-gray-300">
                                    <!-- Granulación -->
                                    <div class="absolute top-0 left-0 h-full bg-[#E90D0D] transition-all duration-300"
                                        :style="{ width: percentWidth('granulation_percent') + '%' }"
                                        title="Granulación">
                                    </div>

                                    <!-- Esfacelo -->
                                    <div class="absolute top-0 h-full bg-[#FFE415] transition-all duration-300"
                                        :style="{ left: percentOffset('granulation_percent'), width: percentWidth('slough_percent') + '%' }"
                                        title="Esfacelo"></div>

                                    <!-- Necrosis -->
                                    <div class="absolute top-0 h-full bg-black transition-all duration-300"
                                        :style="{ left: percentOffset('granulation_percent', 'slough_percent'), width: percentWidth('necrosis_percent') + '%' }"
                                        title="Necrosis"></div>

                                    <!-- Epitelización -->
                                    <div class="absolute top-0 h-full bg-[#F43AB6] transition-all duration-300"
                                        :style="{ left: percentOffset('granulation_percent', 'slough_percent', 'necrosis_percent'), width: percentWidth('epithelialization_percent') + '%' }"
                                        title="Epitelización"></div>
                                </div>

                                <!-- Total -->
                                <small class="block text-sm mt-1 text-gray-600">
                                    Total: {{ totalPercentage }}%
                                </small>
                                <small v-if="totalPercentage > 100" class="text-red-500 text-sm">
                                    ⚠️ La suma no debe exceder el 100%
                                </small>
                            </div>
                        </div>
                        <div
                            class="flex flex-col pt-5 px-4 border-surface-200 rounded bg-surface-50 dark:bg-surface-950 mt-6 overflow-y-auto max-h-[400px]">
                            <h3 class="text-xl font-semibold mb-4">Tratamientos</h3>

                            <div v-if="props.treatmentsHistory.length === 0">
                                <p>No hay tratamientos registrados para esta herida.</p>
                            </div>

                            <div v-for="treatment in props.treatmentsHistory" :key="treatment.id"
                                class="mb-6 border-b pb-4">
                                <p class="underline"><strong>Fecha del tratamiento:</strong> {{ treatment.beginDate }}
                                </p>
                                <p class="font-semibold mb-1">Descripción:</p>
                                <div class="w-full overflow-x-auto break-words whitespace-normal text-sm"
                                    v-html="treatment.description"></div>

                                <!-- Métodos -->
                                <div class="mt-3">
                                    <p class="font-semibold">Métodos:</p>
                                    <ul class="list-disc ml-6">
                                        <li v-for="method in treatment.methods" :key="method.id">
                                            {{
                                                props.treatmentMethods.find(m => m.id === method.treatment_method_id)?.name
                                                || `Método ID: ${method.treatment_method_id}`
                                            }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- Submétodos -->
                                <div class="mt-3">
                                    <p class="font-semibold">Submétodos:</p>
                                    <ul class="list-disc ml-6">
                                        <li v-for="sub in treatment.submethods" :key="sub.id">
                                            {{
                                                props.treatmentSubmethods.find(s => s.id ===
                                                    sub.treatment_submethod_id)?.name
                                                || `Submétodo ID: ${sub.treatment_submethod_id}`
                                            }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold px-4">
                                Evidencia de la herida

                            </h3>
                        </div>

                        <!-- Modal de Zoom -->
                        <Dialog v-model:visible="showZoomModal" modal header="Evidencia de la herida"
                            :style="{ width: '90vw', height: '90vh' }" :contentStyle="{
                                padding: 0,
                                margin: 0,
                                height: '100%',
                                display: 'flex',
                                justifyContent: 'center',
                                alignItems: 'center'
                            }">
                            <img :src="zoomImageUrl" class="zoom-img" :style="{
                                ...getImageStyle(zoomRotation),
                                width: '65%',
                                maxWidth: '600px',
                                maxHeight: '80vh',
                                objectFit: 'contain',
                                borderRadius: '0.5rem',
                                marginTop: '2rem'
                            }" />
                        </Dialog>

                        <!-- Galería -->
                        <div v-if="existingImagesHistory.length > 0" class="flex flex-col lg:flex-row gap-8">
                            <div class="flex-1 flex flex-col lg:flex-row gap-8">
                                <div
                                    class="flex-1 p-2 px-5 bg-surface-0 dark:bg-surface-900 shadow-sm overflow-hidden rounded-xl flex flex-col gap-10">
                                    <img :src="selectedImage"
                                        class="w-full flex-1 max-h-[40rem] rounded-lg object-cover cursor-zoom-in"
                                        alt="Imagen principal"
                                        @click="openZoomModal(selectedImage, selectedImageRotation)" />

                                    <div
                                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-10 gap-y-6 pl-6 pr-15">

                                        <img v-for="img in existingImagesHistory" :key="img.id"
                                            :src="`/storage/${img.content}`" :style="getImageStyle(img.position || 0)"
                                            class="w-full h-auto min-h-20 rounded-lg object-cover cursor-pointer transition-all duration-150"
                                            :class="{
                                                'shadow-[0_0_0_2px] shadow-surface-900 dark:shadow-surface-0':
                                                    selectedImage === `/storage/${img.content}`,
                                            }" @click="selectImage(img)" alt="Miniatura" />
                                    </div>

                                    <Button label="Descargar"
                                        class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md" rounded outlined
                                        @click="downloadSelectedImage" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-show="currentStep === 2">
                    <div
                        class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                        <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Seguimiento de la herida</h2>

                        <form @submit.prevent="saveFollow" class="flex flex-col flex-grow overflow-auto">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6 px-4">
                                <!-- Fase -->
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fase de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select v-model="formFollow.wound_phase_id" :options="props.woundsPhase"
                                        optionLabel="name" placeholder="Seleccione una fase" optionValue="id" filter
                                        class="w-full" :class="{
                                            'p-invalid': submittedFollow && !formFollow.wound_phase_id,
                                        }" />
                                    <small v-if="submittedFollow && !formFollow.wound_phase_id" class="text-red-500">
                                        Debe seleccionar la fase.
                                    </small>
                                </div>
                            </div>


                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Evaluación de la herida
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <template v-if="
                                    [
                                        18,
                                        19,
                                        20,
                                        21,
                                        22,
                                        23,
                                        24,
                                        25,
                                        26,
                                        27,
                                        28,
                                        29,
                                        30,
                                        31,
                                        32,
                                        33,
                                    ].includes(formFollow.body_location_id)
                                ">
                                    <!-- Campos vasculares -->
                                    <div class="col-span-full">
                                        <h3 class="text-lg font-semibold text-gray-700 mb-2">
                                            Valoración vascular (solo aplica en heridas en pierna)
                                        </h3>
                                    </div>

                                    <!-- Campo de selección de tipo de valoración -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Índice tobillo brazo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <Select id="valoracion" v-model="formFollow.valoracion" :options="valoracion"
                                            filter optionLabel="name" optionValue="name" class="w-full"
                                            placeholder="Seleccione una opción" />
                                        <small v-if="errors.valoracion" class="text-red-500">{{ errors.valoracion
                                            }}</small>
                                        <p class="text-sm text-gray-500 mt-1">Valor seleccionado: {{
                                            formFollow.valoracion }}</p>

                                    </div>

                                    <div v-if="['Manual'].includes(formFollow.valoracion)">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            {{ formFollow.valoracion }} <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="MESI" v-model="formFollow.MESI" class="w-full" />
                                        <small v-if="errors.MESI" class="text-red-500">{{
                                            errors.MESI
                                        }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB izquierdo <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_izquierdo" v-model="formFollow.ITB_izquierdo"
                                            class="w-full" />
                                        <small v-if="errors.ITB_izquierdo" class="text-red-500">{{
                                            errors.ITB_izquierdo
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            ITB derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="ITB_derecho" v-model="formFollow.ITB_derecho" class="w-full" />
                                        <small v-if="errors.ITB_derecho" class="text-red-500">{{
                                            errors.ITB_derecho
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_izquierdo"
                                            v-model="formFollow.pulse_dorsal_izquierdo" class="w-full" />
                                        <small v-if="errors.pulse_dorsal_izquierdo" class="text-red-500">{{
                                            errors.pulse_dorsal_izquierdo
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_izquierdo"
                                            v-model="formFollow.pulse_popliteo_izquierdo" class="w-full" />
                                        <small v-if="errors.pulse_popliteo_izquierdo" class="text-red-500">{{
                                            errors.pulse_popliteo_izquierdo
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior izquierdo
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_izquierdo"
                                            v-model="formFollow.pulse_tibial_izquierdo" class="w-full" />
                                        <small v-if="errors.pulse_tibial_izquierdo" class="text-red-500">{{
                                            errors.pulse_tibial_izquierdo
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso dorsal pedio derecho
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_dorsal_derecho" v-model="formFollow.pulse_dorsal_derecho"
                                            class="w-full" />
                                        <small v-if="errors.pulse_dorsal_derecho" class="text-red-500">{{
                                            errors.pulse_dorsal_derecho
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso poplíteo derecho <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_popliteo_derecho"
                                            v-model="formFollow.pulse_popliteo_derecho" class="w-full" />
                                        <small v-if="errors.pulse_popliteo_derecho" class="text-red-500">{{
                                            errors.pulse_popliteo_derecho
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Pulso tibial posterior derecho
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="pulse_tibial_derecho" v-model="formFollow.pulse_tibial_derecho"
                                            class="w-full" />
                                        <small v-if="errors.pulse_tibial_derecho" class="text-red-500">{{
                                            errors.pulse_tibial_derecho
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Monofilamento <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="monofilamento" v-model="formFollow.monofilamento"
                                            class="w-full" />
                                        <small v-if="errors.monofilamento" class="text-red-500">{{
                                            errors.monofilamento
                                            }}</small>
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Nivel de glucosa en sangre
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <InputText id="blood_glucose" v-model="formFollow.blood_glucose"
                                            class="w-full" />
                                        <small v-if="errors.blood_glucose" class="text-red-500">{{
                                            errors.blood_glucose
                                            }}</small>
                                    </div>
                                </template>

                                <div class="col-span-full">
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">
                                        Información de la herida
                                    </h3>
                                </div>


                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Edema <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="edema" v-model="formFollow.edema" :options="edema" filter
                                        optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.edema" class="text-red-500">{{
                                        errors.edema
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="dolor" v-model="formFollow.dolor" :options="dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.dolor" class="text-red-500">{{
                                        errors.dolor
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Tipo de dolor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="tipo_dolor" v-model="formFollow.tipo_dolor" :options="tipo_dolor" filter
                                        optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.tipo_dolor" class="text-red-500">{{
                                        errors.tipo_dolor
                                        }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Escala Visual Analógica (EVA)
                                        <span class="text-red-600">*</span>
                                    </label>
                                    <InputText id="visual_scale" v-model="formFollow.visual_scale" class="w-full"
                                        :class="{
                                            'p-invalid': submittedFollow && !formFollow.visual_scale,
                                        }" placeholder="Ej: 3/10" @input="onVisualScaleInputFollow" />
                                    <small v-if="errors.visual_scale" class="text-red-500">{{
                                        errors.visual_scale
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Tipo) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_tipo" v-model="formFollow.exudado_tipo" :options="exudado_tipo"
                                        filter optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_tipo" class="text-red-500">{{
                                        errors.exudado_tipo
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Exudado (Cantidad) <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="exudado_cantidad" v-model="formFollow.exudado_cantidad"
                                        :options="exudado_cantidad" filter optionLabel="name" optionValue="name"
                                        class="w-full" placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.exudado_cantidad" class="text-red-500">{{
                                        errors.exudado_cantidad
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Infección <span class="text-red-600">*</span>
                                    </label>
                                    <MultiSelect id="infeccion" v-model="formFollow.infeccion" :options="infeccion"
                                        optionLabel="label" optionValue="value" class="w-full" filter
                                        placeholder="Selecciona una o más opciones" />
                                    <small v-if="errors.infeccion" class="text-red-500">{{
                                        errors.infeccion
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Olor <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="olor" v-model="formFollow.olor" :options="olor" filter
                                        optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.olor" class="text-red-500">{{ errors.olor }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Borde de la herida <span class="text-red-600">*</span>
                                    </label>
                                    <Select id="borde" v-model="formFollow.borde" :options="bordes" filter
                                        optionLabel="name" optionValue="name" class="w-full"
                                        placeholder="Seleccione una opción">
                                    </Select>
                                    <small v-if="errors.borde" class="text-red-500">{{
                                        errors.borde
                                        }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Piel perilesional <span class="text-red-600">*</span>
                                    </label>
                                    <MultiSelect id="piel_perilesional" v-model="formFollow.piel_perilesional"
                                        :options="piel_perilesional" filter optionLabel="label" optionValue="value"
                                        class="w-full" placeholder="Selecciona una o más opciones" />
                                    <small v-if="errors.piel_perilesional" class="text-red-500">{{
                                        errors.piel_perilesional
                                        }}</small>
                                </div>
                            </div>

                            <!-- Nueva sección: Zona de la herida (dimensiones) -->
                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                                <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                    Zona de la herida (dimensiones)
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">
                                        Fecha de medición
                                        <span class="text-red-600">*</span>
                                    </label>
                                    <DatePicker v-model="formFollow.measurementDate" inputId="measurementDate"
                                        class="w-full" placeholder="mm/dd/yyyy" showIcon />
                                    <small v-if="errors.measurementDate" class="text-red-500">{{
                                        errors.measurementDate
                                        }}</small>
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.length" class="w-full" />
                                    <small v-if="errors.length" class="text-red-500">{{
                                        errors.length
                                        }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.width" class="w-full" />
                                    <small v-if="errors.width" class="text-red-500">{{
                                        errors.width
                                        }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)</label>
                                    <InputText v-model="formFollow.area" class="w-full" disabled />
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)</label>
                                    <InputText v-model="formFollow.depth" class="w-full" />
                                    <small v-if="errors.depth" class="text-red-500">{{
                                        errors.depth
                                        }}</small>
                                </div>
                                <!-- Mostrar campo volumen solo si profundidad tiene valor numérico válido -->
                                <div v-if="parseFloat(formFollow.depth)">
                                    <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)</label>
                                    <InputText v-model="formFollow.volume" class="w-full" disabled />
                                </div>

                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Tunelización</label>
                                    <InputText v-model="formFollow.tunneling" class="w-full" />
                                    <small v-if="errors.tunneling" class="text-red-500">{{
                                        errors.tunneling
                                        }}</small>
                                </div>
                                <div>
                                    <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento<span
                                            class="text-red-600">*</span></label>
                                    <InputText v-model="formFollow.undermining" class="w-full" />
                                    <small v-if="errors.undermining" class="text-red-500">{{
                                        errors.undermining
                                        }}</small>
                                </div>


                                <!-- Granulación -->
                                <div>
                                    <label for="granulation">Granulación (%)<span class="text-red-600">*</span></label>
                                    <InputText id="granulation" v-model="formFollow.granulation_percent" type="number"
                                        min="0" max="100" step="1" @input="adjustProgressFollow" class="w-full" />
                                    <small v-if="errors.granulation_percent" class="text-red-500">{{
                                        errors.granulation_percent }}</small>
                                </div>

                                <!-- Esfacelo -->
                                <div>
                                    <label for="slough">Esfacelo (%)<span class="text-red-600">*</span></label>
                                    <InputText id="slough" v-model="formFollow.slough_percent" type="number" min="0"
                                        max="100" step="1" @input="adjustProgressFollow" class="w-full" />
                                    <small v-if="errors.slough_percent" class="text-red-500">{{ errors.slough_percent
                                    }}</small>
                                </div>

                                <!-- Necrosis -->
                                <div>
                                    <label for="necrosis">Necrosis (%)<span class="text-red-600">*</span></label>
                                    <InputText id="necrosis" v-model="formFollow.necrosis_percent" type="number" min="0"
                                        max="100" step="1" @input="adjustProgressFollow" class="w-full" />
                                    <small v-if="errors.necrosis_percent" class="text-red-500">{{
                                        errors.necrosis_percent }}</small>
                                </div>

                                <!-- Epitelización -->
                                <div>
                                    <label for="epithelialization">Epitelización (%)<span
                                            class="text-red-600">*</span></label>
                                    <InputText id="epithelialization" v-model="formFollow.epithelialization_percent"
                                        type="number" min="0" max="100" step="1" @input="adjustProgressFollow"
                                        class="w-full" />
                                    <small v-if="errors.epithelialization_percent" class="text-red-500">{{
                                        errors.epithelialization_percent
                                    }}</small>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mt-5">
                                <div class="md:col-span-3">
                                    <div
                                        class="relative w-full h-6 bg-gray-200 rounded overflow-hidden shadow-inner border border-gray-300">
                                        <!-- Verde: Granulación -->
                                        <div class="absolute top-0 left-0 h-full bg-[#E90D0D] transition-all duration-300"
                                            :style="{ width: percentWidthFollow('granulation_percent') + '%' }"
                                            title="Granulación"></div>

                                        <!-- Amarillo: Esfacelo -->
                                        <div class="absolute top-0 h-full bg-[#FFE415] transition-all duration-300"
                                            :style="{ left: percentOffsetFollow('granulation_percent'), width: percentWidthFollow('slough_percent') + '%' }"
                                            title="Esfacelo"></div>

                                        <!-- Negro: Necrosis -->
                                        <div class="absolute top-0 h-full bg-black transition-all duration-300"
                                            :style="{ left: percentOffsetFollow('granulation_percent', 'slough_percent'), width: percentWidthFollow('necrosis_percent') + '%' }"
                                            title="Necrosis"></div>

                                        <!-- Azul: Epitelización -->
                                        <div class="absolute top-0 h-full bg-[#F43AB6] transition-all duration-300"
                                            :style="{ left: percentOffsetFollow('granulation_percent', 'slough_percent', 'necrosis_percent'), width: percentWidthFollow('epithelialization_percent') + '%' }"
                                            title="Epitelización"></div>
                                    </div>

                                    <!-- Texto de porcentaje total -->
                                    <small class="block text-sm mt-1 text-gray-600">
                                        Total: {{ totalPercentageFollow }}%
                                    </small>

                                    <small v-if="totalPercentageFollow > 100" class="text-red-500 text-sm">
                                        ⚠️ La suma no debe exceder el 100%
                                    </small>
                                </div>
                            </div>


                            <div class="mt-6 flex flex-col sm:flex-row justify-end sm:justify-end gap-2 px-4 py-6">
                                <Button :label="hasFollow ? 'Actualizar' : 'Guardar'" icon="pi pi-check" type="submit"
                                    :loading="isSavingFollow" :disabled="isSavingFollow" />
                            </div>
                        </form>

                        <!-- Nueva sección: Evidencia de la herida -->
                        <div
                            class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                            <h3 class="text-xl font-semibold mb-4 px-4 pt-4">
                                Evidencia de la herida
                            </h3>
                        </div>

                        <div class="card">
                            <Toast />
                            <FileUpload ref="fileUploadRefFollow" name="images[]" :customUpload="true" :multiple="true"
                                :maxFileSize="9000000" accept="image/*" @select="onSelectedFilesFollow"
                                @uploader="uploadEventFollow">

                                <template #header="{ chooseCallback, clearCallback }">
                                    <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                                        <div class="flex gap-2">
                                            <Button @click="chooseCallback()" icon="pi pi-images" rounded outlined
                                                severity="secondary" />
                                            <Button @click="showConfirmUploadModalFollow = true"
                                                icon="pi pi-cloud-upload" rounded outlined severity="success"
                                                :disabled="!uploadFilesFollow.length" />
                                            <Button @click="() => clearTemplatedUploadFollow(clearCallback)"
                                                icon="pi pi-times" rounded outlined severity="danger"
                                                :disabled="!uploadFilesFollow.length" />
                                        </div>
                                        <ProgressBar :value="totalSizePercentFollow" :showValue="false"
                                            class="md:w-20rem h-1 w-full md:ml-auto">
                                            <span class="whitespace-nowrap">
                                                {{ (totalSizeFollow / 1024 / 1024).toFixed(2) }}MB / 9MB
                                            </span>
                                        </ProgressBar>
                                    </div>
                                </template>


                                <template #content>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                                        <div v-for="(file, index) in uploadFilesFollow" :key="file.name + file.size"
                                            class="p-4 rounded border border-surface flex flex-col items-center gap-4">
                                            <img :src="file.objectURL" :style="getImageStyle(file.rotation)"
                                                class="w-full h-auto max-h-[20rem] object-contain transition-transform duration-300 cursor-zoom-in"
                                                alt="imagen" @click="openZoomModal(file.objectURL, file.rotation)" />
                                            <div class="flex gap-2">
                                                <Button icon="pi pi-replay" @click="rotateImageFollow(index, 'left')"
                                                    rounded outlined />
                                                <Button icon="pi pi-refresh" @click="rotateImageFollow(index, 'right')"
                                                    rounded outlined />
                                            </div>
                                            <span class="text-sm text-center">{{ file.name }}</span>
                                            <Badge value="Pendiente" severity="warn" />
                                            <Button icon="pi pi-times" @click="removeFileFollow(index)" rounded outlined
                                                severity="danger" />
                                        </div>
                                    </div>
                                </template>
                            </FileUpload>
                        </div>

                        <Dialog v-model:visible="showConfirmUploadModalFollow" modal header="Evidencia de la herida"
                            :style="{ width: '400px' }">
                            <div class="text-center p-4">
                                <p class="mb-4">¿Desea subir las imágenes seleccionadas?</p>
                                <div class="flex justify-center gap-3">
                                    <Button label="Cancelar" icon="pi pi-check" text
                                        @click="showConfirmUploadModalFollow = false" />
                                    <Button label="Confirmar" icon="pi pi-check" severity="success"
                                        @click="confirmUploadFollow" autofocus />
                                </div>
                            </div>
                        </Dialog>


                        <!-- Modal de Zoom -->
                        <Dialog v-model:visible="showZoomModal" modal header="Evidencia de la herida"
                            :style="{ width: '90vw', height: '90vh' }" :contentStyle="{
                                padding: 0,
                                margin: 0,
                                height: '100%',
                                display: 'flex',
                                justifyContent: 'center',
                                alignItems: 'center'
                            }">
                            <img :src="zoomImageUrl" class="zoom-img" :style="{
                                ...getImageStyle(zoomRotation),
                                width: '65%',
                                maxWidth: '600px',
                                maxHeight: '80vh',
                                objectFit: 'contain',
                                borderRadius: '0.5rem',
                                marginTop: '2rem'
                            }" />
                        </Dialog>
                        <!-- Modal de Límite -->
                        <Dialog v-model:visible="showLimitModalFollow" modal header="Límite de imágenes"
                            :style="{ width: '400px' }">
                            <div class="text-center p-4">
                                <p class="mb-4">Solo puedes subir un máximo de <strong>4 imágenes</strong>.</p>
                                <Button label="Entendido" icon="pi pi-check" @click="closeLimitModal" autofocus />
                            </div>
                        </Dialog>

                        <!-- Galería -->
                        <div v-if="existingImagesFollow.length > 0" class="flex flex-col lg:flex-row gap-8">
                            <div class="flex-1 flex flex-col lg:flex-row gap-8">
                                <div
                                    class="flex-1 p-2 px-5 bg-surface-0 dark:bg-surface-900 shadow-sm overflow-hidden rounded-xl flex flex-col gap-10">
                                    <img :src="selectedImage"
                                        class="w-full flex-1 max-h-[40rem] rounded-lg object-cover cursor-zoom-in"
                                        alt="Imagen principal"
                                        @click="openZoomModal(selectedImage, selectedImageRotation)" />

                                    <div
                                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-10 gap-y-6 pl-6 pr-15 mt-6">
                                        <div v-for="img in existingImagesFollow" :key="img.id" class="relative group">
                                            <img :src="`/storage/${img.content}`"
                                                :style="{ transform: `rotate(${img.position || 0}deg)` }"
                                                class="w-full h-auto min-h-20 rounded-lg object-cover cursor-pointer transition-all duration-150"
                                                :class="{
                                                    'shadow-[0_0_0_2px] shadow-surface-900 dark:shadow-surface-0':
                                                        selectedImage === `/storage/${img.content}`,
                                                }" @click="selectImage(img)" alt="Miniatura" />

                                            <!-- Botón borrar por miniatura -->
                                            <button type="button" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity
               bg-red-600 text-white rounded-md px-2 py-1 text-xs shadow"
                                                @click.stop="openConfirmDeleteByThumbFollow(img)">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>

                                    <Button label="Descargar"
                                        class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md" rounded outlined
                                        @click="downloadSelectedImage" />

                                    <Button label="Eliminar" severity="danger"
                                        class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md" rounded
                                        :disabled="!selectedImage" @click="openConfirmDeleteSelectedFollow" />
                                </div>
                            </div>
                        </div>

                        <Dialog v-model:visible="showConfirmDeleteModalFollow" modal :draggable="false"
                            header="Confirmar eliminación" :style="{ width: '28rem' }">
                            <p class="mb-6">
                                ¿Seguro que quieres eliminar esta imagen? Esta acción no se puede deshacer.
                            </p>
                            <div class="flex justify-end gap-3">
                                <Button label="Cancelar" outlined @click="showConfirmDeleteModalFollow = false" />
                                <Button label="Eliminar" severity="danger" @click="deleteImageFollow" />
                            </div>
                        </Dialog>


                        <!-- Sección: Establecer tratamiento -->
                        <form @submit.prevent="storeTreatment">
                            <div
                                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto mt-10">
                                <h3 class="text-xl font-semibold mb-4 px-4">
                                    Establecer tratamiento
                                </h3>

                                <!-- Métodos y Submétodos -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6 px-4">
                                    <!-- Selección de métodos -->
                                    <div>
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            Seleccionar métodos
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <MultiSelect v-model="formTreat.methods" :options="treatmentMethods"
                                            optionLabel="name" optionValue="id" display="chip" class="w-full min-w-0"
                                            placeholder="Seleccione uno o varios métodos" />
                                        <small v-if="errors.methods" class="text-red-500">{{ errors.methods }}</small>
                                    </div>

                                    <!-- Submétodos por método seleccionado -->
                                    <div v-for="method in selectedMethodsWithSubmethods" :key="method.id">
                                        <label class="flex items-center gap-1 mb-1 font-medium">
                                            {{ method.name }}
                                        </label>
                                        <MultiSelect v-model="formTreat.submethodsByMethod[method.id]"
                                            :options="method.submethods" class="w-full min-w-0" optionLabel="name"
                                            optionValue="id" placeholder="Seleccione submétodos" display="chip" />
                                    </div>
                                </div>

                                <!-- Descripción final -->
                                <div class="grid grid-cols-3 gap-6 px-4 mt-6">
                                    <div class="col-span-3">
                                        <label for="description" class="flex items-center gap-1 mb-1 font-medium">
                                            Descripción final del tratamiento de la herida
                                            <span class="text-red-600">*</span>
                                        </label>
                                        <Editor v-model="formTreat.description" editorStyle="height: 150px"
                                            class="w-full min-w-0" />
                                        <small v-if="errors.description" class="text-red-500">{{
                                            errors.description
                                        }}</small>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2 px-4 py-6">
                                    <Button :label="hasTreatment ? 'Actualizar' : 'Guardar'" icon="pi pi-check" text
                                        type="submit" :loading="isSavingTreatment" :disabled="isSavingTreatment" />
                                    <Button v-if="canFinishConsultation" label="Terminar consulta" icon="pi pi-sign-out"
                                        severity="danger" @click="confirmFinishConsultation"
                                        :loading="isSavingTreatment" />
                                </div>

                            </div>
                        </form>

                        <Dialog v-model:visible="showConfirmFinishDialog" modal header="Confirmar acción"
                            :style="{ width: '400px' }">
                            <div class="text-center p-4">
                                <p class="mb-4 text-justify">
                                    Esta consulta tiene <strong>{{ woundsInAppointment }}</strong> herida(s)
                                    registrada(s).
                                    &nbsp; ¿Estás seguro de que todas han sido correctamente configuradas y deseas
                                    finalizar la
                                    consulta?<br /><br />
                                    <strong> Esta acción no se puede deshacer. Una vez finalizada, no podrás modificar
                                        las heridas
                                        asociadas.</strong>
                                </p>
                                <div class="flex justify-center gap-3">
                                    <Button label="Cancelar" icon="pi pi-times" text
                                        @click="showConfirmFinishDialog = false" />
                                    <Button label="Finalizar" icon="pi pi-check" severity="danger"
                                        @click="onConfirmFinishConsultation" autofocus />
                                </div>
                            </div>
                        </Dialog>


                    </div>
                </div>

            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.p-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 2rem;
    padding-left: 0;
    list-style: none;
    user-select: none;
}

.p-steps::before {
    content: "";
    position: absolute;
    top: 1.25rem;
    left: 2rem;
    right: 2rem;
    height: 2px;
    background-color: #dcdcdc;
    z-index: 0;
}

.p-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    position: relative;
    flex: 1;
    color: #6c757d;
    font-weight: 600;
    z-index: 1;
}

.p-step-number {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #dee2e6;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
    user-select: none;
}

.p-step-active {
    color: #f10e0e;
    font-weight: 700;
}

.p-step-active .p-step-number {
    background-color: #f10e0e;
    color: white;
    border-color: #f10e0e;
}

.p-step-title {
    font-size: 0.9rem;
    text-align: center;
    user-select: none;
}

.zoom-img {
    margin-top: 0;
    /* Por defecto (móviles y tablets) */
}

/* Solo en pantallas grandes (≥ 1024px) */
@media (min-width: 1024px) {
    .zoom-img {
        margin-top: 5rem;
        /* o 6rem, 100px según necesites */
    }
}
</style>