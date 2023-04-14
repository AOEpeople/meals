<template>
  <table
    v-if="meal"
    class="grow-1 table-fixed bg-white"
  >
    <thead>
      <tr>
        <th
          :colspan="meal.variations.length > 0 ? meal.variations.length : 1"
          class="p-1 text-center align-top"
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
          class="font-light"
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