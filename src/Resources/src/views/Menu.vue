<template>
  <div>{{ `Menu\n-for the Week with ID: ${week}-` }}</div>
  <MenuDay
    v-if="menu.days.length > 0"
    v-model="menu.days[0]"
  />
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { computed, onMounted, reactive } from 'vue';
import MenuDay from '@/components/menu/MenuDay.vue';
import { useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useCategories } from '@/stores/categoriesStore';
import { WeekDTO } from '@/interfaces/DayDTO';
import { Dictionary } from 'types/types';


const { DishesState, fetchDishes } = useDishes();
const { WeeksState, fetchWeeks, getMenuDay } = useWeeks();
const { CategoriesState, fetchCategories } = useCategories();

const props = defineProps<{
  week: string
}>();

const parseWeekId = computed(() => {
  return parseInt(props.week);
});

const menu = reactive<WeekDTO>({
  id: parseWeekId.value,
  days: [],
  notify: false,
  enable: true
});

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

  menu.days[0] = getMenuDay(menu.id, '271');

  progress.finish();
});

</script>