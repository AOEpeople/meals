<template>
  <div
    class="day-shadow group grid cursor-pointer grid-cols-[24px_minmax(0,1fr)_72px] grid-rows-2 rounded-lg border-0 border-none bg-white text-center align-middle"
  >
    <div
      class="col-start-1 row-span-2 row-start-1 w-[24px] rounded-l-lg bg-[#1c5298]"
    />
    <MenuInput
      v-model="selectedDishOne"
      class="col-start-2 row-span-1 row-start-1"
    />
    <MenuInput
      v-model="selectedDishTwo"
      class="col-start-2 row-span-1 row-start-2"
    />
    <div
      class="col-start-3 row-span-2 row-start-1 w-[72px] rounded-r-lg bg-[#1c5298]"
    />
  </div>
</template>

<script setup lang="ts">
import MenuInput from '@/components/menu/MenuInput.vue';
import { computed, onMounted, ref, watch } from 'vue';
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

const selectedDishes = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

watch(
  () => selectedDishOne,
  () => {
    selectedDishes.value.meals[0].dishSlug = selectedDishOne.value[0].slug ?? null;
    console.log(`MealOne changed: ${selectedDishes.value.meals[0].dishSlug}`);
});

watch(
  () => selectedDishTwo,
  () => {
    selectedDishes.value.meals[1].dishSlug = selectedDishTwo.value[1].slug ?? null;
    console.log(`MealTwo changed: ${selectedDishes.value.meals[1].dishSlug}`);
});

onMounted(() => {
  console.log(`MealOne: ${props.modelValue.meals[0].dishSlug}, MealTwo: ${props.modelValue.meals[1].dishSlug}`);

  const dishes = getDishArrayBySlugs(props.modelValue.meals.map((meal: MealDTO) => meal.dishSlug));

  selectedDishOne.value = dishes[0];
  selectedDishTwo.value = dishes[1];
});
</script>