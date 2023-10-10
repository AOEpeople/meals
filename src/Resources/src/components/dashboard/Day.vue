<template>
  <div class="day-shadow mx-auto flex h-auto max-w-[414px] rounded bg-white sm:max-w-none">
    <div
      class="relative grid w-[24px] justify-center gap-2 rounded-l-[5px] py-[2px]"
      :class="[day.isLocked || !day.isEnabled || emptyDay ? 'bg-[#80909F]' : 'bg-primary-2', !day.isLocked && !emptyDay && !guestData ? 'grid-rows-[minmax(0,1fr)_24px]' : '']"
    >
      <span
        class="row-start-1 rotate-180 place-self-center text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]"
        :class="day.isLocked || emptyDay || guestData ? 'py-[24px]' : 'pb-[24px]'"
      >
        {{ weekday }}
      </span>
      <GuestButton
        v-if="!day.isLocked && !emptyDay && !guestData && day.isEnabled"
        :dayID="dayID"
        :index="index"
        class="row-start-2 w-[24px] pl-[3px] text-center"
      />
    </div>
    <div
      v-if="!emptyDay && day.isEnabled"
      class="z-[1] flex min-w-[290px] flex-1 flex-col"
    >
      <div
        v-if="day.slotsEnabled"
        class="flex h-[54px] items-center border-b-[2px] px-[15px]"
      >
        <span class="mr-2 inline-block text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
          {{ t('dashboard.slot.timeslot') }}
        </span>
        <Slots
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
      v-if="emptyDay || !day.isEnabled"
      class="z-[1] grid h-full min-w-[290px] items-center"
    >
      <span class="description relative ml-[23px] text-primary-1">{{ t('dashboard.no_service') }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import MealData from '@/components/dashboard/MealData.vue';
import Slots from '@/components/dashboard/Slots.vue';
import { useI18n } from 'vue-i18n';
import VariationsData from '@/components/dashboard/VariationsData.vue';
import { computed } from 'vue';
import { dashboardStore } from '@/stores/dashboardStore';
import GuestButton from '@/components/dashboard/GuestButton.vue';
import { translateWeekday } from 'tools/localeHelper';
import { GuestDay } from '@/api/getInvitationData';

const { t, locale } = useI18n()

const props = defineProps<{
  weekID?: string,
  dayID?: string,
  index?: number,
  guestData?: GuestDay | undefined
}>();

const day = props.guestData ? props.guestData.guestData : dashboardStore.getDay(props.weekID, props.dayID);
const weekday = computed(() => translateWeekday(day.date, locale));
const emptyDay = Object.keys(day.meals).length === 0;

</script>

<style>
.day-shadow {
  box-shadow: 0 4px 0 hsla(0,0%,100%,.46),0 15px 35px rgba(216,225,233,.8);
}
</style>
