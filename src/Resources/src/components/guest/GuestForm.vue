<template>
  <div class="mx-4 grid grid-cols-3 gap-4">
    <div class="grid grid-rows-2">
          <span class="ml-2 text-primary-3 uppercase tracking-[1px] text-note font-bold self-center">
            {{ t('guest.form.firstname') }}*
          </span>
      <input
          class="bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12"
          type="text"
          v-text="firstname"
          @input="$emit('update:firstName', $event.target.value)"
          required
      />
    </div>
    <div class="grid grid-rows-2">
          <span class="ml-2 text-primary-3 uppercase tracking-[1px] text-note font-bold self-center">
            {{ t('guest.form.lastname') }}*
          </span>
      <input
          class="bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12"
          type="text"
          v-text="lastname"
          @input="$emit('update:lastName', $event.target.value)"
          required
      />
    </div>
    <div class="grid grid-rows-2">
          <span class="ml-2 text-primary-3 uppercase tracking-[1px] text-note font-bold self-center">
            {{ t('guest.form.company') }}*
          </span>
      <input
          class="bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12"
          type="text"
          v-text="company"
          @input="$emit('update:company', $event.target.value)"
          required
      />
    </div>
    <button @click="emit('submitForm')"
            :class="[filled ? 'btn-primary' : 'btn-disabled']"
            :disabled="!filled">
      {{ t('guest.submit') }}
    </button>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
defineProps(['firstname', 'lastname', 'company', 'filled'])
const emit = defineEmits(['submitForm', 'update:modelValue'])

</script>
