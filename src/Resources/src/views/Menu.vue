<template>
  <form
    class="p-4"
    @submit.prevent="handleSubmit"
  >
    <div class="w-full text-center">
      {{ `Menu\n-for the Week with ID: ${week}-` }}
    </div>
    <MenuDay
      v-for="(day, index) in menu.days"
      :key="day.id"
      v-model="menu.days[index]"
      class="mt-4"
    />
    <SubmitButton
      class="mt-4"
    />
  </form>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { computed, onMounted, reactive } from 'vue';
import MenuDay from '@/components/menu/MenuDay.vue';
import { useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useCategories } from '@/stores/categoriesStore';
import { WeekDTO } from '@/interfaces/DayDTO';
import SubmitButton from '@/components/misc/SubmitButton.vue';

const { DishesState, fetchDishes } = useDishes();
const { WeeksState, fetchWeeks, getMenuDay, getWeekById, updateWeek } = useWeeks();
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

  setUpDays();
  progress.finish();
});

async function handleSubmit() {
  menu.days.forEach(day => console.log(`Day #${day.id} is enabled: ${day.enabled}`));
  await updateWeek(menu);
  setUpDays();
}

function setUpDays() {
  const dayKeys = Object.keys(getWeekById(parseWeekId.value).days);
  menu.days = dayKeys.map(dayId => getMenuDay(menu.id, dayId));
}
</script>