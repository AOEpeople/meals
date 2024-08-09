<template>
  <div
    class="group grid grid-cols-[24px_minmax(0,1fr)_58px] grid-rows-3 rounded-lg border-0 border-none bg-white text-center align-middle shadow-day sm:grid-cols-[24px_minmax(0,1fr)_72px]"
  >
    <div
      class="col-start-1 row-start-1 grid w-[24px] grid-rows-[24px_minmax(0,1fr)_24px] justify-center rounded-l-lg bg-primary-2 py-1"
      :class="[selectedEventOne ? 'row-span-4' : 'row-span-3']"
    >
      <Popover
        :translate-x-min="'0%'"
        :translate-x-max="'-5%'"
      >
        <template #button>
          <UserIcon
            class="row-start-1 size-5 cursor-pointer"
            :class="participationLimitNotZero ? 'text-highlight' : 'text-white'"
          />
        </template>
        <template #panel="{ close }">
          <MenuParticipationPanel
            :meals="modelValue.meals"
            @close-panel="close()"
          />
        </template>
      </Popover>
      <span
        class="row-start-2 rotate-180 text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]"
      >
        {{ translateWeekdayWithoutRef(modelValue.date, locale) }}
      </span>
      <MenuLockDatePicker
        :lock-date="modelValue.lockDate"
        :is-standard-lock-date="isStandardLockDate"
        class="row-start-3"
      />
    </div>
    <MenuInput
      v-if="selectedDishOne"
      v-model="selectedDishOne"
      class="col-start-2 row-span-1 row-start-1 border-b px-2 pb-2 pt-4 md:px-4"
    />
    <MenuInput
      v-if="selectedDishTwo"
      v-model="selectedDishTwo"
      class="col-start-2 row-span-1 row-start-2 px-2 pb-4 pt-2 md:px-4"
    />
    <EventInput
      v-model="selectedEventOne"
      class="col-start-2 row-span-1 row-start-3 border-b border-t-[3px] px-2 py-[12px] md:px-4"
    />
    <EventInput
      v-if="selectedEventOne"
      v-model="selectedEventTwo"
      class="col-start-2 row-span-1 row-start-4 px-2 py-[12px] md:px-4"
    />
    <div class="col-start-3 row-span-4 row-start-1 grid items-center rounded-r-lg border-l-2 sm:w-[72px]">
      <Switch
        :sr="t('menu.enableDay')"
        :initial="modelValue.enabled"
        class="m-auto"
        @toggle="(value) => (modelValue.enabled = value)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import MenuInput from '@/components/menu/MenuInput.vue';
import { type Ref, computed, onMounted, ref, watch } from 'vue';
import { type Dish } from '@/stores/dishesStore';
import type { MealDTO, DayDTO } from '@/interfaces/DayDTO';
import { useDishes } from '@/stores/dishesStore';
import { translateWeekdayWithoutRef } from '@/tools/localeHelper';
import { useI18n } from 'vue-i18n';
import Switch from '@/components/misc/Switch.vue';
import { UserIcon } from '@heroicons/vue/solid';
import Popover from '../misc/Popover.vue';
import MenuParticipationPanel from './MenuParticipationPanel.vue';
import MenuLockDatePicker from './MenuLockDatePicker.vue';
import EventInput from './EventInput.vue';
import { type Event } from '@/stores/eventsStore';
import { type DateTime } from '@/api/getDashboardData';
import { type Dictionary } from '@/types/types';

const { getDishArrayBySlugs, getDishBySlug } = useDishes();
const { locale, t } = useI18n();

const props = defineProps<{
  modelValue: DayDTO;
  lockDates: Dictionary<DateTime> | null;
}>();

const emit = defineEmits(['update:modelValue']);

const selectedDishOne = ref<Dish[] | null>(null);
const selectedDishTwo = ref<Dish[] | null>(null);
const selectedEventOne = ref<Event | null>(null);
const selectedEventTwo = ref<Event | null>(null);
const participationLimitNotZero = computed(() => {
  let limitNotZero = false;
  (Object.values(props.modelValue.meals) as MealDTO[][]).forEach((meals) => {
    meals.forEach((meal) => {
      if (meal.participationLimit !== 0) {
        limitNotZero = true;
      }
    });
  });
  return limitNotZero;
});

const mealKeys = computed(() => {
  const combiDish = getDishBySlug('combined-dish');
  if (combiDish !== null) {
    return Object.keys(props.modelValue.meals).filter((key) => key !== combiDish.id.toString());
  }
  return Object.keys(props.modelValue.meals);
});

const selectedDishes = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const isStandardLockDate = computed(() => {
  const dayIds = props.lockDates !== null && props.lockDates !== undefined ? Object.keys(props.lockDates) : [];
  if (
    props.lockDates !== null &&
    dayIds.length > 0 &&
    dayIds.map((id) => String(id)).includes(String(props.modelValue.id))
  ) {
    return props.lockDates[props.modelValue.id].date === props.modelValue.lockDate.date;
  }
  return true;
});

watch(selectedDishOne, () => {
  // meals that already exist in the backend can be changed to fit the new dishes
  const mealIds = selectedDishes.value.meals[mealKeys.value[0]].map((meal: MealDTO) => meal.mealId);
  // slugs of the dishes that were selected
  const dishSlugs = getSlugsFromSelectedDishes(selectedDishOne);
  // set the new dishes
  selectedDishes.value.meals[mealKeys.value[0]] = dishSlugs.map((dishSlug) => {
    return {
      dishSlug: dishSlug,
      mealId: mealIds.length > 0 ? mealIds.shift() : null,
      participationLimit: getParticipationLimitFromModel(dishSlug, mealKeys.value[0])
    } as MealDTO;
  });
});

watch(selectedDishTwo, () => {
  // meals that already exist in the backend can be changed to fit the new dishes
  const mealIds = selectedDishes.value.meals[mealKeys.value[1]].map((meal: MealDTO) => meal.mealId);
  // slugs of the dishes that were selected
  const dishSlugs = getSlugsFromSelectedDishes(selectedDishTwo);
  // set the new dishes
  selectedDishes.value.meals[mealKeys.value[1]] = dishSlugs.map((dishSlug) => {
    return {
      dishSlug: dishSlug,
      mealId: mealIds.length > 0 ? mealIds.shift() : null,
      participationLimit: getParticipationLimitFromModel(dishSlug, mealKeys.value[1])
    } as MealDTO;
  });
});

watch(selectedEventOne, () => {
  try {
    const firstKey = Object.keys(props.modelValue.events)[0] ?? selectedEventOne.value?.id;

    if (selectedEventOne.value) {
      selectedDishes.value.events[firstKey] = {
        eventId: selectedEventOne.value.id,
        eventSlug: selectedEventOne.value.slug,
        eventTitle: selectedEventOne.value.title,
        isPublic: selectedEventOne.value.public
      };
    } else if (firstKey) {
      selectedDishes.value.events[firstKey].eventId = null;
    }
  } catch (error) {
    console.error('Fehler: ', error);
  }
});

watch(selectedEventTwo, () => {
  try {
    const secondKey = Object.keys(props.modelValue.events)[1] ?? selectedEventTwo.value?.id;

    if (selectedEventTwo.value) {
      console.log('Second Event wird eingetragen')
      console.log(selectedEventTwo.value);
      selectedDishes.value.events[secondKey] = {
        eventId: selectedEventTwo.value.id,
        eventSlug: selectedEventTwo.value.slug,
        eventTitle: selectedEventTwo.value.title,
        isPublic: selectedEventTwo.value.public
      };
      console.log(Object.keys(props.modelValue.events)[1])
    } else if (secondKey) {
      selectedDishes.value.events[secondKey].eventId = null;
    }
  } catch (error) {
    console.error('Fehler: ', error);
  }
});


onMounted(() => {
  console.log('onMounted Menu Day');
  // get mealKeys
  selectedDishOne.value = getDishArrayBySlugs(
    props.modelValue.meals[mealKeys.value[0]]
      .map((meal: MealDTO) => meal.dishSlug)
      .filter((slug) => slug !== null) as string[]
  );
  selectedDishTwo.value = getDishArrayBySlugs(
    props.modelValue.meals[mealKeys.value[1]]
      .map((meal: MealDTO) => meal.dishSlug)
      .filter((slug) => slug !== null) as string[]
  );

  try {
    console.log('try Block');
    const firstKey = Object.keys(props.modelValue.events)[0];
    const secondKey = Object.keys(props.modelValue.events)[1];
    console.log('First key ' + firstKey);

    if (props.modelValue.events[firstKey]) {
      selectedEventOne.value = {
        id: props.modelValue.events[firstKey].eventId as number,
        slug: props.modelValue.events[firstKey].eventSlug as string,
        title: props.modelValue.events[firstKey].eventTitle as string,
        public: props.modelValue.events[firstKey].isPublic as boolean,
      };
    }
    if (props.modelValue.events[secondKey]) {
      selectedEventTwo.value = {
        id: props.modelValue.events[secondKey].eventId as number,
        slug: props.modelValue.events[secondKey].eventSlug as string,
        title: props.modelValue.events[secondKey].eventTitle as string,
        public: props.modelValue.events[secondKey].isPublic as boolean,
      };
    }
  } catch (error) {
    console.error('Fehler beim Laden der Events: ', error);
  }
});

/**
 * Extract the slugs from the selected dishes. Returns the slugs of variations if there are selected variations.
 * Otherwise the slug of the parent dish is returned.
 * @param selectedDishRef Ref contining the selected dishes
 */
function getSlugsFromSelectedDishes(selectedDishRef: Ref<Dish[] | null>) {
  const meals: string[] = [];

  if (selectedDishRef.value !== null && selectedDishRef.value !== undefined && selectedDishRef.value.length === 1) {
    selectedDishRef.value.forEach((dish) => {
      if (dish !== null && dish !== undefined && dish.parentId === null) {
        meals.push(dish.slug);
      }
    });
  } else if (
    selectedDishRef.value !== null &&
    selectedDishRef.value !== undefined &&
    selectedDishRef.value.length > 1
  ) {
    selectedDishRef.value.forEach((dish) => {
      if (dish !== null && dish !== undefined && dish.parentId !== null) {
        meals.push(dish.slug);
      }
    });
  }

  return meals;
}

function getParticipationLimitFromModel(dishSlug: string, key: string) {
  for (const meal of selectedDishes.value.meals[key]) {
    if (meal.dishSlug === dishSlug) {
      return meal.participationLimit;
    }
  }
  return 0;
}
</script>
