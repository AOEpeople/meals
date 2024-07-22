<template>
  <form
    class="p-2 md:p-4"
    @submit.prevent="handleSubmit"
  >
    <MenuHeader
      :week="menu"
      :date-range="dateRange"
      :calendar-week="calendarWeek"
      :create="create !== null && create === 'create'"
    />
    <MenuDay
      v-for="(day, index) in menu.days"
      :key="Object.keys(day.meals).join()"
      v-model="menu.days[index]"
      :lockDates="lockDates"
      class="mt-4"
    />
    <LoadingSpinner :loaded="loaded" />
    <div class="mt-4 grid w-full grid-cols-2 items-center">
      <div class="col-span-1 col-start-1 flex items-center justify-center">
        <CancelButton
          :btn-text="t('button.cancel')"
          class="cursor-pointer"
          @click="router.push('/weeks')"
        />
      </div>
      <SubmitButton class="col-span-1 col-start-2 m-0" />
    </div>
  </form>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import MenuDay from '@/components/menu/MenuDay.vue';
import { useDishes } from '@/stores/dishesStore';
import { useWeeks } from '@/stores/weeksStore';
import { useCategories } from '@/stores/categoriesStore';
import { type WeekDTO } from '@/interfaces/DayDTO';
import SubmitButton from '@/components/misc/SubmitButton.vue';
import MenuHeader from '@/components/menu/MenuHeader.vue';
import { useI18n } from 'vue-i18n';
import CancelButton from '@/components/misc/CancelButton.vue';
import { useRouter } from 'vue-router';
import LoadingSpinner from '@/components/misc/LoadingSpinner.vue';
import { useEvents } from '@/stores/eventsStore';

const { DishesState, fetchDishes } = useDishes();
const {
  WeeksState,
  MenuCountState,
  lockDates,
  fetchWeeks,
  getMenuDay,
  getWeekById,
  updateWeek,
  getDishCountForWeek,
  getWeekByCalendarWeek,
  getDateRangeOfWeek,
  createWeek,
  fetchLockDatesForWeek
} = useWeeks();
const { CategoriesState, fetchCategories } = useCategories();
const { t } = useI18n();
const router = useRouter();
const { fetchEvents } = useEvents();

const props = withDefaults(
  defineProps<{
    week: string;
    create?: string | null;
  }>(),
  {
    create: null
  }
);

const parseWeekId = ref(parseInt(props.week));
const loaded = ref(false);

watch(
  () => props.week,
  () => {
    parseWeekId.value = parseInt(props.week);
    menu.id = parseWeekId.value;
  }
);

const menu = reactive<WeekDTO>({
  id: parseWeekId.value,
  days: [],
  notify: false,
  enabled: true
});

const calendarWeek = ref(0);

onMounted(async () => {
  const progress = useProgress().start();
  parseWeekId.value = parseInt(props.week);

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
  await fetchEvents();

  if (props.create === null) {
    await fetchLockDatesForWeek(parseWeekId.value);
  }

  setUpDaysAndEnabled();

  loaded.value = true;
  progress.finish();
});

const dateRange = computed(() => {
  return getDateRangeOfWeek(calendarWeek.value, new Date().getFullYear());
});

async function handleSubmit() {
  if (props.create === null || props.create === undefined || props.create !== 'create') {
    await updateWeek(menu);
    setUpDaysAndEnabled();
  } else {
    const weekId = await createWeek(getWeekByCalendarWeek(calendarWeek.value).year, calendarWeek.value, menu);
    if (typeof weekId === 'number') {
      parseWeekId.value = weekId;
      await router.push({ name: 'Menu', params: { week: weekId, create: null }, force: true, replace: true });
      await setUpDaysAndEnabled();
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function setUpDaysAndEnabled() {
  const week =
    props.create === null || props.create !== 'create'
      ? getWeekById(parseWeekId.value)
      : getWeekByCalendarWeek(parseWeekId.value);
  const dayKeys = Object.keys(week.days);
  menu.days = dayKeys.map((dayId) => {
    if (props.create === null || props.create !== 'create') {
      return getMenuDay(dayId, menu.id);
    } else {
      return getMenuDay(dayId, null, parseWeekId.value);
    }
  });
  menu.enabled = week.enabled;
  calendarWeek.value = week.calendarWeek;
}
</script>
