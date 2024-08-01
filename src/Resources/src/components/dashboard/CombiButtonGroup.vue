<template>
  <RadioGroup
    v-model="selected"
    :disabled="!meal.variations"
  >
    <RadioGroupLabel class="sr-only"> Combi Meal Selection </RadioGroupLabel>
    <div class="-space-y-px rounded-md bg-white">
      <RadioGroupOption
        v-for="(dish, index) in dishes"
        :key="dish.id"
        v-slot="{ checked, active }"
        as="template"
        :value="dish.slug"
      >
        <div
          :class="[
            index === 0 ? 'rounded-t-md' : '',
            index === dishes.length - 1 ? 'rounded-b-md' : '',
            checked ? 'z-10 border-indigo-200 bg-indigo-50' : 'border-gray-200',
            'relative flex cursor-pointer border p-4 focus:outline-none'
          ]"
        >
          <span
            :class="[
              checked ? 'border-transparent bg-indigo-600' : 'border-gray-300 bg-white',
              active ? 'ring-2 ring-indigo-500 ring-offset-2' : '',
              'mt-0.5 flex size-4 shrink-0 cursor-pointer items-center justify-center rounded-full border'
            ]"
            aria-hidden="true"
          >
            <span class="size-1.5 rounded-full bg-white" />
          </span>
          <span class="ml-3 flex flex-col">
            <RadioGroupLabel
              as="span"
              :class="[checked ? 'text-indigo-900' : 'text-gray-900', 'block text-sm font-medium']"
            >
              {{ dish.title }}
            </RadioGroupLabel>
            <RadioGroupDescription
              as="span"
              :class="[checked ? 'text-indigo-700' : 'text-gray-500', 'block text-sm']"
            >
              {{ dish.description }}
            </RadioGroupDescription>
          </span>
        </div>
      </RadioGroupOption>
    </div>
  </RadioGroup>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { RadioGroup, RadioGroupDescription, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { type Meal } from '@/api/getDashboardData';

interface DishInfo {
  id: string | number,
  title: string,
  description: string,
  slug: string
}

const props = defineProps<{
  weekID: number | string;
  dayID: number | string;
  mealID: number | string;
  meal: Meal;
}>();

const meal = props.meal ?? dashboardStore.getMeal(props.weekID, props.dayID, props.mealID);
const emit = defineEmits(['addEntry', 'removeEntry']);
const selected = ref();
let dishes: DishInfo[] = [];
let oldSlug = '';

if (meal.variations) {
  for (const variationID in meal.variations) {
    dishes.push({
      id: parseInt(String(variationID)),
      title: meal.title.en,
      description: meal.variations[variationID].title.en,
      slug: meal.variations[variationID].dishSlug ?? ''
    });
  }
  selected.value = dishes[0];
} else {
  dishes.push({
    id: parseInt(String(props.mealID)),
    title: meal.title.en,
    description: meal.description?.en ?? '',
    slug: meal.dishSlug ?? ''
  });
  selected.value = meal.dishSlug;
  emit('addEntry', meal.dishSlug);
}

watch(selected, () => {
  if (oldSlug !== '') {
    emit('removeEntry', oldSlug);
  }
  emit('addEntry', selected.value);
  oldSlug = selected.value;
});
</script>
