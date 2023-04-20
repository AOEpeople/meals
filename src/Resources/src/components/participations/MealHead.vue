<template>
  <div class="flex h-full w-full flex-col">
    <div class="h-full w-full flex-1 truncate text-center">
      {{ getTitleForLocale(meal) }}
    </div>
    <div
      v-if="meal.variations.length > 0"
      class="flex h-full w-full flex-1 flex-row"
    >
      <div
        v-for="variation in meal.variations"
        :key="variation.mealId"
        class="h-full w-full flex-1 truncate border-r-2 text-center last:border-r-0"
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
  meal: IMealWithVariations
}>();

const languageIsEnglish = computed(() => locale.value === 'en');

function getTitleForLocale(variation: IMealWithVariations) {
  return languageIsEnglish.value ? variation.title.en : variation.title.de;
}
</script>