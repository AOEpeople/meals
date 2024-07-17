<template>
  <table
    v-if="meal && meal.title.en !== 'Combined Dish'"
    class="grow-1 table-fixed border-collapse rounded-b-lg rounded-t-[18px] border-0 border-none bg-white"
  >
    <thead
      class="shadow-[0_15px_35px_0_#5B788F21]"
      :class="[meal.variations.length > 0 ? '' : 'rounded-b-lg']"
    >
      <tr class="w-full">
        <th
          :colspan="meal.variations.length > 0 ? meal.variations.length : 1"
          class="flex flex-row justify-center gap-2 p-4 align-top text-lg text-primary"
        >
          {{ languageIsEnglish ? meal.title.en : meal.title.de }}
          <VeggiIcon
            v-if="testIf(meal)"
            :diet="meal.diet"
            class="h-[45px] self-center"
          />
        </th>
      </tr>
    </thead>
    <tbody v-if="meal.variations.length > 0">
      <tr
        v-for="(variation, index) in meal.variations"
        :key="index"
      >
        <td
          class="flex flex-row justify-center gap-2 p-4"
          :class="//@ts-ignore
          [meal.variations.length - 1 > index ? 'border-b border-solid' : 'border-none']"
        >
          {{ getTitleForLocale(variation) }}
          <VeggiIcon
            v-if="variation.diet && variation.diet !== Diet.MEAT"
            :diet="variation.diet"
            class="h-[45px] self-center"
          />
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { IMealWithVariations, type IMealData } from '@/api/getShowParticipations';
import VeggiIcon from '@/components/misc/VeggiIcon.vue';
import { Diet } from '@/enums/Diet';

const { locale } = useI18n();

defineProps<{
  meal: IMealWithVariations;
}>();

const languageIsEnglish = computed(() => locale.value === 'en');

function getTitleForLocale(variation: IMealData) {
  return languageIsEnglish.value ? variation.title.en : variation.title.de;
}

function testIf(meal: IMealWithVariations) {
  console.log(`Meal ${meal.title.de} is ${meal.diet} and has ${meal.variations.length} variations!`);
  return meal.variations.length === 0 && meal.diet && meal.diet !== Diet.MEAT;
}
</script>
