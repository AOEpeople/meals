<template>
  <div class="w-full">
    <label
      v-if="labelText !== '' && labelVisible"
      :for="labelText"
      class="w-full px-4 text-start text-xs font-medium text-[#173D7A]"
    >
      {{ labelText }}
    </label>
    <div
      :class="
        overwriteContainerStyles === ''
          ? 'invalid flex h-[46px] w-full flex-row items-center overflow-hidden rounded-full border-2 border-solid border-[#CAD6E1] bg-white px-4 py-2 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2'
          : overwriteContainerStyles
      "
    >
      <input
        :id="labelText"
        v-model="value"
        :type="type"
        :name="labelText"
        :placeholder="labelText"
        :min="min"
        :max="max"
        :step="step"
        :required="required"
        :class="
          overwriteInputStyle === ''
            ? 'w-full truncate border-none bg-none text-[14px] font-medium text-[#9CA3AF] invalid:border-[#E02927] focus:outline-none'
            : overwriteInputStyle
        "
      />
      <XIcon
        v-if="value !== '' && xButtonActive === true"
        class="h-full w-10 cursor-pointer justify-self-end text-[#9CA3AF] transition-transform hover:scale-[120%]"
        aria-hidden="true"
        @click="value = ''"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { XIcon } from '@heroicons/vue/solid';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    labelText?: string;
    modelValue: string | number;
    type?: string;
    min?: number;
    max?: number | undefined;
    step?: string | number;
    labelVisible?: boolean;
    required?: boolean;
    xButtonActive?: boolean;
    overwriteInputStyle?: string;
    overwriteContainerStyles?: string;
  }>(),
  {
    labelText: '',
    type: 'text',
    min: 0,
    step: '',
    labelVisible: true,
    required: false,
    xButtonActive: false,
    overwriteInputStyle: '',
    overwriteContainerStyles: ''
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
