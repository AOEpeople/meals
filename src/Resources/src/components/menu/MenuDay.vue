<template>
  <div
    class="day-shadow group grid cursor-pointer grid-cols-[24px_minmax(0,1fr)_72px] grid-rows-2 rounded-lg border-0 border-none bg-white text-center align-middle"
  >
    <div
      class="col-start-1 row-span-2 row-start-1 w-[24px] rounded-l-lg bg-[#1c5298]"
    />
    <MenuInput
      v-if="selectedDishOne"
      v-model="selectedDishOne"
      class="col-start-2 row-span-1 row-start-1 px-4 pt-4"
    />
    <MenuInput
      v-if="selectedDishTwo"
      v-model="selectedDishTwo"
      class="col-start-2 row-span-1 row-start-2 px-4 pb-4 pt-2"
    />
    <div
      class="col-start-3 row-span-2 row-start-1 w-[72px] rounded-r-lg bg-[#1c5298]"
    />
  </div>
</template>

<script setup lang="ts">
import MenuInput from '@/components/menu/MenuInput.vue';
import { Ref, computed, onMounted, ref, watch } from 'vue';
import { Dish } from '@/stores/dishesStore';
import { MealDTO, DayDTO } from '@/interfaces/DayDTO';
import { useDishes } from '@/stores/dishesStore';

const { getDishArrayBySlugs } = useDishes();

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
      if (dish.parentId === null) {
        meals.push(dish.slug);
      }
    });
  } else if (selectedDishRef.value && selectedDishRef.value.length > 1) {
    selectedDishRef.value.forEach(dish => {
      if (dish.parentId !== null) {
        meals.push(dish.slug);
      }
    });
  }

  return meals;
}
</script>