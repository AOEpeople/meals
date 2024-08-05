<template>
  <table
    v-if="meal && meal.title.en !== 'Combined Dish'"
    class="w-full table-fixed border-collapse rounded-b-lg rounded-t-[18px] border-0 border-none bg-white"
  >
    <thead
      class="shadow-[0_15px_35px_0_#5B788F21] w-full"
      :class="[meal.variations.length > 0 ? '' : 'rounded-b-lg']"
    >
      <tr class="w-full">
        <th
          :colspan="meal.variations.length > 0 ? meal.variations.length : 1"
          class="grid w-full grid-flow-col justify-center gap-2 p-4 align-top break-all text-lg text-primary"
        >
          {{ languageIsEnglish ? meal.title.en : meal.title.de }}
          <VeggiIcon
            v-if="meal.variations.length === 0 && meal.diet && meal.diet !== Diet.MEAT"
            :diet="meal.diet"
            class="self-center"
            :class="meal.diet === Diet.VEGAN ? 'h-[45px]' : 'h-[42px]'"
            :tooltip-active="false"
            :maxHeight="meal.diet === Diet.VEGAN ? 'max-h-[45px]' : 'max-h-[42px]'"
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
          class="grid min-h-[60px] grid-flow-col content-center justify-center gap-2 p-2"
          :class="//@ts-ignore
          [meal.variations.length - 1 > index ? 'border-b border-solid' : 'border-none']"
        >
          <span class="my-auto break-all">
            {{ getTitleForLocale(variation) }}
          </span>
          <VeggiIcon
            v-if="variation.diet && variation.diet !== Diet.MEAT"
            :diet="variation.diet"
            class="self-center"
            :class="variation.diet === Diet.VEGAN ? 'h-[31px]' : 'h-[36px]'"
            :tooltip-active="false"
            :maxHeight="meal.diet === Diet.VEGAN ? 'max-h-[31px] h-[31px]' : 'max-h-[36px] h-[36px]'"
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
</script>
