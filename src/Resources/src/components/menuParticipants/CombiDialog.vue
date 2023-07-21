<template>
  <Dialog
    :open="openCombi === mealId"
    class="relative z-50"
    @close="closeCombi(false)"
  >
    <div
      class="fixed flex items-center justify-center p-4"
    >
      <DialogPanel
        class="relative overflow-hidden rounded-lg bg-white p-4 text-left shadow-xl sm:my-8 sm:p-6"
      >
        <DialogTitle>
          {{ t('combiModal.title') }}
        </DialogTitle>
        <CombiRadioGroup
          v-for="meals, key, index in menuOfTheDay"
          :key="`${key}_${index}`"
          v-model="selectedCombi[index]"
          :meals="meals"
        />
        <div class="flex flex-row gap-2">
          <CreateButton
            :managed="true"
            :btn-text="t('combiModal.submit')"
            class="cursor-pointer"
            @click="closeCombi(true)"
          />
          <CancelButton
            :btn-text="t('combiModal.cancel')"
            class="cursor-pointer"
            @click="closeCombi(false)"
          />
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { useI18n } from 'vue-i18n';
import CombiRadioGroup from './CombiRadioGroup.vue';
import { Dictionary } from 'types/types';
import { useWeeks } from '@/stores/weeksStore';
import { MealDTO } from '@/interfaces/DayDTO';
import CancelButton from '../misc/CancelButton.vue';
import CreateButton from '@/components/misc/CreateButton.vue';

const { t } = useI18n();
const { getMenuDay } = useWeeks();

const props = defineProps<{
  openCombi: number | null,
  mealId: number,
  dayId: string,
  weekId: number
}>();

const emit = defineEmits(['closeDialog']);

const menuOfTheDay = computed(() => {
  Object.entries(getMenuDay(props.weekId, props.dayId).meals).filter(meals => meals[1].find(meal => meal.dishSlug !== 'combined-dish') !== undefined)
  let menuDict: Dictionary<MealDTO[]> = {};
  for (const [key, meals] of Object.entries(getMenuDay(props.weekId, props.dayId).meals)) {
    if (meals.find(meal => meal.dishSlug !== 'combined-dish') !== undefined) {
      menuDict[key] = meals;
    }
  }
  return menuDict;
});

const selectedCombi = ref<number[]>([-1, -1]);

function closeCombi(doSubmit: boolean) {
  console.log(`Selected meals to post: ${selectedCombi.value.join(', ')}, with combi-meal: ${props.mealId}`);
  if (doSubmit === true && selectedCombi.value.includes(-1) === false) {
    console.log('submitting choosen combi-meals')
    emit('closeDialog', selectedCombi.value);
  } else {
    emit('closeDialog', null);
  }
}
</script>