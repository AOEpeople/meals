<template>
  <div class="m-2 bg-white border-2">
    <div class="bg-primary-1 text-white">
      <span class="m-2">{{ dateString }}</span>
    </div>
    <div class="flex flex-col flex-1">
      <GuestSlots :slots="invitation.slots"/>
      <div v-for="(meal, mealId) in invitation.meals"
           class="py-[13px] mx-[15px] border-b-[0.7px] last:border-b-0"
           :key="mealId"
      >
        <GuestMeal v-if="!meal.variations" :meal="meal"/>
        <GuestVariation v-else :meal="meal"/>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useI18n} from 'vue-i18n'
import GuestSlots from '@/components/guest/GuestSlots.vue'
import GuestMeal from '@/components/guest/GuestMeal.vue'
import GuestVariation from '@/components/guest/GuestVariation.vue'

const { t, locale } = useI18n()
const props = defineProps(['invitation'])

const date = new Date(Date.parse(props.invitation.date.date));
const dateString = date.toLocaleDateString(locale.value, {weekday: 'short', year: 'numeric', month: 'numeric', day: 'numeric'})

let chosenMeals = []

function processMeal(slug) {
  const exists = chosenMeals.indexOf(slug);
  if (exists !== -1) {
    chosenMeals.slice(exists, 1)
  } else {
    chosenMeals.push(slug)
  }
  console.log(chosenMeals)
}
</script>

<style scoped>

</style>