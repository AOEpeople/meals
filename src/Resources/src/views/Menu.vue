<template>
  <div>{{ `Menu\n-for the Week with ID: ${week}-` }}</div>
  <MenuDay />
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted } from 'vue';
import MenuDay from '@/components/menu/MenuDay.vue';
import { useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useCategories } from '@/stores/categoriesStore';

const { DishesState, fetchDishes } = useDishes();
const { WeeksState, fetchWeeks } = useWeeks();
const { CategoriesState, fetchCategories } = useCategories();

defineProps<{
  week: number
}>();

onMounted(async () => {
  const progress = useProgress().start();

  if (DishesState.dishes.length === 0) {
    await fetchDishes();
  }
  if (CategoriesState.categories.length === 0) {
    await fetchCategories();
  }
  if (WeeksState.weeks.length === 0) {
    await fetchWeeks();
  }

  progress.finish();
});
</script>