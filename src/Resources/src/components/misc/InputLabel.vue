<template>
  <div class="w-full">
    <label
      v-if="labelText !== ''"
      :for="labelText"
      class="w-full pl-2 text-start"
    >
      {{ labelText }}
    </label>
    <input
      :id="labelText"
      v-model="value"
      :type="type"
      :name="labelText"
      :placeholder="labelText"
      class="w-full rounded-lg border-0 border-none p-2"
    >
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  labelText?: string,
  modelValue: string,
  type?: string
}>(), {
  labelText: "",
  type: "text"
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});
</script>