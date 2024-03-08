<template>
  <div class="w-full">
    <label
      v-if="labelText !== '' && labelVisible"
      :for="labelText"
      class="w-full px-4 text-start text-xs font-medium text-[#173D7A]"
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
      :required="required"
      class="focus-visible:ring-offset-orange-300 relative flex h-8 w-full items-center rounded-3xl border border-[#B4C1CE] bg-white pl-4 pr-2 text-left text-[12px] leading-5 text-[#9CA3AF] focus:outline-none focus-visible:border-[#FF890E] focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 min-[380px]:pr-10 sm:w-64"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    labelText?: string;
    modelValue: string;
    type?: string;
    min?: number;
    labelVisible?: boolean;
    required?: boolean;
  }>(),
  {
    labelText: '',
    type: 'text',
    min: 0,
    labelVisible: true,
    required: false
  }
);

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
