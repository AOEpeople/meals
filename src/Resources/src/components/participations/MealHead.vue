<template>
  <div class="flex h-full w-full flex-col px-2">
    <div class="h-full w-full flex-1 truncate text-center">
      {{ getTitleForLocale(meal) }}
    </div>
    <div
      v-if="meal.variations.length > 0"
      class="flex h-full w-full flex-1 flex-row pt-2"
    >
      <div
        v-for="variation in meal.variations"
        :key="variation.mealId"
        class="h-full w-full flex-1 truncate border-l-0 text-center first:pr-[10px] last:border-l-[1px]"
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