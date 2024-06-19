<template>
  <div class="flex size-full flex-col px-2">
    <div class="meal-header-test size-full flex-1 truncate text-center">
      {{ getTitleForLocale(meal) }}
    </div>
    <div
      v-if="meal.variations.length > 0"
      class="flex size-full flex-1 flex-row pt-2"
    >
      <div
        v-for="variation in meal.variations"
        :key="variation.mealId"
        class="meal-variations-test size-full flex-1 truncate border-l-0 text-center first:pr-[10px] last:border-l"
      >
        {{ getTitleForLocale(variation) }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IMealWithVariations } from '@/api/getShowParticipations';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

defineProps<{
  meal: IMealWithVariations;
}>();

const languageIsEnglish = computed(() => locale.value === 'en');

function getTitleForLocale(variation: IMealWithVariations) {
  if (languageIsEnglish.value === true) {
    return variation.title.en === 'Combined Dish' ? 'Combi' : variation.title.en;
  }
  return variation.title.de === 'Kombi-Gericht' ? 'Kombi' : variation.title.de;
}
</script>
