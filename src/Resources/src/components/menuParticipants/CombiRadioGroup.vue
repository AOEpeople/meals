<template>
  <RadioGroup
    v-model="mealId"
    class="my-2 rounded-md bg-white p-2 shadow-md"
  >
    <RadioGroupOption
      v-for="meal in meals"
      :key="`${meal.dishSlug}_${meal.mealId}`"
      v-slot="{ checked }"
      :value="meal.mealId"
      class="mb-2 focus:outline-none"
    >
      <div
        class="m-2 flex cursor-pointer flex-row gap-2 rounded-md border-2 border-transparent bg-white p-2 ring-2 focus:outline-none"
        :class="checked ? 'ring-indigo-600' : 'ring-gray-300 hover:bg-[#FAFAFA]'"
      >
        <span
          class="grow-[1] self-start justify-self-center text-primary"
        >
          {{ locale === 'en' ? getDishBySlug(meal.dishSlug).titleEn : getDishBySlug(meal.dishSlug).titleDe }}
        </span>
        <CheckCircleIcon
          v-if="checked === true"
          class="m-auto block h-6 w-6 self-end text-primary"
        />
      </div>
    </RadioGroupOption>
  </RadioGroup>
</template>

<script setup lang="ts">
import { MealDTO } from '@/interfaces/DayDTO';
import { useDishes } from '@/stores/dishesStore';
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { CheckCircleIcon } from '@heroicons/vue/solid';

const { getDishBySlug } = useDishes();
const { locale } = useI18n();

const props = defineProps<{
  modelValue: number,
  meals: MealDTO[]
}>();

const emit = defineEmits(['update:modelValue'])

const mealId = computed({
  get() {
    return props.modelValue;
  },
  set(mealId) {
    emit('update:modelValue', mealId);
  }
});

onMounted(() => {
  mealId.value = props.meals[0].mealId;
});
</script>