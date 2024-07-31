<template>
  <label class="flex flex-row">
    <span class="w-[70%] self-center truncate text-left text-xs font-medium text-[#173D7A]">
      {{ locale === 'en' ? getDishBySlug(meal.dishSlug ?? '')?.titleEn : getDishBySlug(meal.dishSlug ?? '')?.titleDe }}
    </span>
    <input
      v-model.number="meal.participationLimit"
      type="number"
      :placeholder="String(meal.participationLimit)"
      class="w-[30%] rounded-full border-2 border-solid border-[#CAD6E1] px-4 py-2 text-center text-[14px] text-[#9CA3AF]"
      data-cy="meal-participation-limit-input"
    />
  </label>
</template>

<script setup lang="ts">
import { type MealDTO } from '@/interfaces/DayDTO';
import { useDishes } from '@/stores/dishesStore';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const { getDishBySlug } = useDishes();

defineProps<{
  meal: MealDTO;
}>();
</script>
