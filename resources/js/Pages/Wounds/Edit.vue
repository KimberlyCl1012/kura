<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { ref, watch, onMounted, computed } from "vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Button from "primevue/button";
import { Badge, DatePicker, ProgressBar, usePrimeVue } from "primevue";
import Editor from "primevue/editor";
import FileUpload from 'primevue/fileupload';
import Dialog from 'primevue/dialog';

// Props
const props = defineProps({
  woundsType: Array,
  woundsSubtype: Array,
  woundsPhase: Array,
  bodyLocations: Array,
  bodySublocation: Array,
  wound: Object,
  healthRecordId: String,
  measurement: Object,
  treatmentMethods: Array,
  treatmentSubmethods: Array,
  treatment: Object,
});

// UI y estado
const toast = useToast();
const currentStep = ref(1);
const isSavingUser = ref(false);
const isSavingMeasurement = ref(false);
const submittedUser = ref(false);
const submittedMeasurement = ref(false);
const errors = ref({});

// Catálogos
const bordes = ref([{ name: "Adherido" }, { name: "No adherido" }, { name: "Enrollado" }, { name: "Epitalizado" }]);
const valoracion = ref([{ name: "MESI" }, { name: "No aplica" }]);
const edema = ref([{ name: "+++" }, { name: "++" }, { name: "+" }, { name: "No aplica" }]);
const dolor = ref([{ name: "En reposo" }, { name: "Con movimiento" }, { name: "Ninguno" }]);
const exudado_cantidad = ref([{ name: "Abundante" }, { name: "Moderado" }, { name: "Bajo" }]);
const exudado_tipo = ref([{ name: "Seroso" }, { name: "Purulento" }, { name: "Hemático" }, { name: "Serohemático" }]);
const olor = ref([{ name: "Mal olor" }, { name: "No aplica" }]);
const grades = ref([{ id: 1, name: "1" }, { id: 2, name: "2" }, { id: 3, name: "3" }]);
const piel_perisional = ref([
  { name: "Eritema" }, { name: "Escoriación" }, { name: "Maceración" }, { name: "Reseca" },
  { name: "Equimosis" }, { name: "Indurada" }, { name: "Queratosis" }, { name: "Integra" },
  { name: "Hiperpigmentada" }
]);
const infeccion = ref([
  { name: "Celulitis" }, { name: "Pirexia" }, { name: "Aumento del dolor" },
  { name: "Rapida extensión del area ulcerada" }, { name: "Mal olor" }, { name: "Incremento del exudado" },
  { name: "Eritema" }, { name: "No aplica" }
]);
const tipo_dolor = ref([{ name: "Nociceptivo" }, { name: "Neuropático" }]);

// Formulario principal
const formWound = ref({
  wound_type_id: null,
  grade_foot: null,
  wound_subtype_id: null,
  wound_type_other: "",
  body_location_id: null,
  body_sublocation_id: null,
  wound_phase_id: null,
  woundBeginDate: null,
  woundHealthDate: null,
  woundBackground: "",
  MESI: "",
  borde: null,
  edema: null,
  dolor: null,
  exudado_cantidad: null,
  exudado_tipo: null,
  olor: null,
  piel_perisional: null,
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
});

async function saveUser() {
  submittedUser.value = true;
  isSavingUser.value = true;
  errors.value = {};

  const woundId = formWound.value.id;

  const payload = {
    ...formWound.value,
    woundBeginDate: formWound.value.woundBeginDate
      ? new Date(formWound.value.woundBeginDate).toISOString().slice(0, 10)
      : null,
    woundHealthDate: formWound.value.woundHealthDate
      ? new Date(formWound.value.woundHealthDate).toISOString().slice(0, 10)
      : null,
  };

  try {
    let response;

    if (woundId) {
      response = await axios.put(`/wounds/${woundId}`, payload);
    } else {
      response = await axios.post("/wounds", payload);
      formWound.value.id = response.data.id;
    }

    toast.add({
      severity: "success",
      summary: "Guardado",
      detail: "Datos de la herida guardados correctamente.",
      life: 3000,
    });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    toast.add({
      severity: "error",
      summary: "Error",
      detail: error.response?.data?.message || "No se pudo guardar la herida.",
      life: 5000,
    });
  } finally {
    isSavingUser.value = false;
  }
}

// Carga de datos iniciales
const woundSubtypes = ref([]);
const bodySublocations = ref([]);
const isInitialLoadType = ref(true);
const isInitialLoadLocation = ref(true);

onMounted(() => {
  if (props.wound) {
    Object.assign(formWound.value, {
      ...props.wound,
      id: props.wound.id || props.wound.wound_id,
      woundBeginDate: props.wound.woundBeginDate ? new Date(props.wound.woundBeginDate) : null,
      woundHealthDate: props.wound.woundHealthDate ? new Date(props.wound.woundHealthDate) : null,
      grade_foot: props.wound.grade_foot ? parseInt(props.wound.grade_foot) : null,
    });
    if (formWound.value.wound_type_id) loadSubtypes(formWound.value.wound_type_id);
    if (formWound.value.body_location_id) loadSublocations(formWound.value.body_location_id);
  }

  if (props.measurement) {
    Object.assign(formWound.value, {
      measurementDate: new Date(props.measurement.measurementDate),
      length: props.measurement.length,
      width: props.measurement.width,
      area: props.measurement.area,
      depth: props.measurement.depth,
      volume: props.measurement.volume,
      tunneling: props.measurement.tunneling,
      undermining: props.measurement.undermining,
      granulation_percent: props.measurement.granulation_percent ?? props.measurement.redPercentaje,
      slough_percent: props.measurement.slough_percent ?? props.measurement.yellowPercentaje,
      necrosis_percent: props.measurement.necrosis_percent ?? props.measurement.blackPercentaje,
      epithelialization_percent: props.measurement.epithelialization_percent,
    });
  }


});

// Cargar subtipos y sublocalizaciones
async function loadSubtypes(typeId) {
  try {
    const { data } = await axios.get(`/wound_types/${typeId}/subtypes`);
    woundSubtypes.value = data;
  } catch {
    toast.add({ severity: "error", summary: "Error", detail: "No se pudieron cargar los subtipos.", life: 5000 });
  }
}
async function loadSublocations(locationId) {
  try {
    const { data } = await axios.get(`/body_locations/${locationId}/sublocations`);
    bodySublocations.value = data;
  } catch {
    toast.add({ severity: "error", summary: "Error", detail: "No se pudieron cargar las sublocalizaciones.", life: 5000 });
  }
}

// Watchers de subtipos y sublocalización
watch(() => formWound.value.wound_type_id, (newVal) => {
  if (isInitialLoadType.value) return isInitialLoadType.value = false;
  formWound.value.wound_subtype_id = null;
  woundSubtypes.value = [];
  if (newVal) loadSubtypes(newVal);
});
watch(() => formWound.value.body_location_id, (newVal) => {
  if (isInitialLoadLocation.value) return isInitialLoadLocation.value = false;
  formWound.value.body_sublocation_id = null;
  bodySublocations.value = [];
  if (newVal) loadSublocations(newVal);
});

// Área y volumen automáticos
watch(() => [formWound.value.length, formWound.value.width], ([length, width]) => {
  const l = parseFloat(length);
  const w = parseFloat(width);
  if (!isNaN(l) && !isNaN(w)) {
    formWound.value.area = (l * w).toFixed(2);
  } else {
    formWound.value.area = '';
  }
  if (formWound.value.depth) {
    const d = parseFloat(formWound.value.depth);
    if (!isNaN(d)) {
      formWound.value.volume = (l * w * d).toFixed(2);
    }
  }
});

watch(() => formWound.value.depth, (depth) => {
  const d = parseFloat(depth);
  const a = parseFloat(formWound.value.area);
  formWound.value.volume = (!isNaN(d) && !isNaN(a)) ? (a * d).toFixed(2) : '';
});

// Validación de escala visual
function onVisualScaleInput(event) {
  const input = event.target.value.replace("/10", "");
  let number = parseInt(input);
  if (!isNaN(number)) {
    if (number < 1) number = 1;
    if (number > 10) number = 10;
    formWound.value.visual_scale = `${number}/10`;
  } else {
    formWound.value.visual_scale = "";
  }
}

// Guardado de dimensiones
async function saveMeasurement() {
  submittedMeasurement.value = true;
  isSavingMeasurement.value = true;
  errors.value = {};

  const requiredFields = [
    'measurementDate', 'length', 'width', 'tunneling', 'undermining',
    'granulation_percent', 'slough_percent', 'necrosis_percent', 'epithelialization_percent',
  ];

  const fieldLabels = {
    measurementDate: 'Fecha de medición',
    length: 'Longitud',
    width: 'Anchura',
    area: 'Área',
    depth: 'Profundidad',
    volume: 'Volumen',
    tunneling: 'Tunelización',
    undermining: 'Socavamiento',
    granulation_percent: 'Tejido de granulación (%)',
    slough_percent: 'Tejido de esfácelo (%)',
    necrosis_percent: 'Necrosis (%)',
    epithelialization_percent: 'Epitelización (%)'
  };

  const missingFields = requiredFields.filter(
    (field) => !formWound.value[field] && formWound.value[field] !== 0
  );

  if (missingFields.length > 0) {
    const firstField = missingFields[0];
    missingFields.forEach((field) => {
      errors.value[field] = `El campo ${fieldLabels[field]} es requerido`;
    });
    toast.add({
      severity: 'error',
      summary: 'Campo requerido',
      detail: `El campo ${fieldLabels[firstField]} es requerido.`,
      life: 4000,
    });
    isSavingMeasurement.value = false;
    return;
  }

  const numericFields = [
    'length', 'width', 'tunneling', 'undermining',
    'granulation_percent', 'slough_percent', 'necrosis_percent', 'epithelialization_percent'
  ];
  let firstNumericErrorField = null;

  numericFields.forEach((field) => {
    const value = formWound.value[field];
    if (value !== '' && isNaN(parseFloat(value))) {
      errors.value[field] = `El campo ${fieldLabels[field]} debe ser un número válido.`;
      if (!firstNumericErrorField) firstNumericErrorField = field;
    }
  });

  if (firstNumericErrorField) {
    toast.add({
      severity: 'error',
      summary: 'Error de validación',
      detail: `El campo ${fieldLabels[firstNumericErrorField]} debe ser un número válido.`,
      life: 4000,
    });
    isSavingMeasurement.value = false;
    return;
  }
  // Guardar measurements
  try {
    const payload = {
      wound_id: formWound.value.id,
      appointment_id: formWound.value.appointment_id,
      measurementDate: formWound.value.measurementDate
        ? new Date(formWound.value.measurementDate).toISOString().slice(0, 10)
        : null,
      length: formWound.value.length,
      width: formWound.value.width,
      area: formWound.value.area,
      depth: formWound.value.depth,
      volume: formWound.value.volume,
      tunneling: formWound.value.tunneling,
      undermining: formWound.value.undermining,
      granulation_percent: formWound.value.granulation_percent,
      slough_percent: formWound.value.slough_percent,
      necrosis_percent: formWound.value.necrosis_percent,
      epithelialization_percent: formWound.value.epithelialization_percent,
    };

    await axios.post("/measurements", payload);

    toast.add({
      severity: "success",
      summary: "Dimensiones guardadas",
      detail: "Medición registrada",
      life: 3000,
    });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    toast.add({
      severity: "error",
      summary: "Error",
      detail: error.response?.data?.message || "No se pudo guardar la medición",
      life: 5000,
    });
  } finally {
    isSavingMeasurement.value = false;
  }
}

function goToStep(step) {
  currentStep.value = step;
}

const showVascularFields = computed(() => {
  return formWound.value.body_location_id === 3 || formWound.value.body_location_id === 5;
});

// Actualiza la barra de colores
function adjustProgress() { }

const totalPercentage = computed(() => {
  const g = parseFloat(formWound.value.granulation_percent) || 0;
  const s = parseFloat(formWound.value.slough_percent) || 0;
  const n = parseFloat(formWound.value.necrosis_percent) || 0;
  const e = parseFloat(formWound.value.epithelialization_percent) || 0;
  return g + s + n + e;
});

function percentWidth(field) {
  const value = parseFloat(formWound.value[field]) || 0;
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
    const val = parseFloat(formWound.value[field]) || 0;
    sum += val;
  }
  if (total > 100) {
    return ((sum / total) * 100).toFixed(2) + '%';
  }
  return sum + '%';
}

//Evidencia de la herida
const MAX_FILES = 4

const zoomImageUrl = ref('')
const zoomRotation = ref(0)
const showZoomModal = ref(false)
const showLimitModal = ref(false)

const uploadFiles = ref([])
const totalSize = ref(0)
const totalSizePercent = ref(0)

const existingImages = ref([])
const selectedImage = ref('')
const selectedImageRotation = ref(0)
// funciones auxiliares
const getImageStyle = (rotation) => ({ transform: `rotate(${rotation}deg)` })

const updateTotalSize = () => {
  totalSize.value = uploadFiles.value.reduce((acc, f) => acc + f.size, 0)
  totalSizePercent.value = totalSize.value / 10
}

const resetUploads = () => {
  uploadFiles.value = []
  totalSize.value = 0
  totalSizePercent.value = 0
}

const isLimitReached = (incomingCount) =>
  existingImages.value.length + uploadFiles.value.length + incomingCount > MAX_FILES

const openZoomModal = (src, rotation) => {
  zoomImageUrl.value = src
  zoomRotation.value = rotation
  showZoomModal.value = true
}

const closeLimitModal = () => {
  showLimitModal.value = false
}

const onSelectedFiles = (event) => {
  const incoming = event.files
  if (isLimitReached(incoming.length)) {
    showLimitModal.value = true
    return
  }

  incoming.forEach(file => {
    uploadFiles.value.push({
      raw: file,
      name: file.name,
      size: file.size,
      type: file.type,
      objectURL: URL.createObjectURL(file),
      rotation: 0,
    })
  })

  updateTotalSize()
}

const rotateImage = (index, direction) => {
  const file = uploadFiles.value[index]
  file.rotation = direction === 'left'
    ? (file.rotation - 5 + 360) % 360
    : (file.rotation + 5) % 360
}

const removeFile = (index) => {
  uploadFiles.value.splice(index, 1)
  updateTotalSize()
}

const clearTemplatedUpload = (clear) => {
  clear()
  resetUploads()
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

const loadExistingImages = async () => {
  const woundId = props.wound.id
  if (!woundId) return

  try {
    const { data } = await axios.get('/media', {
      params: { wound_id: woundId }
    })
    existingImages.value = data
  } catch (error) {
    if (error.response?.status !== 404) {
      console.error('Error al cargar imágenes:', error)
    }
  }
}

const uploadEvent = async () => {
  if (!props.wound.id) {
    toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'Debe guardar la herida antes de subir imágenes.', life: 4000 })
    return
  }

  if (!uploadFiles.value.length) {
    toast.add({ severity: 'warn', summary: 'Sin archivos', detail: 'Debes seleccionar imágenes para subir.', life: 3000 })
    return
  }

  const formData = new FormData()
  uploadFiles.value.forEach((file, index) => {
    formData.append('images[]', file.raw, file.name)
    formData.append(`rotations[${index}]`, file.rotation || 0)
  })
  formData.append('wound_id', props.wound.id)

  try {
    await axios.post('/media/upload', formData)
    toast.add({ severity: 'success', summary: 'Éxito', detail: 'Imágenes subidas.', life: 3000 })
    resetUploads()
    await loadExistingImages()
  } catch (err) {
    console.error(err)
    toast.add({ severity: 'error', summary: 'Error', detail: 'Fallo al subir.', life: 4000 })
  }
}

watch(existingImages, (imgs) => {
  if (imgs.length > 0 && !selectedImage.value) {
    selectImage(imgs[0])
  }
})

// carga inicial de imágenes
loadExistingImages()

const fileUploadClearCallback = ref(null)

const fileUploadRef = ref(null)

//Confirmación de carga de imagenes
const showConfirmUploadModal = ref(false)

const confirmUpload = async () => {
  showConfirmUploadModal.value = false
  await uploadEvent()

  // Limpiar componente y estado después de subida exitosa
  resetUploads()
  if (fileUploadRef.value) {
    fileUploadRef.value.clear()
  }
}

//Tratamiento 
const hasTreatment = ref(!!props.treatment);


const formTreat = ref({
  methods: [],
  submethodsByMethod: {},
  description: "",
});

const isSavingTreatment = ref(false);
const submittedTreatment = ref(false);

// Computed: filtrar métodos seleccionados
const selectedMethodsWithSubmethods = computed(() => {
  return props.treatmentMethods.filter(method =>
    formTreat.value.methods.includes(method.id)
  );
});

const storeTreatment = async () => {
  submittedTreatment.value = true;
  errors.value = {};

  if (!formWound.value.id) {
    toast.add({
      severity: "warn",
      summary: "Advertencia",
      detail: "Debe guardar la herida antes.",
      life: 4000,
    });
    return;
  }

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
    wound_id: formWound.value.id,
    description: formTreat.value.description || null,
    method_ids: formTreat.value.methods,
    submethodsByMethod: formTreat.value.submethodsByMethod,
  };

  try {
    await axios.post("/treatments", payload);
    toast.add({
      severity: "success",
      summary: "Éxito",
      detail: "Tratamiento guardado.",
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
  if (props.treatment) {
    formTreat.value.description = props.treatment.description;
    formTreat.value.methods = props.treatment.methods.map(m => m.treatment_method_id);

    const map = {};
    props.treatment.submethods.forEach(sub => {
      const methodId = sub.treatment_method_id;
      if (methodId) {
        map[methodId] = map[methodId] || [];
        map[methodId].push(sub.treatment_submethod_id);
      }
    });
    formTreat.value.submethodsByMethod = map;

  }
});

//Terminar consulta
watch(() => props.treatment, (val) => {
  hasTreatment.value = !!val;
});

const finishConsultation = async () => {
  isSavingTreatment.value = true;

  try {
    const response = await axios.put('/appointments/finish', {
      wound_id: props.wound.id,
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

    if (error.response?.data?.error) {
      console.error('Detalles del error:', error.response.data.error);
    }
  } finally {
    isSavingTreatment.value = false;
  }
};

</script>

<template>
  <AppLayout title="Heridas">
    <div class="card max-w-6xl mx-auto min-h-screen flex flex-col">
      <!-- Navegación -->
      <nav class="p-steps" aria-label="Steps" role="list">
        <div class="p-step" :class="{ 'p-step-active': currentStep === 1 }" role="listitem" @click="goToStep(1)">
          <div class="p-step-number">1</div>
          <div class="p-step-title">Datos de la herida</div>
        </div>
        <div class="p-step" :class="{ 'p-step-active': currentStep === 2 }" role="listitem" @click="goToStep(2)">
          <div class="p-step-number">2</div>
          <div class="p-step-title">Evaluación de la herida</div>
        </div>
        <div class="p-step" :class="{ 'p-step-active': currentStep === 3 }" role="listitem" @click="goToStep(3)">
          <div class="p-step-number">3</div>
          <div class="p-step-title">Abordaje de la herida</div>
        </div>
      </nav>

      <!-- Contenido -->
      <section class="flex-grow overflow-auto border rounded p-4">
        <div v-show="currentStep === 1">
          <div
            class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
            <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Datos de la herida</h2>

            <form @submit.prevent="saveUser" class="flex flex-col flex-grow overflow-auto">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
                <!-- Tipo de herida -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Tipo de herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.wound_type_id" :options="props.woundsType" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione un tipo" class="w-full min-w-0" :class="{
                      'p-invalid': submittedUser && !formWound.wound_type_id,
                    }" />
                  <small v-if="submittedUser && !formWound.wound_type_id" class="text-red-500">
                    Debe seleccionar el tipo de herida.
                  </small>
                </div>

                <!-- Grado (condicional) -->
                <!-- Subtipo o Grado (según tipo de herida) -->
                <div v-if="formWound.wound_type_id === 8">
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Grado <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.grade_foot" :options="grades" optionLabel="name" optionValue="id"
                    placeholder="Seleccione un grado" filter class="w-full min-w-0"
                    :class="{ 'p-invalid': submittedUser && !formWound.grade_foot }" />
                  <small v-if="submittedUser && !formWound.grade_foot" class="text-red-500">
                    Debe seleccionar el grado.
                  </small>
                </div>

                <!-- Otro tipo (condicional) -->
                <div v-if="
                  formWound.wound_type_id === 9 ||
                  [7, 11, 25, 33, 46].includes(formWound.wound_subtype_id)
                ">
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Indicar tipo de herida <span class="text-red-600">*</span>
                  </label>
                  <InputText v-model="formWound.wound_type_other" class="w-full min-w-0" :class="{
                    'p-invalid': submittedUser && !formWound.wound_type_other,
                  }" />
                  <small v-if="submittedUser && !formWound.wound_type_other" class="text-red-500">
                    Debe especificar otro tipo de herida.
                  </small>
                </div>

                <!-- Subtipo de herida -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Subtipo de herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.wound_subtype_id" :options="woundSubtypes" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione un subtipo" class="w-full min-w-0" :class="{
                      'p-invalid': submittedUser && !formWound.wound_subtype_id,
                    }" />
                  <small v-if="submittedUser && !formWound.wound_subtype_id" class="text-red-500">
                    Debe seleccionar el subtipo.
                  </small>
                </div>

                <!-- Ubicación corporal -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Ubicación corporal <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.body_location_id" :options="props.bodyLocations" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full min-w-0" :class="{
                      'p-invalid': submittedUser && !formWound.body_location_id,
                    }" />
                  <small v-if="submittedUser && !formWound.body_location_id" class="text-red-500">
                    Debe seleccionar una ubicación.
                  </small>
                </div>

                <!-- Sublocalización corporal -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Ubicación corporal secundaria
                    <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.body_sublocation_id" :options="bodySublocations" optionLabel="name"
                    optionValue="id" filter placeholder="Seleccione una ubicación" class="w-full min-w-0" :class="{
                      'p-invalid': submittedUser && !formWound.body_sublocation_id,
                    }" />
                  <small v-if="submittedUser && !formWound.body_sublocation_id" class="text-red-500">
                    Debe seleccionar la sublocalización.
                  </small>
                </div>

                <!-- Fase -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Fase de la herida <span class="text-red-600">*</span>
                  </label>
                  <Select v-model="formWound.wound_phase_id" :options="props.woundsPhase" optionLabel="name"
                    placeholder="Seleccione una fase" optionValue="id" filter class="w-full min-w-0" :class="{
                      'p-invalid': submittedUser && !formWound.wound_phase_id,
                    }" />
                  <small v-if="submittedUser && !formWound.wound_phase_id" class="text-red-500">
                    Debe seleccionar la fase.
                  </small>
                </div>

                <!-- Fecha inicio -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Fecha que inició la herida <span
                      class="text-red-600">*</span></label>
                  <DatePicker v-model="formWound.woundBeginDate" class="w-full min-w-0" inputId="woundBeginDate"
                    placeholder="Seleccione una fecha" showIcon />
                  <small v-if="submittedUser && !formWound.woundBeginDate" class="text-red-500">
                    Debe seleccionar la fecha de inicio.
                  </small>
                </div>
              </div>

              <div class="mt-6 flex justify-end gap-2 p-10">
                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSavingUser"
                  :disabled="isSavingUser" />
              </div>
            </form>
          </div>
        </div>
        <div v-show="currentStep === 2">
          <div
            class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
            <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Evaluación de la herida</h2>

            <form @submit.prevent="saveUser" class="flex flex-col flex-grow overflow-auto">
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
                  ].includes(formWound.body_location_id)
                ">
                  <!-- Campos vasculares -->
                  <div class="col-span-full">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">
                      Valoración vascular (solo aplica en heridas en pierna)
                    </h3>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Índice tobillo brazo Manual
                      <span class="text-red-600">*</span>
                    </label>
                    <Select id="valoracion" v-model="formWound.valoracion" :options="valoracion" filter
                      optionLabel="name" optionValue="name" class="w-full min-w-0"
                      placeholder="Seleccione una opción" />
                    <small v-if="errors.valoracion" class="text-red-500">{{
                      errors.valoracion
                    }}</small>
                  </div>

                  <div v-if="['MESI'].includes(formWound.valoracion)">
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      MESI <span class="text-red-600">*</span>
                    </label>
                    <InputText id="MESI" v-model="formWound.MESI" class="w-full min-w-0" />
                    <small v-if="errors.MESI" class="text-red-500">{{
                      errors.MESI
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      ITB izquierdo <span class="text-red-600">*</span>
                    </label>
                    <InputText id="ITB_izquierdo" v-model="formWound.ITB_izquierdo" class="w-full min-w-0" />
                    <small v-if="errors.ITB_izquierdo" class="text-red-500">{{
                      errors.ITB_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      ITB derecho <span class="text-red-600">*</span>
                    </label>
                    <InputText id="ITB_derecho" v-model="formWound.ITB_derecho" class="w-full min-w-0" />
                    <small v-if="errors.ITB_derecho" class="text-red-500">{{
                      errors.ITB_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso dorsal pedio izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_dorsal_izquierdo" v-model="formWound.pulse_dorsal_izquierdo"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_dorsal_izquierdo" class="text-red-500">{{
                      errors.pulse_dorsal_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso poplíteo izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_popliteo_izquierdo" v-model="formWound.pulse_popliteo_izquierdo"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_popliteo_izquierdo" class="text-red-500">{{
                      errors.pulse_popliteo_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso tibial posterior izquierdo
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_tibial_izquierdo" v-model="formWound.pulse_tibial_izquierdo"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_tibial_izquierdo" class="text-red-500">{{
                      errors.pulse_tibial_izquierdo
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso dorsal pedio derecho
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_dorsal_derecho" v-model="formWound.pulse_dorsal_derecho"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_dorsal_derecho" class="text-red-500">{{
                      errors.pulse_dorsal_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso poplíteo derecho <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_popliteo_derecho" v-model="formWound.pulse_popliteo_derecho"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_popliteo_derecho" class="text-red-500">{{
                      errors.pulse_popliteo_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Pulso tibial posterior derecho
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="pulse_tibial_derecho" v-model="formWound.pulse_tibial_derecho"
                      class="w-full min-w-0" />
                    <small v-if="errors.pulse_tibial_derecho" class="text-red-500">{{
                      errors.pulse_tibial_derecho
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Monofilamento <span class="text-red-600">*</span>
                    </label>
                    <InputText id="monofilamento" v-model="formWound.monofilamento" class="w-full min-w-0" />
                    <small v-if="errors.monofilamento" class="text-red-500">{{
                      errors.monofilamento
                    }}</small>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Nivel de glucosa en sangre
                      <span class="text-red-600">*</span>
                    </label>
                    <InputText id="blood_glucose" v-model="formWound.blood_glucose" class="w-full min-w-0" />
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
                    Fecha de primera valoración
                    <span class="text-red-600">*</span>
                  </label>
                  <DatePicker v-model="formWound.woundHealthDate" inputId="woundHealthDate" class="w-full min-w-0"
                    placeholder="Seleccione una fecha" showIcon />
                  <small v-if="errors.woundHealthDate" class="text-red-500">{{
                    errors.woundHealthDate
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Edema <span class="text-red-600">*</span>
                  </label>
                  <Select id="edema" v-model="formWound.edema" :options="edema" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.edema" class="text-red-500">{{
                    errors.edema
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Dolor <span class="text-red-600">*</span>
                  </label>
                  <Select id="dolor" v-model="formWound.dolor" :options="dolor" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.dolor" class="text-red-500">{{
                    errors.dolor
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Tipo de dolor <span class="text-red-600">*</span>
                  </label>
                  <Select id="tipo_dolor" v-model="formWound.tipo_dolor" :options="tipo_dolor" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
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
                  <InputText id="visual_scale" v-model="formWound.visual_scale" class="w-full min-w-0" :class="{
                    'p-invalid': submittedUser && !formWound.visual_scale,
                  }" placeholder="Ej: 3/10" @input="onVisualScaleInput" />
                  <small v-if="errors.visual_scale" class="text-red-500">{{
                    errors.visual_scale
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Exudado (Tipo) <span class="text-red-600">*</span>
                  </label>
                  <Select id="exudado_tipo" v-model="formWound.exudado_tipo" :options="exudado_tipo" filter
                    optionLabel="name" optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.exudado_tipo" class="text-red-500">{{
                    errors.exudado_tipo
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Exudado (Cantidad) <span class="text-red-600">*</span>
                  </label>
                  <Select id="exudado_cantidad" v-model="formWound.exudado_cantidad" :options="exudado_cantidad" filter
                    optionLabel="name" optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.exudado_cantidad" class="text-red-500">{{
                    errors.exudado_cantidad
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Infección <span class="text-red-600">*</span>
                  </label>
                  <Select id="infeccion" v-model="formWound.infeccion" :options="infeccion" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.infeccion" class="text-red-500">{{
                    errors.infeccion
                  }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Olor <span class="text-red-600">*</span>
                  </label>
                  <Select id="olor" v-model="formWound.olor" :options="olor" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.olor" class="text-red-500">{{ errors.olor }}</small>
                </div>

                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Borde de la herida <span class="text-red-600">*</span>
                  </label>
                  <Select id="borde" v-model="formWound.borde" :options="bordes" filter optionLabel="name"
                    optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.borde" class="text-red-500">{{
                    errors.borde
                  }}</small>
                </div>
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    Piel perisional <span class="text-red-600">*</span>
                  </label>
                  <Select id="piel_perisional" v-model="formWound.piel_perisional" :options="piel_perisional" filter
                    optionLabel="name" optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.piel_perisional" class="text-red-500">{{
                    errors.piel_perisional
                  }}</small>
                </div>
              </div>

              <!-- Botón para guardar evaluación -->
              <div class="mt-6 flex justify-end gap-2 p-10">
                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSavingUser"
                  :disabled="isSavingUser" />
              </div>
            </form>

            <!-- Nueva sección: Zona de la herida (dimensiones) -->
            <div class="col-span-full mt-10">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">
                Zona de la herida (dimensiones)
              </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 px-4">
              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">
                  Fecha de medición
                  <span class="text-red-600">*</span>
                </label>
                <DatePicker v-model="formWound.measurementDate" inputId="measurementDate" class="w-full min-w-0"
                  placeholder="Seleccione una fecha" showIcon />
                <small v-if="errors.measurementDate" class="text-red-500">{{
                  errors.measurementDate
                }}</small>
              </div>

              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Longitud (cm)<span
                    class="text-red-600">*</span></label>
                <InputText v-model="formWound.length" class="w-full min-w-0" />
                <small v-if="errors.length" class="text-red-500">{{
                  errors.length
                }}</small>
              </div>
              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Anchura (cm)<span
                    class="text-red-600">*</span></label>
                <InputText v-model="formWound.width" class="w-full min-w-0" />
                <small v-if="errors.width" class="text-red-500">{{
                  errors.width
                }}</small>
              </div>
              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Área (cm²)</label>
                <InputText v-model="formWound.area" class="w-full min-w-0" disabled />
              </div>
              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)<span
                    class="text-red-600">*</span></label>
                <InputText v-model="formWound.depth" class="w-full min-w-0" />
                <small v-if="errors.depth" class="text-red-500">{{
                  errors.depth
                }}</small>
              </div>
              <!-- Mostrar campo volumen solo si profundidad tiene valor numérico válido -->
              <div v-if="parseFloat(formWound.depth)">
                <label class="flex items-center gap-1 mb-1 font-medium">Volumen (cm³)</label>
                <InputText v-model="formWound.volume" class="w-full min-w-0" disabled />
              </div>

              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Tunelización<span
                    class="text-red-600">*</span></label>
                <InputText v-model="formWound.tunneling" class="w-full min-w-0" />
                <small v-if="errors.tunneling" class="text-red-500">{{
                  errors.tunneling
                }}</small>
              </div>
              <div>
                <label class="flex items-center gap-1 mb-1 font-medium">Socavamiento<span
                    class="text-red-600">*</span></label>
                <InputText v-model="formWound.undermining" class="w-full min-w-0" />
                <small v-if="errors.undermining" class="text-red-500">{{
                  errors.undermining
                }}</small>
              </div>

              <!-- Granulación -->
              <div>
                <label for="granulation">Granulación (%)<span class="text-red-600">*</span></label>
                <InputText id="granulation" v-model="formWound.granulation_percent" type="number" min="0" max="100"
                  step="1" @input="adjustProgress" class="w-full" />
                <small v-if="errors.granulation_percent" class="text-red-500">{{ errors.granulation_percent }}</small>
              </div>

              <!-- Esfacelo -->
              <div>
                <label for="slough">Esfacelo (%)<span class="text-red-600">*</span></label>
                <InputText id="slough" v-model="formWound.slough_percent" type="number" min="0" max="100" step="1"
                  @input="adjustProgress" class="w-full" />
                <small v-if="errors.slough_percent" class="text-red-500">{{ errors.slough_percent }}</small>
              </div>

              <!-- Necrosis -->
              <div>
                <label for="necrosis">Necrosis (%)<span class="text-red-600">*</span></label>
                <InputText id="necrosis" v-model="formWound.necrosis_percent" type="number" min="0" max="100" step="1"
                  @input="adjustProgress" class="w-full" />
                <small v-if="errors.necrosis_percent" class="text-red-500">{{ errors.necrosis_percent }}</small>
              </div>

              <!-- Epitelización -->
              <div>
                <label for="epithelialization">Epitelización (%)<span class="text-red-600">*</span></label>
                <InputText id="epithelialization" v-model="formWound.epithelialization_percent" type="number" min="0"
                  max="100" step="1" @input="adjustProgress" class="w-full" />
                <small v-if="errors.epithelialization_percent" class="text-red-500">{{ errors.epithelialization_percent
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

            <div class="mt-6 flex justify-end gap-2 p-10">
              <Button label="Actualizar" icon="pi pi-check" @click="saveMeasurement" :loading="isSavingMeasurement"
                :disabled="isSavingMeasurement" />
            </div>

            <!-- Nueva sección: Evidencia de la herida -->
            <div class="col-span-full mt-10">
              <h3 class="text-lg font-semibold text-gray-700 mb-2">
                Evidencia de la herida
              </h3>
            </div>

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
                    <ProgressBar :value="totalSizePercent" :showValue="false" class="md:w-20rem h-1 w-full md:ml-auto">
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

                    <img v-for="img in existingImages" :key="img.id" :src="`/storage/${img.content}`"
                      :style="getImageStyle(img.position || 0)"
                      class="w-full h-auto min-h-20 rounded-lg object-cover cursor-pointer transition-all duration-150"
                      :class="{
                        'shadow-[0_0_0_2px] shadow-surface-900 dark:shadow-surface-0':
                          selectedImage === `/storage/${img.content}`,
                      }" @click="selectImage(img)" alt="Miniatura" />
                  </div>

                  <Button label="Descargar" class="w-full !py-2 !px-3 !text-base !font-medium !rounded-md" rounded
                    outlined @click="downloadSelectedImage" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-show="currentStep === 3">
          <form @submit.prevent="storeTreatment">
            <div
              class="flex flex-col flex-grow p-4 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
              <h2 class="text-xl font-semibold mb-4 px-4 pt-4">Establecer tratamiento</h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-6 px-4">

                <!-- Selección de métodos -->
                <div>
                  <label class="flex items-center gap-1 mb-1 font-medium">Seleccionar métodos<span
                      class="text-red-600">*</span></label>
                  <MultiSelect v-model="formTreat.methods" :options="treatmentMethods" optionLabel="name"
                    optionValue="id" display="chip" class="w-full min-w-0"
                    placeholder="Seleccione uno o varios métodos" />
                </div>

                <!-- Submétodos por método -->
                <div v-for="method in selectedMethodsWithSubmethods" :key="method.id">
                  <label class="flex items-center gap-1 mb-1 font-medium">
                    {{ method.name }}
                  </label>
                  <MultiSelect v-model="formTreat.submethodsByMethod[method.id]" :options="method.submethods"
                    class="w-full min-w-0" optionLabel="name" optionValue="id" placeholder="Seleccione submétodos"
                    display="chip" />
                </div>
              </div>
              <!-- Descripción final -->
              <div class="grid grid-cols-3 gap-6 px-4 mt-6">
                <div class="col-span-3">
                  <label for="description" class="flex items-center gap-1 mb-1 font-medium">
                    Descripción final del tratamiento de la herida<span class="text-red-600">*</span>
                  </label>
                  <Editor v-model="formTreat.description" editorStyle="height: 150px" class="w-full min-w-0" />
                  <small v-if="errors.description" class="text-red-500">{{ errors.description }}</small>
                </div>
              </div>
              <div>

              </div>
              <div class="mt-6 flex justify-end gap-2 p-10">
                <Button label="Actualizar" icon="pi pi-check" type="submit" :loading="isSavingTreatment"
                  :disabled="isSavingTreatment" />

                <Button v-if="hasTreatment" label="Terminar consulta" icon="pi pi-check" severity="danger"
                  :loading="isSavingTreatment" :disabled="isSavingTreatment" @click="finishConsultation" />

              </div>

            </div>
          </form>
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