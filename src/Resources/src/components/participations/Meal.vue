<template>
  <table
    v-if="meal"
    class="grow-1 table-fixed border-separate rounded-t-[18px] rounded-b-lg border-0 border-none bg-white"
  >
    <thead class="py-4 shadow-[0_15px_35px_0_#5B788F21]">
      <tr class="w-full">
        <th
          :colspan="meal.variations.length > 0 ? meal.variations.length : 1"
          class="text-primary text-center align-top text-lg"
        >
          {{ languageIsEnglish ? meal.title.en : meal.title.de }}
        </th>
      </tr>
    </thead>
    <tbody v-if="meal.variations.length > 0">
      <tr>
        <td
          v-for="(variation, index) in meal.variations"
          :key="index"
          class="pb-4 font-light"
        >
          {{ getTitleForLocale(variation) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>


<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { IMealWithVariations, type IMealData } from '@/api/getShowParticipations';

const { locale } = useI18n();

defineProps<{
  meal: IMealWithVariations
}>();

const languageIsEnglish = computed(() => locale.value === 'en');

function getTitleForLocale(variation: IMealData) {
  return languageIsEnglish.value ? variation.title.en : variation.title.de;
}

</script>