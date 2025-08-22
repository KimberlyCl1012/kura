<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, watch, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Button from "primevue/button";
import { Badge, DatePicker, ProgressBar } from "primevue";
import Editor from "primevue/editor";
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';

const props = defineProps({
  woundsType: Array,
  woundsSubtype: Array,
  woundsPhase: Array,
  bodyLocations: Array,
  bodySublocation: Array,
  woundHistory: Object,
});

const toast = useToast();
const currentStep = ref(1);
const isSavingUser = ref(false);
const submittedUser = ref(false);
const errors = ref({});

const bordes = ref([{ name: "Adherido" }, { name: "No adherido" }, { name: "Enrollado" }, { name: "Epitalizado" }]);
const valoracion = ref([{ name: "Manual" }, { name: "MESI" }, { name: "No aplica" }]);
const edema = ref([{ name: "+++" }, { name: "++" }, { name: "+" }, { name: "No aplica" }]);
const dolor = ref([{ name: "En reposo" }, { name: "Con movimiento" }, { name: "Ninguno" }]);
const grades = ref([{ id: 1, name: "1" }, { id: 2, name: "2" }, { id: 3, name: "3" }]);
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

const formWoundHistory = ref({
  id: null,
  wound_type_id: null,
  grade_foot: null,
  wound_subtype_id: null,
  body_location_id: null,
  body_sublocation_id: null,
  wound_phase_id: null,
  woundBeginDate: null,
  woundHealthDate: null,
  measurementDate: null,
  valoracion: "",
  MESI: "",
  borde: null,
  edema: null,
  dolor: null,
  exudado_cantidad: null,
  exudado_tipo: null,
  olor: null,
  piel_perilesional: null,
  infeccion: null,
  tipo_dolor: null,
  visual_scale: "",
  blood_glucose: "",
  ITB_izquierdo: "",
  pulse_dorsal_izquierdo: "",
  pulse_tibial_izquierdo: "",
  pulse_popliteo_izquierdo: "",
  ITB_derecho: "",
  pulse_dorsal_derecho: "",
  pulse_tibial_derecho: "",
  pulse_popliteo_derecho: "",
  length: '',
  width: '',
  depth: '',
  area: '',
  volume: '',
  tunneling: '',
  undermining: '',
  granulation_percent: '',
  slough_percent: '',
  necrosis_percent: '',
  epithelialization_percent: '',
  description: ''
});

const requiresVascular = computed(() =>
  [
    18, 19, 20, 21, 22, 23, 24, 25,
    26, 27, 28, 29, 30, 31, 32, 33,
  ].includes(formWoundHistory.value.body_location_id)
);

function goToStep(step) {
  currentStep.value = step;
}

const addError = (field, msg = 'Este campo es obligatorio.') => {
  errors.value[field] = msg;
};

const isMissing = (v) =>
  v === null || v === undefined || (typeof v === 'string' && v.trim() === '');

const validateRequired = (fields) => {
  const missing = [];
  fields.forEach((f) => {
    if (isMissing(formWoundHistory.value[f])) {
      addError(f);
      missing.push(f);
    }
  });
  return missing;
};


const saveUser = async () => {
  submittedUser.value = true;
  isSavingUser.value = true;
  errors.value = {};

  // ---------- Paso 1: requeridos básicos ----------
  if (currentStep.value === 1) {
    const requiredStep1 = [
      'wound_type_id',
      'wound_subtype_id',
      'body_location_id',
      'body_sublocation_id',
      'wound_phase_id',
      'woundBeginDate',
    ];
    if (parseInt(formWoundHistory.value.wound_type_id) === 8) {
      requiredStep1.push('grade_foot'); // Diabética con grado
    }

    const miss1 = validateRequired(requiredStep1);
    if (miss1.length) {
      toast.add({
        severity: 'error',
        summary: 'Error de validación',
        detail: 'Completa los campos requeridos del antecedente.',
        life: 4000,
      });
      isSavingUser.value = false;
      return;
    }
  }

  // ---------- Paso 2: requeridos de evaluación ----------
  if (currentStep.value === 2) {
    // Requeridos generales del paso 2
    const requiredStep2 = [
      'woundHealthDate',
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
      'depth',
      'tunneling',
      'undermining',
      'granulation_percent',
      'slough_percent',
      'necrosis_percent',
      'epithelialization_percent',
      'description'
    ];
    validateRequired(requiredStep2);

    // Requeridos vasculares si la ubicación lo amerita
    if (requiresVascular.value) {
      const vascularFields = [
        'valoracion',
        'ITB_izquierdo',
        'ITB_derecho',
        'pulse_dorsal_izquierdo',
        'pulse_dorsal_derecho',
        'pulse_popliteo_izquierdo',
        'pulse_popliteo_derecho',
        'pulse_tibial_izquierdo',
        'pulse_tibial_derecho',
        'blood_glucose',
      ];
      if (['MESI', 'Manual'].includes(formWoundHistory.value.valoracion)) {
        vascularFields.push('MESI');
      }
      validateRequired(vascularFields);
    }

    // Validaciones de formato/reglas:
    // EVA (formato n/10 y rango 1-10)
    if (!errors.value.visual_scale && !/^\d{1,2}\/10$/.test(formWoundHistory.value.visual_scale)) {
      addError('visual_scale', 'Formato inválido. Usa n/10 (ej. 3/10).');
    } else if (!errors.value.visual_scale) {
      const n = parseInt(formWoundHistory.value.visual_scale.split('/')[0], 10);
      if (isNaN(n) || n < 1 || n > 10) addError('visual_scale', 'Debe estar entre 1/10 y 10/10.');
    }

    // Números positivos: length, width, depth
    ['length', 'width', 'depth'].forEach((k) => {
      if (!errors.value[k]) {
        const v = parseFloat(formWoundHistory.value[k]);
        if (isNaN(v) || v <= 0) addError(k, 'Debe ser un número mayor a 0.');
      }
    });

    // Porcentajes 0–100 y suma <= 100
    const pctKeys = [
      'granulation_percent',
      'slough_percent',
      'necrosis_percent',
      'epithelialization_percent',
    ];
    let pctSum = 0;
    pctKeys.forEach((k) => {
      if (!errors.value[k]) {
        const v = parseFloat(formWoundHistory.value[k]);
        if (isNaN(v) || v < 0 || v > 100) {
          addError(k, 'Debe ser un porcentaje entre 0 y 100.');
        } else {
          pctSum += v;
        }
      }
    });
    if (pctKeys.some((k) => errors.value[k])) {
      // ya hay errores de porcentaje individuales
    } else if (pctSum > 100) {
      pctKeys.forEach((k) => addError(k, 'La suma total no debe exceder 100%.'));
    }

    // Si hay cualquier error, detener
    if (Object.keys(errors.value).length) {
      toast.add({
        severity: 'error',
        summary: 'Error de validación',
        detail: 'Corrige los campos marcados en la evaluación.',
        life: 4000,
      });
      isSavingUser.value = false;
      return;
    }
  }

  // ---------- Envío ----------
  try {
    const payload = {
      ...formWoundHistory.value,
      woundBeginDate: formWoundHistory.value.woundBeginDate
        ? new Date(formWoundHistory.value.woundBeginDate).toISOString().slice(0, 10)
        : null,
      woundHealthDate: formWoundHistory.value.woundHealthDate
        ? new Date(formWoundHistory.value.woundHealthDate).toISOString().slice(0, 10)
        : null,
      measurementDate: formWoundHistory.value.measurementDate
        ? new Date(formWoundHistory.value.measurementDate).toISOString().slice(0, 10)
        : null, // 👈 NUEVO
    };

    const response = await axios.put(`/wounds_histories/${formWoundHistory.value.id}/edit`, payload);

    if (response.data?.message) {
      toast.add({ severity: 'success', summary: 'Éxito', detail: response.data.message, life: 3000 });
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Ocurrió un error al guardar.', life: 3000 });
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
      toast.add({ severity: 'error', summary: 'Error de validación', detail: 'Corrige los errores del formulario.', life: 4000 });
    } else {
      console.error(error);
      toast.add({ severity: 'error', summary: 'Error', detail: 'Error inesperado al guardar.', life: 4000 });
    }
  } finally {
    isSavingUser.value = false;
  }
};

// Carga inicial forzada de datos
const woundSubtypes = ref([]);
const bodySublocations = ref([]);
const isInitialLoadType = ref(true);
const isInitialLoadLocation = ref(true);

onMounted(() => {
  if (props.woundHistory) {
    formWoundHistory.value.wound_type_id = null;
    formWoundHistory.value.body_location_id = null;

    Object.assign(formWoundHistory.value, {
      ...props.woundHistory,
      woundBeginDate: props.woundHistory.woundBeginDate ? new Date(props.woundHistory.woundBeginDate) : null,
      woundHealthDate: props.woundHistory.woundHealthDate ? new Date(props.woundHistory.woundHealthDate) : null,
      measurementDate: props.woundHistory.measurementDate ? new Date(props.woundHistory.measurementDate) : null,
      grade_foot: props.woundHistory.grade_foot ? parseInt(props.woundHistory.grade_foot) : null,
    });

    if (props.woundHistory.wound_type_id) {
      formWoundHistory.value.wound_type_id = parseInt(props.woundHistory.wound_type_id);
      loadSubtypes(formWoundHistory.value.wound_type_id);
    }

    if (props.woundHistory.body_location_id) {
      formWoundHistory.value.body_location_id = parseInt(props.woundHistory.body_location_id);
      loadSublocations(formWoundHistory.value.body_location_id);
    }
  }

  loadExistingImages();
});

// --- Eliminar imágenes (Historial) ---
const showConfirmDeleteModal = ref(false);
const imageToDelete = ref(null);

const openConfirmDeleteSelected = () => {
  if (!selectedImage.value) return;
  const img = existingImages.value.find(i => `/storage/${i.content}` === selectedImage.value);
  if (!img) return;
  imageToDelete.value = img;
  showConfirmDeleteModal.value = true;
};

const openConfirmDeleteByThumb = (img) => {
  imageToDelete.value = img;
  showConfirmDeleteModal.value = true;
};

const deleteImage = async () => {
  if (!imageToDelete.value?.id) return;

  try {
    await axios.delete(`/media_history/${imageToDelete.value.id}`);

    // Quitar del arreglo local
    existingImages.value = existingImages.value.filter(i => i.id !== imageToDelete.value.id);

    // Si la eliminada era la seleccionada, elegir otra o limpiar
    if (selectedImage.value === `/storage/${imageToDelete.value.content}`) {
      if (existingImages.value.length > 0) {
        selectImage(existingImages.value[0]);
      } else {
        selectedImage.value = '';
        selectedImageRotation.value = 0;
      }
    }

    toast.add({ severity: 'success', summary: 'Eliminada', detail: 'Imagen eliminada correctamente.', life: 3000 });
  } catch (err) {
    console.error(err);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo eliminar la imagen.', life: 4000 });
  } finally {
    showConfirmDeleteModal.value = false;
    imageToDelete.value = null;
  }
};

const clearTemplatedUpload = (clear) => {
  clear();
  resetUploads();
};


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

watch(() => formWoundHistory.value.wound_type_id, (newVal) => {
  if (isInitialLoadType.value) {
    isInitialLoadType.value = false;
    return;
  }

  const typeId = parseInt(newVal);
  formWoundHistory.value.wound_subtype_id = null;
  woundSubtypes.value = [];

  if (typeId) {
    loadSubtypes(typeId);
  }
});

watch(() => formWoundHistory.value.body_location_id, (newVal) => {
  if (isInitialLoadLocation.value) {
    isInitialLoadLocation.value = false;
    return;
  }

  const locationId = parseInt(newVal);
  formWoundHistory.value.body_sublocation_id = null;
  bodySublocations.value = [];

  if (locationId) {
    loadSublocations(locationId);
  }
});

watch(() => [formWoundHistory.value.length, formWoundHistory.value.width], ([length, width]) => {
  const l = parseFloat(length);
  const w = parseFloat(width);
  if (!isNaN(l) && !isNaN(w)) {
    formWoundHistory.value.area = (l * w).toFixed(2);
  } else {
    formWoundHistory.value.area = '';
  }
  if (formWoundHistory.value.depth) {
    const d = parseFloat(formWoundHistory.value.depth);
    if (!isNaN(d)) {
      formWoundHistory.value.volume = (l * w * d).toFixed(2);
    }
  }
});

watch(() => formWoundHistory.value.depth, (depth) => {
  const d = parseFloat(depth);
  const a = parseFloat(formWoundHistory.value.area);
  formWoundHistory.value.volume = (!isNaN(d) && !isNaN(a)) ? (a * d).toFixed(2) : '';
});

function onVisualScaleInput(event) {
  const input = event.target.value.replace("/10", "");
  let number = parseInt(input);
  if (!isNaN(number)) {
    if (number < 1) number = 1;
    if (number > 10) number = 10;
    formWoundHistory.value.visual_scale = `${number}/10`;
  } else {
    formWoundHistory.value.visual_scale = "";
  }
}

function adjustProgress() { }

const totalPercentage = computed(() => {
  const g = parseFloat(formWoundHistory.value.granulation_percent) || 0;
  const s = parseFloat(formWoundHistory.value.slough_percent) || 0;
  const n = parseFloat(formWoundHistory.value.necrosis_percent) || 0;
  const e = parseFloat(formWoundHistory.value.epithelialization_percent) || 0;
  return g + s + n + e;
});

function percentWidth(field) {
  const value = parseFloat(formWoundHistory.value[field]) || 0;
  const total = totalPercentage.value;
  if (total === 0) return 0;
  if (total > 100) return ((value / total) * 100).toFixed(2);
  return value.toFixed(2);
}

function percentOffset(...fields) {
  const total = totalPercentage.value;
  let sum = 0;
  for (const field of fields) {
    const val = parseFloat(formWoundHistory.value[field]) || 0;
    sum += val;
  }
  if (total > 100) return ((sum / total) * 100).toFixed(2) + '%';
  return sum + '%';
}

// -------------------------
// Evidencia fotográfica
// -------------------------
const MAX_FILES = 4;
const zoomImageUrl = ref('');
const zoomRotation = ref(0);
const showZoomModal = ref(false);
const showLimitModal = ref(false);
const uploadFiles = ref([]);
const totalSize = ref(0);
const totalSizePercent = ref(0);
const existingImages = ref([]);
const selectedImage = ref('');
const selectedImageRotation = ref(0);
const fileUploadRef = ref(null);
const showConfirmUploadModal = ref(false);

const getImageStyle = (rotation) => ({ transform: `rotate(${rotation}deg)` });

const updateTotalSize = () => {
  totalSize.value = uploadFiles.value.reduce((acc, f) => acc + f.size, 0);
  totalSizePercent.value = totalSize.value / 10;
};

const resetUploads = () => {
  uploadFiles.value = [];
  totalSize.value = 0;
  totalSizePercent.value = 0;
};

const isLimitReached = (incomingCount) =>
  existingImages.value.length + uploadFiles.value.length + incomingCount > MAX_FILES;

const openZoomModal = (src, rotation) => {
  zoomImageUrl.value = src;
  zoomRotation.value = rotation;
  showZoomModal.value = true;
};

const closeLimitModal = () => {
  showLimitModal.value = false;
};

const onSelectedFiles = (event) => {
  const incoming = event.files;
  if (isLimitReached(incoming.length)) {
    showLimitModal.value = true;
    return;
  }

  incoming.forEach(file => {
    uploadFiles.value.push({
      raw: file,
      name: file.name,
      size: file.size,
      type: file.type,
      objectURL: URL.createObjectURL(file),
      rotation: 0,
    });
  });

  updateTotalSize();
};

const rotateImage = (index, direction) => {
  const file = uploadFiles.value[index];
  file.rotation = direction === 'left'
    ? (file.rotation - 5 + 360) % 360
    : (file.rotation + 5) % 360;
};

const removeFile = (index) => {
  uploadFiles.value.splice(index, 1);
  updateTotalSize();
};

const selectImage = (img) => {
  selectedImage.value = `/storage/${img.content}`;
  selectedImageRotation.value = img.position || 0;
};

const downloadSelectedImage = () => {
  const link = document.createElement('a');
  link.href = selectedImage.value;
  link.download = selectedImage.value.split('/').pop();
  link.click();
};

const loadExistingImages = async () => {
  const woundHistoryId = props.woundHistory?.id;
  if (!woundHistoryId) return;

  try {
    const { data } = await axios.get('/media_history', {
      params: { wound_history_id: woundHistoryId }
    });
    existingImages.value = data;
  } catch (error) {
    if (error.response?.status !== 404) {
      console.error('Error al cargar imágenes:', error);
    }
  }
};

const uploadEvent = async () => {
  if (!props.woundHistory.id) {
    toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'Debe guardar la herida antes de subir imágenes.', life: 4000 });
    return;
  }

  if (!uploadFiles.value.length) {
    toast.add({ severity: 'warn', summary: 'Sin archivos', detail: 'Debes seleccionar imágenes para subir.', life: 3000 });
    return;
  }

  const formData = new FormData();
  uploadFiles.value.forEach((file, index) => {
    formData.append('images[]', file.raw, file.name);
    formData.append(`rotations[${index}]`, file.rotation || 0);
  });
  formData.append('wound_history_id', props.woundHistory.id);

  try {
    await axios.post('/media_history/upload', formData);
    toast.add({ severity: 'success', summary: 'Éxito', detail: 'Imágenes subidas.', life: 3000 });
    resetUploads();
    await loadExistingImages();
  } catch (err) {
    console.error(err);
    toast.add({ severity: 'error', summary: 'Error', detail: 'Fallo al subir.', life: 4000 });
  }
};

const confirmUpload = async () => {
  showConfirmUploadModal.value = false;
  await uploadEvent();

  resetUploads();
  if (fileUploadRef.value) {
    fileUploadRef.value.clear();
  }
};

watch(existingImages, (imgs) => {
  if (imgs.length > 0 && !selectedImage.value) {
    selectImage(imgs[0]);
  }
});
</script>
<template>
  <AppLayout title="Antecedente de la herida">
    <div class="card max-w-6xl mx-auto min-h-screen flex flex-col">
      <!-- Navegación por pasos -->
      <nav class="p-steps">
        <div class="p-step" :class="{ 'p-step-active': currentStep === 1 }" @click="currentStep = 1">
          <div class="p-step-number">1</div>
          <div class="p-step-title">Antecedente de la herida</div>
        </div>
        <div class="p-step" :class="{ 'p-step-active': currentStep === 2 }" @click="currentStep = 2">
          <div class="p-step-number">2</div>
          <div class="p-step-title">Evaluación de la herida</div>
        </div>
        <div class="p-step" :class="{ 'p-step-active': currentStep === 3 }" @click="currentStep = 3">
          <div class="p-step-number">3</div>
          <div class="p-step-title">Evidencia de la herida</div>
        </div>
      </nav>

      <!-- Formulario principal -->
      <section class="flex-grow overflow-auto border rounded p-4">
        <form @submit.prevent="saveUser" class="flex flex-col gap-6">

          <div v-show="currentStep === 1">
            <div
              class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
              <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Antecedente de la herida</h2>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                <!-- Tipo de herida -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Tipo de herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.wound_type_id" :options="props.woundsType" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione un tipo" class="w-full" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.wound_type_id,
                    }" />
                  <small v-if="submittedUser && !formWoundHistory.wound_type_id" class="text-red-500">
                    Debe seleccionar el tipo de herida.
                  </small>
                </div>

                <!-- Grado (condicional) -->
                <div v-if="formWoundHistory.wound_type_id === 8">
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Grado <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.grade_foot" :options="grades" optionLabel="name" optionValue="id"
                    placeholder="Seleccione un grado" filter class="w-full"
                    :class="{ 'p-invalid': submittedUser && !formWoundHistory.grade_foot }" />
                  <small v-if="submittedUser && !formWoundHistory.grade_foot" class="text-red-500">
                    Debe seleccionar el grado.
                  </small>
                </div>

                <!-- Subtipo de herida -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Subtipo de herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.wound_subtype_id" :options="woundSubtypes" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione un subtipo" class="w-full" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.wound_subtype_id,
                    }" />
                  <small v-if="submittedUser && !formWoundHistory.wound_subtype_id" class="text-red-500">
                    Debe seleccionar el subtipo.
                  </small>
                </div>

                <!-- Ubicación corporal -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Ubicación corporal <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.body_location_id" :options="props.bodyLocations" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.body_location_id,
                    }" />
                  <small v-if="submittedUser && !formWoundHistory.body_location_id" class="text-red-500">
                    Debe seleccionar una ubicación.
                  </small>
                </div>

                <!-- Sublocalización corporal -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Ubicación corporal secundaria
                    <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.body_sublocation_id" :options="bodySublocations" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.body_sublocation_id,
                    }" />
                  <small v-if="submittedUser && !formWoundHistory.body_sublocation_id" class="text-red-500">
                    Debe seleccionar la sublocalización.
                  </small>
                </div>

                <!-- Fase -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Fase de la herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWoundHistory.wound_phase_id" :options="props.woundsPhase" optionLabel="name"
                    placeholder="Seleccione una fase" optionValue="id" filter class="w-full" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.wound_phase_id,
                    }" />
                  <small v-if="submittedUser && !formWoundHistory.wound_phase_id" class="text-red-500">
                    Debe seleccionar la fase.
                  </small>
                </div>

                <!-- Fecha inicio -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Fecha que inició la herida <span
                      class="text-red-600">*</span></label>
                  <DatePicker v-model="formWoundHistory.woundBeginDate" class="w-full" inputId="woundBeginDate"
                    placeholder="mm/dd/yyyy" :class="{
                      'p-invalid': submittedUser && !formWoundHistory.woundBeginDate,
                    }" showIcon />
                  <small v-if="submittedUser && !formWoundHistory.woundBeginDate" class="text-red-500">
                    Debe seleccionar la fecha de inicio.
                  </small>
                </div>
              </div>

              <div class="mt-6 flex justify-end gap-2 p-10">
                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSavingUser"
                  :disabled="isSavingUser" />
              </div>
            </div>
          </div>
        </form>

        <!-- Paso 2 -->
        <div v-show="currentStep === 2">
          <div
            class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
            <form @submit.prevent="saveUser" class="flex flex-col gap-6">
              <template v-if="requiresVascular">
                <!-- Campos vasculares -->
                <div
                  class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                  <h3 class="text-xl font-semibold px-4">
                    Valoración vascular
                  </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">
                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Índice tobillo brazo
                      <span class="text-red-600">*</span>
                    </label>
                    <Select id="valoracion" v-model="formWoundHistory.valoracion" :options="valoracion" filter
                      optionLabel="name" optionValue="name" class="w-full" placeholder="Seleccione una opción" />
                    <small v-if="errors.valoracion" class="text-red-500">{{
                      errors.valoracion
                    }}</small>
                  </div>

                  <div v-if="['MESI', 'Manual'].includes(formWoundHistory.valoracion)">
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      {{ formWoundHistory.valoracion }} <span class="text-red-600">*</span>
                    </label>
                    <InputText id="MESI" v-model="formWoundHistory.MESI" class="w-full" />
                    <small v-if="errors.MESI" class="text-red-500">{{
                      errors.MESI
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      ITB izquierdo <span class="text-red-600">*</span>
                    </label>
                    <InputText id="ITB_izquierdo" v-model="formWoundHistory.ITB_izquierdo" class="w-full" />
                    <small v-if="errors.ITB_izquierdo" class="text-red-500">{{
                      errors.ITB_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      ITB derecho <span class="text-red-600">*</span>
                    </label>
                    <InputText id="ITB_derecho" v-model="formWoundHistory.ITB_derecho" class="w-full" />
                    <small v-if="errors.ITB_derecho" class="text-red-500">{{
                      errors.ITB_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso dorsal pedio izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_dorsal_izquierdo" v-model="formWoundHistory.pulse_dorsal_izquierdo"
                      class="w-full" />
                    <small v-if="errors.pulse_dorsal_izquierdo" class="text-red-500">{{
                      errors.pulse_dorsal_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso poplíteo izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_popliteo_izquierdo" v-model="formWoundHistory.pulse_popliteo_izquierdo"
                      class="w-full" />
                    <small v-if="errors.pulse_popliteo_izquierdo" class="text-red-500">{{
                      errors.pulse_popliteo_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso tibial posterior izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_tibial_izquierdo" v-model="formWoundHistory.pulse_tibial_izquierdo"
                      class="w-full" />
                    <small v-if="errors.pulse_tibial_izquierdo" class="text-red-500">{{
                      errors.pulse_tibial_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso dorsal pedio derecho
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_dorsal_derecho" v-model="formWoundHistory.pulse_dorsal_derecho"
                      class="w-full" />
                    <small v-if="errors.pulse_dorsal_derecho" class="text-red-500">{{
                      errors.pulse_dorsal_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso poplíteo derecho <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_popliteo_derecho" v-model="formWoundHistory.pulse_popliteo_derecho"
                      class="w-full" />
                    <small v-if="errors.pulse_popliteo_derecho" class="text-red-500">{{
                      errors.pulse_popliteo_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso tibial posterior derecho
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_tibial_derecho" v-model="formWoundHistory.pulse_tibial_derecho"
                      class="w-full" />
                    <small v-if="errors.pulse_tibial_derecho" class="text-red-500">{{
                      errors.pulse_tibial_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Nivel de glucosa en sangre
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="blood_glucose" v-model="formWoundHistory.blood_glucose" class="w-full" />
                    <small v-if="errors.blood_glucose" class="text-red-500">{{
                      errors.blood_glucose
                    }}</small>
                  </div>
                </div>
              </template>

              <div
                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                <h3 class="text-xl font-semibold px-4">
                  Información de la herida
                </h3>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Fecha de primera valoración
                    <span class="text-red-600">*</span>
                  </label>
                  <DatePicker v-model="formWoundHistory.woundHealthDate" inputId="woundHealthDate" class="w-full"
                    placeholder="mm/dd/yyyy" showIcon />
                  <small v-if="errors.woundHealthDate" class="text-red-500">{{
                    errors.woundHealthDate
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Edema <span class="text-red-600">*</span>
                  </label>
                  <Select id="edema" v-model="formWoundHistory.edema" :options="edema" optionLabel="name"
                    optionValue="name" class="w-full" placeholder="Seleccione una opción"
                    :class="{ 'p-invalid': !!errors.edema }" />
                  <small v-if="errors.edema" class="text-red-500">{{ errors.edema }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Dolor <span class="text-red-600">*</span>
                  </label>
                  <Select id="dolor" v-model="formWoundHistory.dolor" :options="dolor" filter optionLabel="name"
                    optionValue="name" class="w-full" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.dolor" class="text-red-500">{{
                    errors.dolor
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Tipo de dolor <span class="text-red-600">*</span>
                  </label>
                  <Select id="tipo_dolor" v-model="formWoundHistory.tipo_dolor" :options="tipo_dolor" filter
                    optionLabel="name" optionValue="name" class="w-full" placeholder="Seleccione una opción">
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
                  <InputText id="visual_scale" v-model="formWoundHistory.visual_scale" class="w-full" :class="{
                    'p-invalid': submittedUser && !formWoundHistory.visual_scale,
                  }" placeholder="Ej: 3/10" @input="onVisualScaleInput" />
                  <small v-if="errors.visual_scale" class="text-red-500">{{
                    errors.visual_scale
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Exudado (Tipo) <span class="text-red-600">*</span>
                  </label>
                  <Select id="exudado_tipo" v-model="formWoundHistory.exudado_tipo" :options="exudado_tipo" filter
                    optionLabel="name" optionValue="name" class="w-full" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.exudado_tipo" class="text-red-500">{{
                    errors.exudado_tipo
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Exudado (Cantidad) <span class="text-red-600">*</span>
                  </label>
                  <Select id="exudado_cantidad" v-model="formWoundHistory.exudado_cantidad" :options="exudado_cantidad"
                    filter optionLabel="name" optionValue="name" class="w-full" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.exudado_cantidad" class="text-red-500">{{
                    errors.exudado_cantidad
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Infección <span class="text-red-600">*</span>
                  </label>
                  <MultiSelect id="infeccion" v-model="formWoundHistory.infeccion" :options="infeccion"
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
                  <Select id="olor" v-model="formWoundHistory.olor" :options="olor" filter optionLabel="name"
                    optionValue="name" class="w-full" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.olor" class="text-red-500">{{ errors.olor }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Borde de la herida <span class="text-red-600">*</span>
                  </label>
                  <Select id="borde" v-model="formWoundHistory.borde" :options="bordes" filter optionLabel="name"
                    optionValue="name" class="w-full" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.borde" class="text-red-500">{{
                    errors.borde
                  }}</small>
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Piel perilesional <span class="text-red-600">*</span>
                  </label>
                  <MultiSelect id="piel_perilesional" v-model="formWoundHistory.piel_perilesional"
                    :options="piel_perilesional" filter optionLabel="label" optionValue="value" class="w-full"
                    placeholder="Selecciona una o más opciones" />
                  <small v-if="errors.piel_perilesional" class="text-red-500">{{
                    errors.piel_perilesional
                  }}</small>
                </div>
              </div>

              <!-- Nueva sección: Zona de la herida (dimensiones) -->
              <div
                class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
                <h3 class="text-xl font-semibold px-4">
                  Zona de la herida (dimensiones)
                </h3>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4 mb-5">
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Fecha de medición
                    <span class="text-red-600">*</span>
                  </label>
                  <DatePicker v-model="formWoundHistory.measurementDate" inputId="measurementDate" class="w-full"
                    placeholder="mm/dd/yyyy" showIcon />
                  <small v-if="errors.measurementDate" class="text-red-500">{{
                    errors.measurementDate
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)<span
                      class="text-red-600">*</span></label>
                  <InputText v-model="formWoundHistory.length" class="w-full" />
                  <small v-if="errors.length" class="text-red-500">{{
                    errors.length
                  }}</small>
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)<span
                      class="text-red-600">*</span></label>
                  <InputText v-model="formWoundHistory.width" class="w-full" />
                  <small v-if="errors.width" class="text-red-500">{{
                    errors.width
                  }}</small>
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)</label>
                  <InputText v-model="formWoundHistory.area" class="w-full" disabled />
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)<span
                      class="text-red-600">*</span></label>
                  <InputText v-model="formWoundHistory.depth" class="w-full" />
                  <small v-if="errors.depth" class="text-red-500">{{
                    errors.depth
                  }}</small>
                </div>
                <!-- Mostrar campo volumen solo si profundidad tiene valor numérico válido -->
                <div v-if="parseFloat(formWoundHistory.depth)">
                  <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)</label>
                  <InputText v-model="formWoundHistory.volume" class="w-full" disabled />
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Tunelización<span
                      class="text-red-600">*</span></label>
                  <InputText v-model="formWoundHistory.tunneling" class="w-full" />
                  <small v-if="errors.tunneling" class="text-red-500">{{
                    errors.tunneling
                  }}</small>
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento<span
                      class="text-red-600">*</span></label>
                  <InputText v-model="formWoundHistory.undermining" class="w-full" />
                  <small v-if="errors.undermining" class="text-red-500">{{
                    errors.undermining
                  }}</small>
                </div>

                <!-- Granulación -->
                <div>
                  <label for="granulation">Granulación (%)<span class="text-red-600">*</span></label>
                  <InputText id="granulation" v-model="formWoundHistory.granulation_percent" type="number" min="0"
                    max="100" step="1" @input="adjustProgress" class="w-full" />
                  <small v-if="errors.granulation_percent" class="text-red-500">{{ errors.granulation_percent }}</small>
                </div>

                <!-- Esfacelo -->
                <div>
                  <label for="slough">Esfacelo (%)<span class="text-red-600">*</span></label>
                  <InputText id="slough" v-model="formWoundHistory.slough_percent" type="number" min="0" max="100"
                    step="1" @input="adjustProgress" class="w-full" />
                  <small v-if="errors.slough_percent" class="text-red-500">{{ errors.slough_percent }}</small>
                </div>

                <!-- Necrosis -->
                <div>
                  <label for="necrosis">Necrosis (%)<span class="text-red-600">*</span></label>
                  <InputText id="necrosis" v-model="formWoundHistory.necrosis_percent" type="number" min="0" max="100"
                    step="1" @input="adjustProgress" class="w-full" />
                  <small v-if="errors.necrosis_percent" class="text-red-500">{{ errors.necrosis_percent }}</small>
                </div>

                <!-- Epitelización -->
                <div>
                  <label for="epithelialization">Epitelización (%)<span class="text-red-600">*</span></label>
                  <InputText id="epithelialization" v-model="formWoundHistory.epithelialization_percent" type="number"
                    min="0" max="100" step="1" @input="adjustProgress" class="w-full" />
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
                      :style="{ width: percentWidth('granulation_percent') + '%' }" title="Granulación"></div>

                    <!-- Amarillo: Esfacelo -->
                    <div class="absolute top-0 h-full bg-[#FFE415] transition-all duration-300"
                      :style="{ left: percentOffset('granulation_percent'), width: percentWidth('slough_percent') + '%' }"
                      title="Esfacelo"></div>

                    <!-- Negro: Necrosis -->
                    <div class="absolute top-0 h-full bg-black transition-all duration-300"
                      :style="{ left: percentOffset('granulation_percent', 'slough_percent'), width: percentWidth('necrosis_percent') + '%' }"
                      title="Necrosis"></div>

                    <!-- Azul: Epitelización -->
                    <div class="absolute top-0 h-full bg-[#F43AB6] transition-all duration-300"
                      :style="{ left: percentOffset('granulation_percent', 'slough_percent', 'necrosis_percent'), width: percentWidth('epithelialization_percent') + '%' }"
                      title="Epitelización"></div>
                  </div>

                  <!-- Texto de porcentaje total -->
                  <small class="block text-sm mt-1 text-gray-600">
                    Total: {{ totalPercentage }}%
                  </small>

                  <small v-if="totalPercentage > 100" class="text-red-500 text-sm">
                    ⚠️ La suma no debe exceder el 100%
                  </small>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-6 px-4 mt-6">
                <div class="col-span-3">
                  <label for="description" class="flex items-center gap-1 mb-1 font-medium">
                    Describir tratamiento actual de la herida
                  </label>
                  <Editor v-model="formWoundHistory.description" editorStyle="height: 150px" class="w-full" />
                  <small v-if="errors.description" class="text-red-500">{{ errors.description }}</small>
                </div>
              </div>

              <div class="mt-6 flex justify-end gap-2 p-10">
                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSavingUser"
                  :disabled="isSavingUser" />
              </div>
            </form>
          </div>
        </div>

        <!-- Paso 3 -->
        <div v-show="currentStep === 3">
          <div
            class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
            <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Evidencia de la herida</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6 px-4 mb-5">
              <div class="card">
                <Toast />
                <FileUpload ref="fileUploadRef" name="images[]" :customUpload="true" :multiple="true"
                  :maxFileSize="9000000" accept="image/*" @select="onSelectedFiles" @uploader="uploadEvent">
                  <template #header="{ chooseCallback, clearCallback }">
                    <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                      <div class="flex gap-2">
                        <Button @click="chooseCallback()" icon="pi pi-images" rounded outlined severity="secondary" />
                        <Button @click="showConfirmUploadModal = true" icon="pi pi-cloud-upload" rounded outlined
                          severity="success" :disabled="!uploadFiles.length" />
                        <Button @click="() => clearTemplatedUpload(clearCallback)" icon="pi pi-times" rounded outlined
                          severity="danger" :disabled="!uploadFiles.length" />
                      </div>
                      <ProgressBar :value="totalSizePercent" :showValue="false"
                        class="md:w-20rem h-1 w-full md:ml-auto">
                        <span class="whitespace-nowrap">
                          {{ (totalSize / 1024 / 1024).toFixed(2) }}MB / 9MB
                        </span>
                      </ProgressBar>
                    </div>
                  </template>


                  <template #content>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                      <div v-for="(file, index) in uploadFiles" :key="file.name + file.size"
                        class="p-4 rounded border border-surface flex flex-col items-center gap-4">
                        <img :src="file.objectURL" :style="getImageStyle(file.rotation)"
                          class="w-full h-auto max-h-[20rem] object-contain transition-transform duration-300 cursor-zoom-in"
                          alt="imagen" @click="openZoomModal(file.objectURL, file.rotation)" />
                        <div class="flex gap-2">
                          <Button icon="pi pi-replay" @click="rotateImage(index, 'left')" rounded outlined />
                          <Button icon="pi pi-refresh" @click="rotateImage(index, 'right')" rounded outlined />
                        </div>
                        <span class="text-sm text-center">{{ file.name }}</span>
                        <Badge value="Pendiente" severity="warn" />
                        <Button icon="pi pi-times" @click="removeFile(index)" rounded outlined severity="danger" />
                      </div>
                    </div>
                  </template>
                </FileUpload>
              </div>

              <Dialog v-model:visible="showConfirmUploadModal" modal header="Evidencia de la herida"
                :style="{ width: '400px' }">
                <div class="text-center p-4">
                  <p class="mb-4">¿Desea subir las imágenes seleccionadas?</p>
                  <div class="flex justify-center gap-3">
                    <Button label="Cancelar" icon="pi pi-times" severity="secondary"
                      @click="showConfirmUploadModal = false" />
                    <Button label="Confirmar" icon="pi pi-check" severity="success" @click="confirmUpload" autofocus />
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
              <Dialog v-model:visible="showLimitModal" modal header="Límite de imágenes" :style="{ width: '400px' }">
                <div class="text-center p-4">
                  <p class="mb-4">Solo puedes subir un máximo de <strong>4 imágenes</strong>.</p>
                  <Button label="Entendido" icon="pi pi-check" @click="closeLimitModal" autofocus />
                </div>
              </Dialog>

              <!-- Galería -->
              <div v-if="existingImages.length > 0" class="flex flex-col lg:flex-row gap-8">
                <div class="flex-1 flex flex-col lg:flex-row gap-8">
                  <div
                    class="flex-1 p-2 px-5 bg-surface-0 dark:bg-surface-900 shadow-sm overflow-hidden rounded-xl flex flex-col gap-10">
                    <img :src="selectedImage" class="w-full flex-1 max-h-[40rem] rounded-lg object-cover cursor-zoom-in"
                      alt="Imagen principal" @click="openZoomModal(selectedImage, selectedImageRotation)" />

                    <div
                      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-10 gap-y-6 pl-6 pr-15">

                      <div v-for="img in existingImages" :key="img.id" class="relative group">
                        <img :src="`/storage/${img.content}`" :style="getImageStyle(img.position || 0)"
                          class="w-full h-auto min-h-20 rounded-lg object-cover cursor-pointer transition-all duration-150"
                          :class="{
                            'shadow-[0_0_0_2px] shadow-surface-900 dark:shadow-surface-0':
                              selectedImage === `/storage/${img.content}`,
                          }" @click="selectImage(img)" alt="Miniatura" />

                        <!-- Botón borrar por miniatura -->
                        <button type="button"
                          class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-red-600 text-white rounded-md px-2 py-1 text-xs shadow"
                          @click.stop="openConfirmDeleteByThumb(img)">
                          Eliminar
                        </button>
                      </div>
                    </div>

                    <Button label="Descargar" class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md" rounded
                      outlined @click="downloadSelectedImage" />
                    <Button label="Eliminar" severity="danger"
                      class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md mt-3" :disabled="!selectedImage"
                      @click="openConfirmDeleteSelected" />

                  </div>
                </div>
              </div>
            </div>
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
</style>
