<template>
  <div class="grid grid-cols-4 gap-2">
    <WeekOverview
      v-for="week in WeeksState.weeks"
      :key="week.calendarWeek"
      :week="(week as Week)"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Week, useWeeks } from '@/stores/weeksStore';
import WeekOverview from './WeekOverview.vue';

const { t } = useI18n();
const { WeeksState, fetchWeeks } = useWeeks();

onMounted(async () => {
  await fetchWeeks();
});
</script>