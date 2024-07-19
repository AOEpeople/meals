<template>
  <table
    class="size-full table-fixed border-collapse rounded-b-lg rounded-t-[18px] border-0 border-none bg-white p-0 align-top shadow-[0_15px_35px_0_#5B788F21]"
  >
    <tr class="h-[60px]">
      <th class="h-[60px] p-4 text-primary shadow-[0_15px_35px_0_#5B788F21]">
        {{ weekDay }}
      </th>
    </tr>
    <tr
      v-for="(meal, index) in mealNames"
      :key="`${String(meal)}_${String(index)}`"
      class="h-[60px]"
    >
      <td
        v-if="index < 3"
        class="truncate p-4"
        :class="[mealNameIsEmpty(String(meal)) ? 'h-[60px]' : 'h-[60px] border-b border-solid']"
      >
        {{ String(meal) }}
      </td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import { IDay } from '@/api/getMealsNextThreeDays';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const props = defineProps<{
  day: IDay;
}>();

const mealNames = computed(() => {
  const names: string[] = [];
  for (const meal of Object.values(locale.value === 'en' ? props.day.en : props.day.de)) {
    names.push(meal as string);
  }
  const timesToFill = 3 - names.length;
  for (let i = 0; i < timesToFill; i++) {
    names.push('');
  }
  return names;
});

const weekDay = computed(() => {
  return new Date(props.day.date).toLocaleDateString(locale.value, { weekday: 'long' });
});

function mealNameIsEmpty(txt: string) {
  return txt === '' || txt === 'Kombi-Gericht' || txt === 'Combined Dish';
}
</script>
