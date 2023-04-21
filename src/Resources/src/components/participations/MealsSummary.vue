<template>
  <table class="h-full w-full table-fixed border-separate rounded-t-[18px] rounded-b-lg border-0 border-none bg-white align-top shadow-[0_15px_35px_0_#5B788F21]">
    <tr>
      <th class="text-primary p-4 shadow-[0_15px_35px_0_#5B788F21]">
        {{ weekDay }}
      </th>
    </tr>
    <tr
      v-for="meal in mealNames"
      :key="meal"
    >
      <td
        class="truncate p-4"
        :class="[mealNameIsEmpty(meal) ? 'h-[60px]' : 'border-b-[1px] border-solid']"
      >
        {{ meal }}
      </td>
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
  for(let i = 0; i < (3 - names.length); i++) {
    names.push("");
  }
  return names;
})

const weekDay = computed(() => {
  return (new Date(props.day.date.date)).toLocaleDateString(locale.value, { weekday: 'long' });
});

function mealNameIsEmpty(txt: string) {
  return txt === "";
}
</script>