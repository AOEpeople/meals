<template>
  <div>
    <div class="mt-6 grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.firstname') }}
      </span>
      <input
        :class="{'outline-2 outline-red': firstNameMissing}"
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.firstnamePlaceholder')"
        required
        @input="$emit('update:firstName', $event.target.value)"
        v-text="firstname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.lastname') }}
      </span>
      <input
        :class="{'outline-2 outline-red':lastNameMissing}"
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.lastnamePlaceholder')"
        required
        @input="$emit('update:lastName', $event.target.value)"
        v-text="lastname"
      >
    </div>
    <div class="grid grid-rows-2">
      <span class="mt-auto mb-1 self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.company') }}
      </span>
      <input
        :class="{'outline-2 outline-red': companyMissing}"
        class="h-12 rounded-[100px] border-[2px] border-solid border-[#CAD6E1] bg-white placeholder:pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.companyPlaceholder')"
        required
        @input="$emit('update:company', $event.target.value)"
        v-text="company"
      >
    </div>
    <div class="mt-10 text-right">
      <button
        class="shadow-highlight-1 hover:bg-highlight-2 active:shadow-highlight-1 mx-2 mt-4 mb-6 h-9 items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-medium shadow-btn drop-shadow-btn transition-all duration-300 ease-out active:translate-y-0.5 active:shadow-btn-active"
        @click="emit('submitForm')"
      >
        {{ t('guest.submit') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
defineProps(['firstname', 'lastname', 'company', 'filled', 'firstNameMissing', 'lastNameMissing', 'companyMissing'])
const emit = defineEmits(['submitForm', 'update:modelValue', 'update:firstName', 'update:lastName', 'update:company'])

</script>
