<template>
  <div class="day-shadow w-max-screen-aoe mx-auto flex h-auto rounded bg-white">
    <div :class="[day.isLocked ? 'bg-[#80909F]' : 'bg-primary-2', 'flex relative justify-center w-[24px] rounded-l-[5px]']">
      <div
        v-if="!day.isLocked"
        class="absolute bottom-[1px] left-[2px] z-[2] w-[24px] text-center"
      >
        <GuestButton
          v-if="!guestData"
          :dayID="dayID"
          :index="index"
        />
      </div>
      <div class="relative top-1/2 grid min-w-[200px] -translate-y-1/2 -rotate-90 items-center">
        <div class="text-center">
          <span class="align-middle text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white">{{ weekday }}</span>
        </div>
      </div>
    </div>
    <div
      v-if="!emptyDay"
      class="z-[1] flex min-w-[390px] flex-1 flex-col"
    >
      <div class="flex h-[54px] items-center border-b-[2px] px-[15px]">
        <span class="mr-2 inline-block text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
          {{ t('dashboard.slot.timeslot') }}
        </span>
        <Slots
          v-if="day.slotsEnabled"
          :dayID="dayID"
          :day="day"
        />
      </div>
      <div
        v-for="(meal, mealID) in day.meals"
        :key="mealID"
        class="mx-[15px] border-b-[0.7px] py-[13px] last:border-b-0"
      >
        <VariationsData
          v-if="meal.variations"
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
          :day="day"
          :meal="meal"
        />
        <MealData
          v-else
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
          :day="day"
          :meal="meal"
        />
      </div>
    </div>
    <div
      v-if="emptyDay"
      class="h-[134px]"
    >
      <span class="description relative top-[53px] ml-[23px] text-primary-1">{{ t('dashboard.no_service') }}</span>
    </div>
  </div>
</template>

<script setup>
import MealData from '@/components/dashboard/MealData.vue'
import Slots from '@/components/dashboard/Slots.vue'
import {useI18n} from 'vue-i18n'
import VariationsData from '@/components/dashboard/VariationsData.vue'
import {computed} from 'vue'
import {dashboardStore} from "@/stores/dashboardStore";
import GuestButton from "@/components/dashboard/GuestButton.vue";
import {translateWeekday} from "tools/localeHelper";

const { t, locale } = useI18n()

const props = defineProps([
    'weekID',
    'dayID',
    'index',
    'guestData'
])

const day = props.guestData ? props.guestData : dashboardStore.getDay(props.weekID, props.dayID)
const weekday = computed(() => translateWeekday(day.date, locale))
const emptyDay = Object.keys(day.meals).length === 0

</script>
