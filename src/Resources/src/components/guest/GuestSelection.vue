<template>
  <div class="m-2 border-2 bg-white">
    <div class="bg-primary-1 text-white">
      <span class="m-2">{{ dateString }}</span>
    </div>
    <div class="flex flex-1 flex-col">
      <GuestSlots :slots="invitation.slots" />
      <div
        v-for="(meal, mealId) in invitation.meals"
        :key="mealId"
        class="mx-[15px] border-b-[0.7px] py-[13px] last:border-b-0"
      >
        <GuestMeal
          v-if="!meal.variations"
          :meals="invitation.meals"
          :mealId="mealId"
        />
        <GuestVariation
          v-else
          :meal="meal"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import {useI18n} from 'vue-i18n'
import GuestSlots from '@/components/guest/GuestSlots.vue'
import GuestMeal from '@/components/guest/GuestMeal.vue'
import GuestVariation from '@/components/guest/GuestVariation.vue'
import {computed} from "vue";

const { locale } = useI18n()
const props = defineProps(['invitation'])

const date = new Date(Date.parse(props.invitation.date.date));
const dateString = computed(() => date.toLocaleDateString(locale.value, {weekday: 'short', year: 'numeric', month: 'numeric', day: 'numeric'}))
</script>
