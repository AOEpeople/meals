<template>
  <table>
    <tr>
      <th>{{ weekDay }}</th>
    </tr>
    <tr
      v-for="meal in mealNames"
      :key="meal"
    >
      <td>{{ meal }}</td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import { Day } from '@/api/getDashboardData';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const props = defineProps<{
  day: Day
}>();

const mealNames = computed(() => {
  const names: string[] = [];
  for(const meal of Object.values(props.day.meals)) {
    locale.value === 'en' ? names.push(meal.title.en) : names.push(meal.title.de);
  }
  return names;
})

const weekDay = computed(() => {
  return (new Date(props.day.date.date)).toLocaleDateString(locale.value, { weekday: 'long' });
});
</script>