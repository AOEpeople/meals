<template>
  <div class="flex mx-auto w-3/4 h-auto bg-white rounded day-shadow w-max-screen-aoe">
    <div :class="[disabled ? 'bg-[#80909F]' : 'bg-primary-2', 'flex justify-center w-[24px] rounded-l-[5px]']">
      <div id="icon" class="relative left-[425%] bottom-[2%]">
        <Icons icon="guest" box="0 0 13 13" class="w-[13px] h-[13px] fill-white"/>
      </div>
      <div class="grid weekday min-w-[200px]">
        <div id="dayLabel" class="mb-1">
          <span class="uppercase align-top dayLabel">{{ weekday }}</span>
        </div>
      </div>
    </div>
    <div v-if="!emptyDay" class="flex flex-col flex-1">
      <Slots :slots="day.slots" :disabled="disabled" :activeSlot="day.activeSlot"/>
      <div
          v-for="meal in day.meals"
          :key="meal.id"
          class="py-[13px] mx-[15px] border-b-[0.7px] last:border-b-0"
      >
        <VariationsData v-if="meal.variations" :meal="meal" :disabled="disabled"/>
        <MealData v-if="!meal.variations" :meal="meal" :disabled="disabled" />
      </div>
    </div>
    <div v-if="emptyDay" class="h-[134px]">
      <span class="relative top-[53px] description text-primary-1 ml-[23px]">{{ t('dashboard.no_service') }}</span>
    </div>
  </div>
</template>

<script setup>
import MealData from '@/components/dashboard/MealData.vue'
import Slots from '@/components/dashboard/Slots.vue'
import Icons from '@/components/misc/Icons.vue'
import { useI18n } from 'vue-i18n'
import VariationsData from '@/components/dashboard/VariationsData.vue'
import { computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps([
  'day',
])

const date = new Date(Date.parse(props.day.date.date));
let weekday = computed(() => date.toLocaleDateString(locale.value, { weekday: 'long' }))

let emptyDay = false
let disabled = true
if(props.day.meals.length === 0) {
  emptyDay = true
} else {
  disabled = props.day.meals[0].isLocked
}

</script>

<style scoped>
.day-shadow {
  box-shadow: 0 4px 0 hsla(0,0%,100%,.46),0 15px 35px rgba(216,225,233,.8);
}
.weekday {
  align-items: center;
  position: relative;
  top: 50%;
  transform: translateY(-50%) rotate(-90deg);
}
.dayLabel {
  color: #fff;
  font-family: Roboto, Helvetica, Arial, sans-serif;
  font-size: 11px;
  font-weight: 700;
  line-height: 16px;
  letter-spacing: 1.5px;
}

#icon {
  align-self: self-end;
}

#dayLabel {
  text-align-last: center;
}

</style>