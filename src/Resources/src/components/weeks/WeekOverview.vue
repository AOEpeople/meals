<template>
  <div>
    <h4>{{ `Woche #${week.calendarWeek}` }}</h4>
    <h5>{{ `${dateRange[0]} - ${dateRange[1]}` }}</h5>
  </div>
</template>

<script setup lang="ts">
import { Week } from '@/stores/weeksStore';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useWeeks } from '@/stores/weeksStore';

const { getDateRangeOfWeek } = useWeeks();
const { t, locale } = useI18n();

const props = defineProps<{
  week: Week,
}>();

const dateRange = computed(() => {
  return (
    getDateRangeOfWeek(props.week.calendarWeek, props.week.year)
    .map(date => date.toLocaleDateString(locale.value, { day: 'numeric', month: 'numeric' }))
  );
});
</script>