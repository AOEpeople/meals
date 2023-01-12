<template>
  <div>
    <div class="mt-6 grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] leading-4 font-bold uppercase tracking-[1.5px] text-primary">
        {{ t('guest.form.firstname') }}
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:text-[14px] placeholder:leading-[22px] placeholder:font-semibold placeholder:pl-4 placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.firstnamePlaceholder')"
        required
        @input="$emit('update:firstName', $event.target.value)"
        v-text="firstname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] leading-4 font-bold uppercase tracking-[1.5px] text-primary">
        {{ t('guest.form.lastname') }}
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:text-[14px] placeholder:leading-[22px] placeholder:font-semibold placeholder:pl-4 placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.lastnamePlaceholder')"
        required
        @input="$emit('update:lastName', $event.target.value)"
        v-text="lastname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] leading-4 font-bold uppercase tracking-[1.5px] text-primary">
        {{ t('guest.form.company') }}
      </span>
      <input
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:text-[14px] placeholder:leading-[22px] placeholder:font-semibold placeholder:pl-4 placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.companyPlaceholder')"
        required
        @input="$emit('update:company', $event.target.value)"
        v-text="company"
      >
    </div>
    <div class="text-right mt-10">
      <button
          :class="[filled ? 'btn-primary' : 'btn-disabled']"
          :disabled="!filled"
          @click="emit('submitForm')"
      >
        {{ t('guest.submit') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
defineProps(['firstname', 'lastname', 'company', 'filled'])
const emit = defineEmits(['submitForm', 'update:modelValue', 'update:firstName', 'update:lastName', 'update:company'])

</script>
