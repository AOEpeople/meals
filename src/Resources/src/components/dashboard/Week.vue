<template>
  <div class="text-center">
    <h2 class="m-0">{{ t('dashboard.' + index) }}</h2>
    <p class="description text-primary">{{t('Mon')}} 04.10. - {{ t('Fri') }} 08.10.</p>
  </div>
  <div class="grid" id="weekly-menu">
    <Day v-for="day in week.days"
         :day="day"
         :key="day.id"
         class="mb-[2.5rem]"
    />
  </div>
</template>

<script setup>
import Day from '@/components/dashboard/Day.vue'
import { useI18n } from "vue-i18n";
const props = defineProps(['week', 'index']);

const { t } = useI18n();
</script>
