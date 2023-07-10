<template>
  <h2 class="px-2 xl:px-0">
    {{ t('menu.list') }}
  </h2>
  <div class="grid grid-cols-1 gap-4 px-2 min-[380px]:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:px-0">
    <WeekOverview
      v-for="week in WeeksState.weeks"
      :key="week.calendarWeek"
      :week="// @ts-ignore
        (week as Week)"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Week, useWeeks } from '@/stores/weeksStore';
import WeekOverview from '@/components/weeks/WeekOverview.vue';
import { useProgress } from '@marcoschulte/vue3-progress';

const { t } = useI18n();
const { WeeksState, fetchWeeks } = useWeeks();

onMounted(async () => {
  const progress = useProgress().start();
  await fetchWeeks();
  progress.finish();
});
</script>