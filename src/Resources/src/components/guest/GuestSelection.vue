<template>
  <div class="m-2 border-2 bg-white">
    <div class="text-white bg-primary-1">
      <span class="m-2">{{ dateString }}</span>
    </div>
    <div class="flex flex-1 flex-col">
      <GuestSlots :slots="invitation.slots" />
      <div v-for="(meal, mealId) in invitation.meals"
           class="py-[13px] mx-[15px] border-b-[0.7px] last:border-b-0"
           :key="mealId"
      >
          <GuestMeal v-if="!meal.variations" :meals="invitation.meals" :mealId="mealId"/>
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
</script>
