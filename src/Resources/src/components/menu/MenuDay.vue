<template>
  <div
    class="day-shadow group grid grid-cols-[24px_minmax(0,1fr)_72px] grid-rows-2 rounded-lg border-0 border-none bg-white text-center align-middle"
  >
    <div
      class="col-start-1 row-span-2 row-start-1 grid w-[24px] grid-rows-[24px_minmax(0,1fr)_24px] justify-center rounded-l-lg bg-[#1c5298] py-1"
    >
      <Popover>
        <template #button="{ open }">
          <UserIcon
            class="row-start-1 h-5 w-5 cursor-pointer text-white"
          />
        </template>
        <template #panel="{ close }">
          <MenuParticipationPanel
            :meals="modelValue.meals"
            :close="close"
          />
        </template>
      </Popover>
      <span class="row-start-2 rotate-180 text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]">
        {{ translateWeekdayWithoutRef(modelValue.date, locale) }}
      </span>
      <MenuLockDatePicker
        :lock-date="modelValue.lockDate"
        class="row-start-3"
      />
    </div>
    <MenuInput
      v-if="selectedDishOne"
      v-model="selectedDishOne"
      class="col-start-2 row-span-1 row-start-1 border-b-[1px] px-4 pt-4"
    />
    <MenuInput
      v-if="selectedDishTwo"
      v-model="selectedDishTwo"
      class="col-start-2 row-span-1 row-start-2 px-4 pb-4 pt-2"
    />
    <div
      class="col-start-3 row-span-2 row-start-1 grid w-[72px] items-center rounded-r-lg border-l-2"
    >
      <Switch
        :sr="t('menu.enableDay')"
        :initial="modelValue.enabled"
        class="m-auto"
        @toggle="(value) => modelValue.enabled = value"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import MenuInput from '@/components/menu/MenuInput.vue';
import { Ref, computed, onMounted, ref, watch } from 'vue';
import { Dish } from '@/stores/dishesStore';
import { MealDTO, DayDTO } from '@/interfaces/DayDTO';
import { useDishes } from '@/stores/dishesStore';
import { translateWeekdayWithoutRef } from '@/tools/localeHelper';
import { useI18n } from 'vue-i18n';
import Switch from "@/components/misc/Switch.vue"
import { UserIcon, CalendarIcon } from '@heroicons/vue/solid';
import Popover from '../misc/Popover.vue';
import MenuParticipationPanel from './MenuParticipationPanel.vue';
import MenuLockDatePicker from './MenuLockDatePicker.vue';

const { getDishArrayBySlugs } = useDishes();
const { locale, t } = useI18n();

const props = defineProps<{
  modelValue: DayDTO;
}>();

const emit = defineEmits(['update:modelValue']);

const selectedDishOne = ref<Dish[] | null>(null);
const selectedDishTwo = ref<Dish[] | null>(null);

let mealKeys: string[] = [];

const selectedDishes = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

watch(
  selectedDishOne,
  () => {
    // meals that already exist in the backend can be changed to fit the new dishes
    const mealIds = selectedDishes.value.meals[mealKeys[0]].map((meal: MealDTO) => meal.mealId);
    // slugs of the dishes that were selected
    const dishSlugs = getSlugsFromSelectedDishes(selectedDishOne);
    // set the new dishes
    selectedDishes.value.meals[mealKeys[0]] = dishSlugs.map(dishSlug => {
      return {
        dishSlug: dishSlug,
        mealId: mealIds.length > 0 ? mealIds.pop() : null,
        participationLimit: getParticipationLimitFromModel(dishSlug, mealKeys[0])
      };
    });
});

watch(
  selectedDishTwo,
  () => {
    // meals that already exist in the backend can be changed to fit the new dishes
    const mealIds = selectedDishes.value.meals[mealKeys[1]].map((meal: MealDTO) => meal.mealId);
    // slugs of the dishes that were selected
    const dishSlugs = getSlugsFromSelectedDishes(selectedDishTwo);
    // set the new dishes
    selectedDishes.value.meals[mealKeys[1]] = dishSlugs.map(dishSlug => {
      return {
        dishSlug: dishSlug,
        mealId: mealIds.length > 0 ? mealIds.pop() : null,
        participationLimit: getParticipationLimitFromModel(dishSlug, mealKeys[0])
      };
    });
});

onMounted(() => {
  // get mealKeys
  mealKeys = Object.keys(props.modelValue.meals)
  selectedDishOne.value = getDishArrayBySlugs(props.modelValue.meals[mealKeys[0]].map((meal: MealDTO) => meal.dishSlug));
  selectedDishTwo.value = getDishArrayBySlugs(props.modelValue.meals[mealKeys[1]].map((meal: MealDTO) => meal.dishSlug));
});

/**
 * Extract the slugs from the selected dishes. Returns the slugs of variations if there are selected variations.
 * Otherwise the slug of the parent dish is returned.
 * @param selectedDishRef Ref contining the selected dishes
 */
function getSlugsFromSelectedDishes(selectedDishRef: Ref<Dish[] | null>) {
  const meals: string[] = [];

  if (selectedDishRef.value && selectedDishRef.value.length === 1) {
    selectedDishRef.value.forEach(dish => {
      if (dish && dish.parentId === null) {
        meals.push(dish.slug);
      }
    });
  } else if (selectedDishRef.value && selectedDishRef.value.length > 1) {
    selectedDishRef.value.forEach(dish => {
      if (dish && dish.parentId !== null) {
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