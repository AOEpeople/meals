<template>
  <div class="w-full">
    <label
      v-if="labelText !== '' && labelVisible"
      :for="labelText"
      class="w-fulltext-start px-4 text-xs font-medium text-[#173D7A]"
    >
      {{ labelText }}
    </label>
    <input
      :id="labelText"
      v-model="value"
      :type="type"
      :name="labelText"
      :placeholder="labelText"
      :min="min"
      class="w-full rounded-full border-2 border-solid border-[#CAD6E1] px-4 py-2 text-[14px] font-medium text-[#9CA3AF]"
    >
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  labelText?: string,
  modelValue: string,
  type?: string,
  min?: number,
  labelVisible?: boolean
}>(), {
  labelText: '',
  type: 'text',
  min: 0,
  labelVisible: true
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