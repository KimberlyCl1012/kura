<template>
  <div class="space-y-6">
    <!-- Evaluación general de la herida -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <InputLabel value="Cantidad de exudado" />
      <InputText v-model="formWound.exudateAmount" class="w-full" />

      <InputLabel value="Color del exudado" />
      <InputText v-model="formWound.exudateColor" class="w-full" />

      <InputLabel value="Olor del exudado" />
      <InputText v-model="formWound.exudateOdor" class="w-full" />

      <InputLabel value="Condición de los bordes" />
      <InputText v-model="formWound.edgeCondition" class="w-full" />

      <InputLabel value="Piel perilesional" />
      <InputText v-model="formWound.perilesionalSkin" class="w-full" />
    </div>

    <!-- Campos vasculares visibles solo si body_location_id es de pierna/pie -->
    <div
      class="mt-6 border-t pt-6"
      v-if="showVascularFields"
    >
      <h3 class="text-lg font-semibold mb-4">Evaluación Vascular</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Lado izquierdo -->
        <div>
          <InputLabel value="ITB izquierdo" />
          <InputText v-model="formWound.ITB_izquierdo" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso dorsal izquierdo" />
          <InputText v-model="formWound.pulse_dorsal_izquierdo" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso tibial izquierdo" />
          <InputText v-model="formWound.pulse_tibial_izquierdo" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso poplíteo izquierdo" />
          <InputText v-model="formWound.pulse_popliteo_izquierdo" class="w-full" />
        </div>

        <!-- Lado derecho -->
        <div>
          <InputLabel value="ITB derecho" />
          <InputText v-model="formWound.ITB_derecho" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso dorsal derecho" />
          <InputText v-model="formWound.pulse_dorsal_derecho" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso tibial derecho" />
          <InputText v-model="formWound.pulse_tibial_derecho" class="w-full" />
        </div>

        <div>
          <InputLabel value="Pulso poplíteo derecho" />
          <InputText v-model="formWound.pulse_popliteo_derecho" class="w-full" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import { InputText } from 'primevue'

const props = defineProps({
  formWound: {
    type: Object,
    required: true
  }
})

// Mostrar campos vasculares si body_location_id está entre 18 y 33
const showVascularFields = computed(() => {
  const ids = Array.from({ length: 16 }, (_, i) => i + 18)
  return ids.includes(props.formWound?.body_location_id)
})
</script>
