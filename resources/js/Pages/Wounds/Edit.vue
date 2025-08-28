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
import { usePage } from "@inertiajs/vue3";

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
  assessments: Object,
});

const page = usePage();
const userRole = computed(() => page.props.userRole);
const userPermissions = computed(() => page.props.userPermissions);
const userSite = computed(() => page.props.userSiteId);
const userSiteName = computed(() => page.props.userSiteName);

const requiresVascular = computed(() =>
  [
    18, 19, 20, 21, 22, 23, 24, 25,
    26, 27, 28, 29, 30, 31, 32, 33,
  ].includes(formWound.value.body_location_id)
);

// UI y estado
const toast = useToast();
const currentStep = ref(1);
const isSavingUser = ref(false);
const isSavingMeasurement = ref(false);
const submittedUser = ref(false);
const submittedMeasurement = ref(false);
const errors = ref({});

// Catálogos
const grades = ref([{ id: 1, name: "1" }, { id: 2, name: "2" }, { id: 3, name: "3" }]);
const MESI = ref([{ name: "Manual" }, { name: "MESI" }, { name: "No aplica" }]);
const bordes = ref([]);
const edema = ref([]);
const dolor = ref([]);
const exudado_cantidad = ref([]);
const exudado_tipo = ref([]);
const olor = ref([]);
const tipo_dolor = ref([]);
const piel_perilesional = ref([]);
const infeccion = ref([]);
const duracion_dolor = ref([]);

onMounted(() => {
  const A = props.assessments || {};
  edema.value = (A["Edema"] || []).map(n => ({ name: n }));
  dolor.value = (A["Dolor"] || []).map(n => ({ name: n }));
  tipo_dolor.value = (A["Tipo de dolor"] || []).map(n => ({ name: n }));
  exudado_cantidad.value = (A["Exudado (Cantidad)"] || []).map(n => ({ name: n }));
  exudado_tipo.value = (A["Exudado (tipo)"] || []).map(n => ({ name: n }));
  olor.value = (A["Olor"] || []).map(n => ({ name: n }));
  bordes.value = (A["Borde de la herida"] || []).map(n => ({ name: n }));
  duracion_dolor.value = (A["Duración del dolor"] || []).map(n => ({ name: n }));
  piel_perilesional.value = (A["Piel perilesional"] || []).map(n => ({ label: n, value: n }));
  infeccion.value = (A["Infeccion"] || []).map(n => ({ label: n, value: n }));
});

// Formulario principal
const formWound = ref({
  wound_type_id: null,
  grade_foot: null,
  wound_subtype_id: null,
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
  piel_perilesional: [],
  infeccion: [],
  tipo_dolor: null,
  duracion_dolor: null,
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

  if (requiresVascular.value) {
    const requiredVascularFields = [
      'MESI',
      'ITB_izquierdo',
      'ITB_derecho',
      'pulse_dorsal_izquierdo',
      'pulse_dorsal_derecho',
      'pulse_popliteo_izquierdo',
      'pulse_popliteo_derecho',
      'pulse_tibial_izquierdo',
      'pulse_tibial_derecho',
      'monofilamento',
      'blood_glucose'
    ];

    const vascularErrors = requiredVascularFields.filter(
      (field) => !formWound.value[field] && formWound.value[field] !== 0
    );

    if (vascularErrors.length > 0) {
      vascularErrors.forEach((field) => {
        errors.value[field] = 'Este campo es obligatorio.';
      });

      toast.add({
        severity: 'error',
        summary: 'Error de validación',
        detail: 'Por favor complete los campos vasculares en la evaluación de la herida.',
        life: 4000,
      });

      isSavingUser.value = false;
      return;
    }
  }

  const isEmpty = (v) =>
    v === null || v === undefined || v === '' || (Array.isArray(v) && v.length === 0);

  // Campos requeridos de la sección (incluye MultiSelect)
  const requiredEvalFields = [
    'woundHealthDate',
    'edema',
    'dolor',
    'tipo_dolor',
    'duracion_dolor',
    'visual_scale',
    'exudado_tipo',
    'exudado_cantidad',
    'infeccion',
    'olor',
    'borde',
    'piel_perilesional',
  ];

  const evalLabels = {
    woundHealthDate: 'Fecha de primera valoración',
    edema: 'Edema',
    dolor: 'Dolor',
    tipo_dolor: 'Tipo de dolor',
    duracion_dolor: 'Duración del dolor',
    visual_scale: 'Escala Visual Analógica (EVA)',
    exudado_tipo: 'Exudado (Tipo)',
    exudado_cantidad: 'Exudado (Cantidad)',
    infeccion: 'Infección',
    olor: 'Olor',
    borde: 'Borde de la herida',
    piel_perilesional: 'Piel perilesional',
  };

  requiredEvalFields.forEach((f) => delete errors.value[f]);

  const missingEval = requiredEvalFields.filter((f) => isEmpty(formWound.value[f]));

  if (!isEmpty(formWound.value.visual_scale)) {
    const eva = String(formWound.value.visual_scale).trim();
    const m = eva.match(/^(\d{1,2})\s*\/\s*10$/);
    if (!m || Number(m[1]) < 1 || Number(m[1]) > 10) {
      errors.value.visual_scale = 'Formato inválido. Usa n/10 entre 1 y 10 (ej. 3/10).';
    }
  }

  missingEval.forEach((f) => {
    if (!errors.value[f]) {
      errors.value[f] = `El campo "${evalLabels[f]}" es requerido.`;
    }
  });

  if (missingEval.length > 0 || errors.value.visual_scale) {
    toast.add({
      severity: 'error',
      summary: 'Campos requeridos',
      detail: 'Completa los campos obligatorios de la evaluación de la herida.',
      life: 4000,
    });
    isSavingUser.value = false;
    return;
  }

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
    // Forzar tipo y ubicación para que los watchers se activen correctamente
    formWound.value.wound_type_id = null;
    formWound.value.body_location_id = null;

    // Asignar valores completos
    Object.assign(formWound.value, {
      ...props.wound,
      id: props.wound.id || props.wound.wound_id,
      woundBeginDate: props.wound.woundBeginDate ? new Date(props.wound.woundBeginDate) : null,
      woundHealthDate: props.wound.woundHealthDate ? new Date(props.wound.woundHealthDate) : null,
      grade_foot: props.wound.grade_foot ? parseInt(props.wound.grade_foot) : null,
    });

    // Llamar explícitamente las cargas iniciales si los valores existen
    if (props.wound.wound_type_id) {
      formWound.value.wound_type_id = parseInt(props.wound.wound_type_id);
      loadSubtypes(formWound.value.wound_type_id);
    }

    if (props.wound.body_location_id) {
      formWound.value.body_location_id = parseInt(props.wound.body_location_id);
      loadSublocations(formWound.value.body_location_id);
    }

    //Measurement
    if (props.measurement) {
      Object.assign(formWound.value, {
        measurementDate: props.measurement.measurementDate ? new Date(props.measurement.measurementDate) : null,
        length: props.measurement.length,
        width: props.measurement.width,
        area: props.measurement.area,
        depth: props.measurement.depth,
        volume: props.measurement.volume,
        tunneling: props.measurement.tunneling,
        undermining: props.measurement.undermining,
        granulation_percent: props.measurement.granulation_percent,
        slough_percent: props.measurement.slough_percent,
        necrosis_percent: props.measurement.necrosis_percent,
        epithelialization_percent: props.measurement.epithelialization_percent,
      });
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
    'measurementDate', 'length', 'width', 'undermining',
    'granulation_percent', 'slough_percent', 'necrosis_percent', 'epithelialization_percent',
  ];

  const fieldLabels = {
    measurementDate: 'Fecha de medición',
    length: 'Longitud',
    width: 'Anchura',
    area: 'Área',
    depth: 'Profundidad',
    volume: 'Volumen',
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
      appointment_id: props.wound.appointment_id,
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
const MAX_FILE_SIZE = 9 * 1024 * 1024; // 9 MB

const fileUploadRef = ref(null);

const showLimitModal = ref(false);
const showConfirmUploadModal = ref(false);
const showConfirmDeleteModal = ref(false);
const showZoomModal = ref(false);

const uploadFiles = ref([]);
const existingImages = ref([]);
const selectedImage = ref("");
const selectedImageRotation = ref(0);

const zoomImageUrl = ref("");
const zoomRotation = ref(0);

const totalSize = ref(0);
const totalSizePercent = ref(0);

const imageToDelete = ref(null);

const getImageStyle = (rotation) => ({ transform: `rotate(${rotation}deg)` });

const updateTotalSize = () => {
  totalSize.value = uploadFiles.value.reduce((acc, f) => acc + f.size, 0);
  totalSizePercent.value = Math.min(100, Math.round((totalSize.value / MAX_FILE_SIZE) * 100));
};

const revokeAllObjectURLs = () => {
  uploadFiles.value.forEach((f) => f?.objectURL && URL.revokeObjectURL(f.objectURL));
};

const resetUploads = () => {
  revokeAllObjectURLs();
  uploadFiles.value = [];
  totalSize.value = 0;
  totalSizePercent.value = 0;
};

const isLimitReached = (incomingCount) =>
  existingImages.value.length + uploadFiles.value.length + incomingCount > MAX_FILES;

const openZoomModal = (src, rotation) => {
  zoomImageUrl.value = src;
  zoomRotation.value = rotation || 0;
  showZoomModal.value = true;
};

const closeLimitModal = () => (showLimitModal.value = false);

const onSelectedFiles = (event) => {
  const incoming = event.files || [];
  if (!incoming.length) return;

  if (isLimitReached(incoming.length)) {
    showLimitModal.value = true;
    return;
  }

  for (const file of incoming) {
    // Validaciones defensivas
    if (!file.type?.startsWith("image/")) {
      toast.add({ severity: "warn", summary: "Archivo omitido", detail: `${file.name} no es una imagen.`, life: 3000 });
      continue;
    }
    if (file.size > MAX_FILE_SIZE) {
      toast.add({ severity: "warn", summary: "Muy grande", detail: `${file.name} excede 9MB.`, life: 3000 });
      continue;
    }

    uploadFiles.value.push({
      raw: file,
      name: file.name,
      size: file.size,
      type: file.type,
      objectURL: URL.createObjectURL(file),
      rotation: 0,
    });
  }

  updateTotalSize();
};

const rotateImage = (index, direction) => {
  const file = uploadFiles.value[index];
  if (!file) return;
  file.rotation = direction === "left"
    ? (file.rotation - 5 + 360) % 360
    : (file.rotation + 5) % 360;
};

const removeFile = (index) => {
  const f = uploadFiles.value[index];
  if (f?.objectURL) URL.revokeObjectURL(f.objectURL);
  uploadFiles.value.splice(index, 1);
  updateTotalSize();
};

const clearTemplatedUpload = (clear) => {
  clear();
  resetUploads();
};

const selectImage = (img) => {
  selectedImage.value = `/storage/${img.content}`;
  selectedImageRotation.value = img.position || 0;
};

const downloadSelectedImage = () => {
  if (!selectedImage.value) return;
  const link = document.createElement("a");
  link.href = selectedImage.value;
  link.download = selectedImage.value.split("/").pop();
  link.click();
};

const loadExistingImages = async () => {
  const woundHistoryId = props.woundHistory?.id;
  const woundId = props.wound?.id;

  try {
    if (woundHistoryId) {
      const { data } = await axios.get("/media_history", {
        params: { wound_history_id: woundHistoryId },
      });
      existingImages.value = data || [];
    } else if (woundId) {
      const { data } = await axios.get("/media", {
        params: {
          wound_id: woundId,
          appointment_id: props.wound?.appointment_id,
          type: "Herida",
        },
      });
      existingImages.value = data || [];
    }
  } catch (error) {
    if (error.response?.status !== 404) {
      console.error("Error al cargar imágenes:", error);
    }
  }
};

watch(existingImages, (imgs) => {
  if (imgs.length > 0 && !selectedImage.value) {
    selectImage(imgs[0]);
  }
  if (imgs.length === 0) {
    selectedImage.value = "";
    selectedImageRotation.value = 0;
  }
});

const uploadEvent = async () => {
  const hasContext = !!(props.woundHistory?.id || props.wound?.id);
  if (!hasContext) {
    toast.add({
      severity: "warn",
      summary: "Advertencia",
      detail: "Debe guardar la herida/antecedente antes de subir imágenes.",
      life: 4000,
    });
    return;
  }

  if (!uploadFiles.value.length) {
    toast.add({ severity: "warn", summary: "Sin archivos", detail: "Debes seleccionar imágenes para subir.", life: 3000 });
    return;
  }

  const formData = new FormData();
  uploadFiles.value.forEach((file, index) => {
    formData.append("images[]", file.raw, file.name);
    formData.append(`rotations[${index}]`, file.rotation || 0);
  });

  try {
    if (props.woundHistory?.id) {
      formData.append("wound_history_id", props.woundHistory.id);
      await axios.post("/media_history/upload", formData);
    } else {
      formData.append("type", "Herida");
      formData.append("wound_id", props.wound.id);
      formData.append("appointment_id", props.wound.appointment_id);
      await axios.post("/media/upload", formData);
    }

    toast.add({ severity: "success", summary: "Éxito", detail: "Imágenes subidas.", life: 3000 });
    resetUploads();
    await loadExistingImages();
  } catch (err) {
    console.error(err);
    toast.add({ severity: "error", summary: "Error", detail: "Fallo al subir.", life: 4000 });
  }
};

const confirmUpload = async () => {
  showConfirmUploadModal.value = false;
  await uploadEvent();
  if (fileUploadRef.value) fileUploadRef.value.clear();
};


const openConfirmDeleteSelected = () => {
  if (!selectedImage.value) return;
  const img = existingImages.value.find((i) => `/storage/${i.content}` === selectedImage.value);
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
    if (props.woundHistory?.id) {
      await axios.delete(`/media_history/${imageToDelete.value.id}`);
    } else {
      await axios.post(`/media/${imageToDelete.value.id}`, {
        _method: "DELETE",
        wound_id: props.wound?.id,
        appointment_id: props.wound?.appointment_id,
        type: "Herida",
      });
    }

    existingImages.value = existingImages.value.filter((i) => i.id !== imageToDelete.value.id);

    if (selectedImage.value === `/storage/${imageToDelete.value.content}`) {
      if (existingImages.value.length) {
        selectImage(existingImages.value[0]);
      } else {
        selectedImage.value = "";
        selectedImageRotation.value = 0;
      }
    }

    toast.add({ severity: "success", summary: "Eliminada", detail: "Imagen eliminada correctamente.", life: 3000 });
  } catch (err) {
    console.error(err);
    toast.add({ severity: "error", summary: "Error", detail: "No se pudo eliminar la imagen.", life: 4000 });
  } finally {
    showConfirmDeleteModal.value = false;
    imageToDelete.value = null;
  }
};

loadExistingImages();

// Tratamiento
const treatmentId = ref(props.treatment?.id ?? null)
const hasTreatment = ref(!!props.treatment)
const isSavingTreatment = ref(false)
const submittedTreatment = ref(false)

const formTreat = ref({
  methods: props.treatment?.methods?.map(m => m.treatment_method_id) ?? [],
  submethodsByMethod: props.treatment
    ? (() => {
      const map = {}
      props.treatment.submethods.forEach(sub => {
        const methodId = sub.treatment_method_id
        map[methodId] = map[methodId] || []
        map[methodId].push(sub.treatment_submethod_id)
      })
      return map
    })()
    : {},
  description: props.treatment?.description ?? '',
  mmhg: props.treatment?.mmhg != null ? String(props.treatment.mmhg) : '',
})

const canFullEdit = computed(() => {
  if (typeof props.permissions?.can_full_edit === 'boolean') {
    return props.permissions.can_full_edit
  }

  if (typeof usePage === 'function') {
    try {
      const p = usePage()
      if (Array.isArray(p?.props?.userPermissions)) {
        return p.props.userPermissions.includes('edit_treatment')
      }
      const po = p?.props?.permissions
      if (po && typeof po === 'object') {
        return Boolean(
          po.edit_treatment ??
          po.can_full_edit ??
          po['record.edit_treatment']
        )
      }
    } catch (_) { /* noop */ }
  }

  return false
})

const editorRestrictDeletion = computed(() => !canFullEdit.value)

const TRIGGER_IDS = [54, 55, 56]
const TRIGGERIDS_SET = new Set(TRIGGER_IDS)
const hydrated = ref(false)

const requiresMMHG = computed(() => {
  const allSelectedSubs = Object.values(formTreat.value.submethodsByMethod || {}).flat()
  return allSelectedSubs.some(id => TRIGGERIDS_SET.has(Number(id)))
})

watch(requiresMMHG, (req, prev) => {
  if (!req && prev && hydrated.value) {
    formTreat.value.mmhg = ''
  }
})

const selectedMethodsWithSubmethods = computed(() => {
  const ids = new Set(formTreat.value.methods)
  return props.treatmentMethods.filter(method => ids.has(method.id))
})

watch(
  () => formTreat.value.methods,
  (newMethods) => {
    const validMap = {}
    newMethods.forEach(methodId => {
      if (Array.isArray(formTreat.value.submethodsByMethod[methodId])) {
        validMap[methodId] = formTreat.value.submethodsByMethod[methodId]
      } else {
        validMap[methodId] = []
      }
    })
    formTreat.value.submethodsByMethod = validMap
  },
  { deep: true }
)

const baselineHtml = ref(props.treatment?.description ?? '')
const normalizeText = (html) => {
  const txt = String(html || '')
    .replace(/<[^>]*>/g, '')
    .replace(/\u00A0/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
  return txt.toLocaleLowerCase()
}
const baselineNorm = ref(normalizeText(baselineHtml.value))
const lastGoodHtml = ref(formTreat.value.description ?? '')
const skipDescWatchOnce = ref(true)
const deletionWarningShown = ref(false)

watch(
  () => formTreat.value.description,
  (newHtml) => {
    if (skipDescWatchOnce.value) {
      lastGoodHtml.value = newHtml ?? ''
      skipDescWatchOnce.value = false
      return
    }

    if (!editorRestrictDeletion.value) {
      lastGoodHtml.value = newHtml ?? ''
      return
    }

    const newNorm = normalizeText(newHtml)
    const ok = baselineNorm.value === '' || newNorm.startsWith(baselineNorm.value)

    if (!ok) {
      if (!deletionWarningShown.value) {
        toast.add({
          severity: 'warn',
          summary: 'Edición limitada',
          detail: 'No puedes eliminar ni modificar el texto existente; solo agregar al final.',
          life: 2000,
        })
        deletionWarningShown.value = true
        setTimeout(() => (deletionWarningShown.value = false), 1200)
      }
      formTreat.value.description = lastGoodHtml.value
      return
    }

    lastGoodHtml.value = newHtml ?? ''
  }
)

const storeTreatment = async () => {
  submittedTreatment.value = true
  errors.value = {}

  const missingSubmethods = formTreat.value.methods.filter(
    (methodId) =>
      !formTreat.value.submethodsByMethod[methodId] ||
      formTreat.value.submethodsByMethod[methodId].length === 0
  )
  if (missingSubmethods.length > 0) {
    toast.add({
      severity: 'error',
      summary: 'Validación',
      detail: 'Cada método debe tener al menos un submétodo seleccionado.',
      life: 4000,
    })
    return
  }

  let mmhgToSend = null
  if (requiresMMHG.value) {
    const raw = String(formTreat.value.mmhg ?? '').trim()
    const isNumber = /^(\d+(\.\d+)?)$/.test(raw)
    if (!raw || !isNumber) {
      errors.value = { ...(errors.value || {}), mmhg: 'Campo mmHg requerido.' }
      toast.add({
        severity: 'error',
        summary: 'Validación',
        detail: 'El campo mmHg es obligatorio y debe ser numérico.',
        life: 4000,
      })
      return
    }
    mmhgToSend = Number(raw)
  }

  isSavingTreatment.value = true

  const payload = {
    treatment_id: treatmentId.value,
    appointment_id: props.wound.appointment_id,
    wound_id: formWound.value.id,
    description: formTreat.value.description || null,
    method_ids: formTreat.value.methods,
    submethodsByMethod: formTreat.value.submethodsByMethod,
    mmhg: mmhgToSend,
  }

  try {
    const { data } = await axios.post('/treatments', payload)

    if (data?.treatment_id) {
      treatmentId.value = data.treatment_id
    }

    if (data?.append_blocked) {
      toast.add({
        severity: 'warn',
        summary: 'Edición limitada',
        detail: 'No puedes eliminar ni modificar el texto existente; solo agregar al final.',
        life: 3000,
      })
    } else {
      toast.add({
        severity: 'success',
        summary: 'Éxito',
        detail: data?.message || (hasTreatment.value ? 'Tratamiento actualizado.' : 'Tratamiento guardado.'),
        life: 3000,
      })
    }

    hasTreatment.value = true
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      const allMessages = Object.values(errors.value).flat().filter(Boolean).join('\n')
      toast.add({
        severity: 'error',
        summary: 'Errores de validación',
        detail: allMessages || 'Revisa los campos.',
        life: 6000,
      })
    } else {
      const detail =
        error.response?.data?.message ||
        error.message ||
        'No se pudo guardar el tratamiento.'
      toast.add({ severity: 'error', summary: 'Error', detail, life: 4000 })
    }
  } finally {
    isSavingTreatment.value = false
  }
}

onMounted(async () => {
  if (props.treatment) {
    formTreat.value.methods = props.treatment.methods?.map(m => m.treatment_method_id) ?? []

    const map = {}
      ; (props.treatment.submethods ?? []).forEach(sub => {
        const methodId = sub.treatment_method_id
        if (methodId) {
          map[methodId] = map[methodId] || []
          map[methodId].push(sub.treatment_submethod_id)
        }
      })
    formTreat.value.submethodsByMethod = map

    formTreat.value.description = props.treatment.description ?? ''
    formTreat.value.mmhg = props.treatment.mmhg != null ? String(props.treatment.mmhg) : ''
  }

  baselineHtml.value = props.treatment?.description ?? ''
  baselineNorm.value = normalizeText(baselineHtml.value)
  lastGoodHtml.value = formTreat.value.description ?? ''

  skipDescWatchOnce.value = false
  hydrated.value = true
})
// // Fin Tratamiento

//Terminar consulta
const showConfirmFinishDialog = ref(false);
const woundsInAppointment = ref(0);

const confirmFinishConsultation = async () => {
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

    // Mostrar el modal de confirmación
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
    }, {
      headers: {
        'Content-Type': 'application/json',
      }
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

                <!-- Picadura -->
                <div v-if="formWound.wound_subtype_id === 10">
                  <label class="block font-bold mb-1">
                    Tipo de picadura/mordedura <span class="text-red-600">*</span>
                  </label>
                  <InputText id="type_bite" v-model="formWound.type_bite" class="w-full min-w-0" />
                  <small v-if="submittedUser && !formWound.type_bite" class="text-red-500">
                    Debe escribir el tipo de picadura/mordedura.
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
                    placeholder="mm/dd/yyyy" showIcon />
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
                <template v-if="requiresVascular">
                  <!-- Campos vasculares -->
                  <div class="col-span-full">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">
                      Valoración vascular
                    </h3>
                  </div>

                  <div>
                    <label class="flex items-center gap-1 mb-1 font-medium">
                      Índice tobillo brazo
                      <span class="text-red-600">*</span>
                    </label>
                    <Select id="MESI" v-model="formWound.MESI" :options="MESI" filter optionLabel="name"
                      optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción" />
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
                    placeholder="mm/dd/yyyy" showIcon />
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
                    Duración del dolor <span class="text-red-600">*</span>
                  </label>
                  <Select id="duracion_dolor" v-model="formWound.duracion_dolor" :options="duracion_dolor" filter
                    optionLabel="name" optionValue="name" class="w-full min-w-0" placeholder="Seleccione una opción">
                  </Select>
                  <small v-if="errors.duracion_dolor" class="text-red-500">{{
                    errors.duracion_dolor
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
                  <MultiSelect id="infeccion" v-model="formWound.infeccion" :options="infeccion" optionLabel="label"
                    optionValue="value" class="w-full min-w-0" filter placeholder="Selecciona una o más opciones" />
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
                    Piel perilesional <span class="text-red-600">*</span>
                  </label>
                  <MultiSelect id="piel_perilesional" v-model="formWound.piel_perilesional" :options="piel_perilesional"
                    filter optionLabel="label" optionValue="value" class="w-full min-w-0"
                    placeholder="Selecciona una o más opciones" />
                  <small v-if="errors.piel_perilesional" class="text-red-500">{{
                    errors.piel_perilesional
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
            <div
              class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
              <h3 class="text-xl font-semibold px-4">
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
                  placeholder="mm/dd/yyyy" showIcon />
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
                <label class="flex items-center gap-1 mb-1 font-medium">Profundidad (cm)</label>
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
                <label class="flex items-center gap-1 mb-1 font-medium">Tunelización</label>
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
            <div
              class="flex flex-col flex-grow pt-5 border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 overflow-auto">
              <h3 class="text-xl font-semibold px-4">
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
                      <Button @click="chooseCallback()" icon="pi pi-images" rounded outlined severity="secondary"  v-if="userRole === 'admin' || (userPermissions.includes('create_photographic_evidence'))" />
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

            <!-- Confirmación de subida -->
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
              :style="{ width: '90vw', height: '90vh' }"
              :contentStyle="{ padding: 0, margin: 0, height: '100%', display: 'flex', justifyContent: 'center', alignItems: 'center' }">
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
                <p class="mb-4">
                  Solo puedes subir un máximo de <strong>{{ MAX_FILES }}</strong> imágenes.
                </p>
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
                          'shadow-[0_0_0_2px] shadow-surface-900 dark:shadow-surface-0': selectedImage === `/storage/${img.content}`,
                        }" @click="selectImage(img)" alt="Miniatura" />

                      <!-- Botón borrar por miniatura -->
                      <button type="button"
                        v-if="userRole === 'admin' || (userPermissions.includes('delete_photographic_evidence'))"
                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-red-600 text-white rounded-md px-2 py-1 text-xs shadow"
                        @click.stop="openConfirmDeleteByThumb(img)" title="Eliminar imagen">
                        Eliminar
                      </button>
                    </div>
                  </div>

                  <Button label="Descargar" class="w-full" @click="downloadSelectedImage" />
                  <Button label="Eliminar" outlined severity="danger" class="w-full" :disabled="!selectedImage"
                    v-if="userRole === 'admin' || (userPermissions.includes('delete_photographic_evidence'))"
                    @click="openConfirmDeleteSelected" />
                </div>
              </div>
            </div>

            <!-- Confirmación de eliminación -->
            <Dialog v-model:visible="showConfirmDeleteModal" modal header="Eliminar imagen" :style="{ width: '400px' }">
              <div class="text-center p-4">
                <p class="mb-4">¿Desea eliminar esta imagen?</p>
                <div class="flex justify-center gap-3">
                  <Button label="Cancelar" text @click="showConfirmDeleteModal = false" />
                  <Button label="Eliminar" severity="danger" @click="deleteImage" autofocus />
                </div>
              </div>
            </Dialog>

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

                <!-- mmHg -->
                <div v-if="requiresMMHG">
                  <label for="mmhg" class="flex items-center gap-1 mb-1 font-medium">
                    mmHg <span class="text-red-600">*</span>
                  </label>
                  <InputText id="mmhg" v-model="formTreat.mmhg" class="w-full min-w-0" />
                  <small v-if="errors.mmhg" class="text-red-500">{{ errors.mmhg }}</small>
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

                <Button v-if="hasTreatment" label="Terminar consulta" icon="pi pi-sign-out" severity="danger" text
                  :loading="isSavingTreatment" :disabled="isSavingTreatment" @click="confirmFinishConsultation" />

              </div>

            </div>
          </form>
        </div>

        <Dialog v-model:visible="showConfirmFinishDialog" modal header="Terminar consulta" :style="{ width: '450px' }">
          <div class="text-center p-4">
            <p class="mb-4 text-justify">
              Esta consulta tiene <strong>{{ woundsInAppointment }}</strong> herida(s) registrada(s).
              &nbsp; ¿Estás seguro de que todas han sido correctamente configuradas y deseas finalizar la
              consulta?<br /><br />
              <strong> Esta acción no se puede deshacer. Una vez finalizada, no podrás modificar las heridas
                asociadas.</strong>
            </p>
            <div class="flex justify-center gap-3">
              <Button label="Cancelar" icon="pi pi-check" text @click="showConfirmFinishDialog = false" />
              <Button label="Sí, finalizar" icon="pi pi-check" @click="onConfirmFinishConsultation" autofocus />
            </div>
          </div>
        </Dialog>

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
}

@media (min-width: 1024px) {
  .zoom-img {
    margin-top: 5rem;
  }
}
</style>