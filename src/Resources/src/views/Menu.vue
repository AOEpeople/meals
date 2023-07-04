<template>
  <form
    class="p-4"
    @submit.prevent="handleSubmit"
  >
    <MenuHeader
      :week="menu"
      :date-range="dateRange"
      :calendar-week="calendarWeek"
    />
    <MenuDay
      v-for="(day, index) in menu.days"
      :key="day.id"
      v-model="menu.days[index]"
      class="mt-4"
    />
    <div class="grid w-full grid-cols-2 items-center">
      <router-link
        to="/weeks"
        class="col-span-1 col-start-1 flex items-center justify-center"
      >
        <CancelButton
          :btn-text="t('button.cancel')"
        />
      </router-link>
      <SubmitButton
        class="col-span-1 col-start-2 m-0"
      />
    </div>
  </form>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { computed, onMounted, reactive, ref } from 'vue';
import MenuDay from '@/components/menu/MenuDay.vue';
import { useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useCategories } from '@/stores/categoriesStore';
import { WeekDTO } from '@/interfaces/DayDTO';
import SubmitButton from '@/components/misc/SubmitButton.vue';
import MenuHeader from '@/components/menu/MenuHeader.vue';
import { useI18n } from 'vue-i18n';
import CancelButton from '@/components/misc/CancelButton.vue';

const { DishesState, fetchDishes } = useDishes();
const { WeeksState, MenuCountState, fetchWeeks, getMenuDay, getWeekById, updateWeek, getDishCountForWeek } = useWeeks();
const { CategoriesState, fetchCategories } = useCategories();
const { t } = useI18n();


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
  enabled: true
});

const calendarWeek = ref(0);

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
  if (Object.keys(MenuCountState.counts).length === 0) {
    await getDishCountForWeek();
  }

  setUpDaysAndEnabled();

  progress.finish();
});

const dateRange = computed(() => {
  if (menu.days[0] && menu.days[menu.days.length - 1]) {
    return [menu.days[0].date.date, menu.days[menu.days.length - 1].date.date];
  }
  return ['', ''];
});

async function handleSubmit() {
  await updateWeek(menu);
  setUpDaysAndEnabled();
}

async function setUpDaysAndEnabled() {
  const week = getWeekById(parseWeekId.value);
  const dayKeys = Object.keys(week.days);
  menu.days = dayKeys.map(dayId => getMenuDay(menu.id, dayId));
  menu.enabled = week.enabled;
  calendarWeek.value = week.calendarWeek;
}
</script>