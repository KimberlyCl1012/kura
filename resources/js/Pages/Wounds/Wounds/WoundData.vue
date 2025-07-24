<script setup>
import { defineProps, defineEmits } from "vue";
import { InputText, Select, Button, DatePicker } from "primevue";

const props = defineProps({
  formWound: Object,
  submitted: Boolean,
  woundsType: Array,
  woundsSubtype: Array,
  grades: Array,
  bodyLocations: Array,
  bodySublocation: Array,
  woundsPhase: Array,
  isSaving: Boolean,
});

const emit = defineEmits(["save"]);

function onSubmit() {
  emit("save");
}
</script>

<template>

</template>
