<template>
  <div class="mx-4 grid grid-cols-3 gap-4">
    <div class="grid grid-rows-2">
      <span class="ml-2 self-center text-note font-bold uppercase tracking-[1px] text-primary-3">
        {{ t('guest.form.firstname') }}*
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white"
        type="text"
        required
        @input="$emit('update:firstName', $event.target.value)"
        v-text="firstname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="ml-2 self-center text-note font-bold uppercase tracking-[1px] text-primary-3">
        {{ t('guest.form.lastname') }}*
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white"
        type="text"
        required
        @input="$emit('update:lastName', $event.target.value)"
        v-text="lastname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="ml-2 self-center text-note font-bold uppercase tracking-[1px] text-primary-3">
        {{ t('guest.form.company') }}*
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white"
        type="text"
        required
        @input="$emit('update:company', $event.target.value)"
        v-text="company"
      >
    </div>
    <button
      :class="[filled ? 'btn-primary' : 'btn-disabled']"
      :disabled="!filled"
      @click="emit('submitForm')"
    >
      {{ t('guest.submit') }}
    </button>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
defineProps(['firstname', 'lastname', 'company', 'filled'])
const emit = defineEmits(['submitForm', 'update:modelValue', 'update:firstName', 'update:lastName', 'update:company'])

</script>
