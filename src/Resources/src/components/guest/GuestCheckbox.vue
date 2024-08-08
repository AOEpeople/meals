<template>
  <span
    data-cy="guest-checkbox"
    class="size-[30px] cursor-pointer rounded-md border-[0.5px] border-[#ABABAB] xl:size-[20px]"
    :class="isChecked ? 'bg-primary-3' : ''"
    @click="handle"
  >
    <CheckIcon
      v-if="isChecked"
      class="relative left-[10%] top-[10%] size-4/5 text-white"
    />
  </span>
  <CombiModal
    v-if="isCombiBox"
    :open="open"
    :meals="meals"
    @closeCombiModal="handleCombiModal"
  />
</template>

<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/solid';
import { computed, ref } from 'vue';
import useEventsBus from '@/tools/eventBus';
import CombiModal from '@/components/dashboard/CombiModal.vue';
import { type Meal } from '@/api/getDashboardData';
import { type Dictionary } from '@/types/types';

const props = defineProps<{
  meals: Dictionary<Meal>;
  mealId: number | string;
  chosenMeals: string[];
}>();

const open = ref(false);
const { emit } = useEventsBus();

const isCombiBox = (props.meals[props.mealId] as Meal).dishSlug === 'combined-dish';

let hasVariations = false;
Object.values(props.meals).forEach((meal) => ((meal as Meal).variations ? (hasVariations = true) : ''));

const isChecked = computed(() => props.chosenMeals.includes(props.mealId.toString() ?? ''));

function handle() {
  // Is a combi meal
  if (isCombiBox && (isMealBookable() || isChecked.value)) {
    // has variations
    if (hasVariations && isChecked.value === false) {
      open.value = true;
    } else {
      const combiDishes = Object.values(props.meals)
        .filter((meal) => (meal as Meal).dishSlug !== 'combined-dish')
        .map((meal) => (meal as Meal).dishSlug);

      emit('guestChosenCombi', combiDishes);
      emit('guestChosenMeals', props.mealId);
    }
  } else if (isMealBookable() || isChecked.value) {
    emit('guestChosenMeals', props.mealId);
  }
}

function handleCombiModal(dishes: string[]) {
  if (dishes !== undefined) {
    emit('guestChosenCombi', dishes);
    emit('guestChosenMeals', props.mealId);
  }
  open.value = false;
}

function isMealBookable() {
  const meal = props.meals[props.mealId];

  if (meal.dishSlug === 'combined-dish') {
    return isCombiBookable(meal);
  } else {
    return isDishBookable(meal);
  }
}

function isCombiBookable(meal: Meal): boolean {
  return !meal.reachedLimit && getBookableCombiMealIds().length >= 2;
}

function getBookableCombiMealIds() {
  const combiMeals: number[] = [];
  Object.values(props.meals)
    .filter((meal) => (meal as Meal).dishSlug !== 'combined-dish')
    .flatMap((combi) => {
      if (combi.variations !== null && combi.variations !== undefined) {
        return Object.values(combi.variations).map((variation) => getBookableObject(variation));
      }
      return getBookableObject(combi);
    })
    .filter((combi) => combi.bookable)
    .forEach((combi) => {
      if (!combiMeals.includes(combi.parent)) {
        return combiMeals.push(combi.parent);
      }
    });

  return combiMeals;
}

function getBookableObject(combi: Meal) {
  return {
    parent: combi.parentId ?? Math.random(),
    bookable: isDishBookable(combi, 0.5)
  };
}

function isDishBookable(meal: Meal, mealValue: number = 1): boolean {
  return (
    !meal.reachedLimit &&
    (meal.limit === 0 || meal.limit === null || meal.limit >= (meal.participations ?? 0) + mealValue)
  );
}
</script>
