<template>
  <div>
    <div class="mt-6 grid grid-rows-2">
      <span class="mb-1 mt-auto self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.firstname') }}
      </span>
      <input
        :class="{ 'border-2 border-red': firstNameMissing }"
        class="h-12 rounded-[100px] border-2 border-solid border-[#CAD6E1] bg-white pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.firstnamePlaceholder')"
        required
        @input="$emit('update:firstName', ($event.target as HTMLInputElement).value)"
        v-text="firstname"
      />
    </div>
    <div class="grid grid-rows-2">
      <span class="mb-1 mt-auto self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.lastname') }}
      </span>
      <input
        :class="{ 'border-2 border-red': lastNameMissing }"
        class="h-12 rounded-[100px] border-2 border-solid border-[#CAD6E1] bg-white pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.lastnamePlaceholder')"
        required
        @input="$emit('update:lastName', ($event.target as HTMLInputElement).value)"
        v-text="lastname"
      />
    </div>
    <div class="grid grid-rows-2">
      <span class="mb-1 mt-auto self-center text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
        {{ t('guest.form.company') }}
      </span>
      <input
        :class="{ 'border-2 border-red': companyMissing }"
        class="h-12 rounded-[100px] border-2 border-solid border-[#CAD6E1] bg-white pl-4 placeholder:text-[14px] placeholder:font-semibold placeholder:leading-[22px] placeholder:opacity-50"
        type="text"
        :placeholder="t('guest.form.companyPlaceholder')"
        required
        @input="$emit('update:company', ($event.target as HTMLInputElement).value)"
        v-text="company"
      />
    </div>
    <div class="mt-10 text-right">
      <button
        class="hover:bg-highlight-2 btn-highlight-shadow mx-2 mb-6 mt-4 h-9 items-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold text-white drop-shadow-btn transition-all duration-300 ease-out active:translate-y-0.5 active:shadow-btn-active"
        @click="emit('submitForm')"
      >
        {{ t('guest.submit') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

withDefaults(
  defineProps<{
    firstname?: string;
    lastname?: string;
    company: string;
    filled: boolean;
    firstNameMissing: boolean;
    lastNameMissing: boolean;
    companyMissing: boolean;
  }>(),
  {
    firstname: '',
    lastname: ''
  }
);

const emit = defineEmits(['submitForm', 'update:modelValue', 'update:firstName', 'update:lastName', 'update:company']);
</script>
