<template>
  <div class="text-center">
    <h2 class="m-0">{{ t(week.label) }}</h2>
    <p class="description text-primary">{{t('Mon')}} 04.10. - {{ t('Fri') }} 08.10.</p>
  </div>
  <div class="grid" id="weekly-menu">
    <Day v-for="day in props.week.value"
         :day="day"
         :key="day.name"
         class="mb-[2.5rem]"
    />
  </div>
</template>

<script setup>
import Day from '@/components/dashboard/Day.vue'
import { useI18n } from "vue-i18n";
const props = defineProps(['week']);
const { t } = useI18n();

</script>
