<template>
  <RadioGroup v-model="selected" :disabled="!meal.variations">
    <RadioGroupLabel class="sr-only">Combi Meal Selection</RadioGroupLabel>
    <div class="bg-white rounded-md -space-y-px">
      <RadioGroupOption as="template" v-for="(dish, index) in dishes"
        :key="dish.id"
        :value="dish.slug"
        v-slot="{ checked, active }"
      >
        <div :class="[index === 0 ? 'rounded-tl-md rounded-tr-md' : '', index === dishes.length - 1 ? 'rounded-bl-md rounded-br-md' : '', checked ? 'bg-indigo-50 border-indigo-200 z-10' : 'border-gray-200', 'relative border p-4 flex cursor-pointer focus:outline-none']">
          <span :class="[checked ? 'bg-indigo-600 border-transparent' : 'bg-white border-gray-300', active ? 'ring-2 ring-offset-2 ring-indigo-500' : '', 'h-4 w-4 mt-0.5 cursor-pointer shrink-0 rounded-full border flex items-center justify-center']" aria-hidden="true">
            <span class="rounded-full bg-white w-1.5 h-1.5" />
          </span>
          <span class="ml-3 flex flex-col">
            <RadioGroupLabel as="span" :class="[checked ? 'text-indigo-900' : 'text-gray-900', 'block text-sm font-medium']">
              {{ dish.title }}
            </RadioGroupLabel>
            <RadioGroupDescription as="span" :class="[checked ? 'text-indigo-700' : 'text-gray-500', 'block text-sm']">
              {{ dish.description }}
            </RadioGroupDescription>
          </span>
        </div>
      </RadioGroupOption>
    </div>
  </RadioGroup>
</template>

<script setup>
import { ref, watch } from 'vue'
import { RadioGroup, RadioGroupDescription, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import {dashboardStore} from "@/store/dashboardStore";

const props = defineProps([
    'weekID',
    'dayID',
    'mealID',
    'meal'
])

const meal = props.meal !== undefined ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)
const emit = defineEmits(['addEntry', 'removeEntry'])
const selected = ref()
let dishes = []
let oldSlug = ''

if(meal.variations) {
  for (const variationID in meal.variations) {
    dishes.push({
      id: variationID,
      title: meal.title.en,
      description: meal.variations[variationID].title.en,
      slug: meal.variations[variationID].dishSlug,
    })
  }
  selected.value = dishes[0]
} else {
  dishes.push({
    id: props.mealID,
    title: meal.title.en,
    description: meal.description.en,
    slug: meal.dishSlug,
  })
  selected.value = meal.dishSlug
  emit('addEntry', meal.dishSlug)
}

watch(selected, () => {
  if(oldSlug !== ''){
    emit('removeEntry', oldSlug)
  }
  emit('addEntry', selected.value)
  oldSlug = selected.value
})
</script>