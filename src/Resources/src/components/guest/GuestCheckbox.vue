<template>
  <span
    data-cy="guest-checkbox"
    :class="[
      enabled ? 'bg-primary-3' : '',
      'size-[30px] cursor-pointer rounded-md border-[0.5px] border-[#ABABAB] xl:size-[20px]'
    ]"
    @click="handle"
  >
    <CheckIcon
      v-if="enabled"
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
import { ref } from 'vue';
import useEventsBus from '@/tools/eventBus';
import CombiModal from '@/components/dashboard/CombiModal.vue';
import { type Meal } from '@/api/getDashboardData';
import { type Dictionary } from '@/types/types';

const props = defineProps<{
  meals: Dictionary<Meal>;
  mealId: number | string;
}>();

const enabled = ref(false);
const open = ref(false);
const { emit } = useEventsBus();

const isCombiBox = (props.meals[props.mealId] as Meal).dishSlug === 'combined-dish';
let hasVariations = false;

Object.values(props.meals).forEach((meal) => ((meal as Meal).variations ? (hasVariations = true) : ''));

function handle() {
  // Is a combi meal
  if (isCombiBox) {
    // has variations
    if (hasVariations) {
      open.value = true;
    } else {
      let combiDishes = Object.values(props.meals)
        .filter((meal) => (meal as Meal).dishSlug !== 'combined-dish')
        .map((meal) => (meal as Meal).dishSlug);

      emit('guestChosenCombi', combiDishes);
      emit('guestChosenMeals', props.mealId);
      enabled.value = !enabled.value;
    }
  } else {
    emit('guestChosenMeals', props.mealId);
    enabled.value = !enabled.value;
  }
}

function handleCombiModal(dishes: string[]) {
  if (dishes !== undefined) {
    emit('guestChosenCombi', dishes);
    emit('guestChosenMeals', props.mealId);
    enabled.value = !enabled.value;
  }
  open.value = false;
}
</script>
