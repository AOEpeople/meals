<template>
  <div class="mx-auto flex h-auto w-3/4 rounded bg-white day-shadow w-max-screen-aoe">
    <div :class="[day.isLocked ? 'bg-[#80909F]' : 'bg-primary-2', 'flex relative justify-center w-[24px] rounded-l-[5px]']">
      <div v-if="!day.isLocked" id="icon" class="absolute w-[24px] text-center bottom-[1px] left-[2px] z-[2]">
        <GuestButton :dayID="dayID" :index="index"/>
      </div>
      <div class="grid weekday min-w-[200px]">
        <div id="dayLabel" class="h-[26px]">
          <span class="align-middle uppercase dayLabel">{{ weekday }}</span>
        </div>
      </div>
    </div>
    <div v-if="!emptyDay" class="flex flex-col flex-1 z-[1]">
      <Slots
          :weekID="weekID"
          :dayID="dayID"
      />
      <div
          v-for="(meal, mealID) in day.meals"
          :key="mealID"
          class="py-[13px] mx-[15px] border-b-[0.7px] last:border-b-0"
      >
        <VariationsData v-if="meal.variations"
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
        />
        <MealData v-else
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
        />
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
import {useI18n} from 'vue-i18n'
import VariationsData from '@/components/dashboard/VariationsData.vue'
import {computed} from 'vue'
import {dashboardStore} from "@/store/dashboardStore";
import GuestButton from "@/components/dashboard/GuestButton.vue";

const { t, locale } = useI18n()

const props = defineProps([
    'weekID',
    'dayID',
    'index'
])

const day = dashboardStore.getDay(props.weekID, props.dayID)
const date = new Date(Date.parse(day.date.date));
let weekday = computed(() => date.toLocaleDateString(locale.value, { weekday: 'long' }))
let emptyDay = Object.keys(day.meals).length === 0

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
  letter-spacing: 3px;
}

#icon {
  align-self: self-end;
}

#dayLabel {
  text-align-last: center;
}

</style>