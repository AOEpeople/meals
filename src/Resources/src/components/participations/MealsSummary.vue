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
      v-for="(meal, index) in mealsToDisplay"
      :key="`${String((meal as IDish).title)}_${String(index)}`"
      class="h-[60px]"
    >
      <td
        v-if="index < 3"
        class="grid grid-flow-col content-center justify-center gap-2 px-2"
        :class="[mealNameIsEmpty(String((meal as IDish).title)) ? 'h-[60px]' : 'h-[60px] border-b border-solid']"
      >
        <span class="my-auto truncate">
          {{ String((meal as IDish).title) }}
        </span>
        <VeggiIcon
          v-if="(meal as IDish).diet && (meal as IDish).diet !== Diet.MEAT"
          :diet="(meal as IDish).diet"
          class="self-center"
          :class="(meal as IDish).diet === Diet.VEGAN ? 'h-[31px]' : 'h-[36px]'"
          :tooltip-active="false"
          :maxHeight="meal.diet === Diet.VEGAN ? 'max-h-[31px] h-[31px]' : 'max-h-[36px] h-[36px]'"
        />
      </td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import type { IDay, IDish } from '@/api/getMealsNextThreeDays';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import VeggiIcon from '@/components/misc/VeggiIcon.vue';
import { Diet } from '@/enums/Diet';

const { locale } = useI18n();

const props = defineProps<{
  day: IDay;
}>();

const mealsToDisplay = computed(() => {
  const names: IDish[] = [] as IDish[];
  for (const meal of Object.values(locale.value === 'en' ? props.day.en : props.day.de)) {
    names.push(meal as IDish);
  }
  const timesToFill = 3 - names.length;
  for (let i = 0; i < timesToFill; i++) {
    names.push({
      title: '',
      diet: Diet.MEAT
    } as IDish);
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
