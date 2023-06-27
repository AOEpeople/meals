<template>
  <form
    class="bg-white p-4"
    @submit.prevent="handleSubmit"
  >
    <div class="w-full text-center">
      {{ `Menu\n-for the Week with ID: ${week}-` }}
    </div>
    <MenuDay
      v-for="(day, index) in menu.days"
      :key="day.id"
      v-model="menu.days[index]"
    />
    <SubmitButton />
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
const { WeeksState, fetchWeeks, getMenuDay, getWeekById } = useWeeks();
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
  // menu.days[0] = getMenuDay(menu.id, Object.keys(getWeekById(parseWeekId.value).days)[0]);
  progress.finish();
});

function handleSubmit() {
  console.log('submit');
}

function setUpDays() {
  const dayKeys = Object.keys(getWeekById(menu.id).days);
  // keys seem to not be needed -> array
  for (const dayId in dayKeys) {
    console.log(dayId)
    menu.days.push(getMenuDay(menu.id, dayId));
  }
}
</script>