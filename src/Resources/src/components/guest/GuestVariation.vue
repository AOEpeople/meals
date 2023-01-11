<template>
  <div class="mb-1">
    <span class="text-note font-bold uppercase tracking-[1px] text-primary">{{ parentTitle }}</span><br>
  </div>
  <div
    v-for="(variation, variationID, index) in meal.variations"
    :key="index"
    class="mb-1.5 flex w-auto flex-row justify-around gap-4 last:mb-0 xl:grid-cols-6"
  >
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center">
        <p class="description m-0 break-words font-light text-primary">
          {{ locale.substring(0, 2) === 'en' ? variation.title.en : variation.title.de }}
          <span
            v-if="variation.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-[1px] pl-1 pr-[3px] align-text-bottom text-[11px] font-bold uppercase leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span>
        </p>
      </div>
    </div>
    <div class="text-align-last flex flex-none basis-2/12 items-center justify-end">
      <ParticipationCounter
        :meal="variation"
        :mealCSS="mealCSS[variationID]"
      />
      <GuestCheckbox
        :meal="variation"
        :meals="meal.variations"
        :mealId="variationID"
      />
    </div>
  </div>
</template>

<script setup>
import {useI18n} from 'vue-i18n'
import {computed} from 'vue'
import GuestCheckbox from "@/components/guest/GuestCheckbox.vue";
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";

const { t, locale } = useI18n()

const props = defineProps([
  'meal'
])

let parentTitle = computed(() => locale.value.substring(0, 2) === 'en' ? props.meal.title.en : props.meal.title.de)

const mealCSS = computed(() => {
  let array = []
  for (const variationId in props.meal.variations) {
    array[variationId] = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
    switch (props.meal.variations[variationId].mealState) {
      case 'disabled':
        array[variationId] += 'bg-[#80909F]'
        break
      case 'open':
        array[variationId] += 'bg-primary-4'
        break
    }
  }
  return array
})

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>