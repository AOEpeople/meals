<template>
  <div class="day-shadow mx-auto flex h-auto max-w-[414px] content-center rounded bg-white sm:max-w-none">
    <div
      class="relative grid w-[24px] content-center justify-center  gap-2 rounded-l-[5px] py-[2px] text-center"
      :class="[day.isLocked ? 'bg-[#80909F]' : 'bg-primary-2 grid-rows-[24px_minmax(0,1fr)_24px]']"
    >
      <InformationButton
        v-if="!day.isLocked && !emptyDay && !guestData"
        :dayID="dayID"
        :index="index"
        class="row-start-1 h-[24px] w-[24px] p-1 text-center"
        @click="openModal"
      />
      <span
        class="row-start-2 rotate-180 place-self-center text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]"
        :class="day.isLocked || emptyDay || guestData ? 'py-[24px]' : 'pb-[0px]'"
      >
        {{ weekday }}
      </span>
      <GuestButton
        v-if="!day.isLocked && !emptyDay && !guestData"
        :dayID="dayID"
        :index="index"
        class="row-start-3 w-[24px] pl-[3px] text-center"
      />
      <ParticipantsListModal
        :openParticipantsModal="openParticipantsModal"
        :date="date"
        @close-dialog="closeParticipantsModal"
      />
    </div>
    <div
      v-if="!emptyDay"
      class="z-[1] flex min-w-[290px] flex-1 flex-col"
    >
      <div
        v-if="day.slotsEnabled"
        class="flex h-[54px] items-center border-b-[2px] px-[15px]"
      >
        <span class="text-primary mr-2 inline-block text-[11px] font-bold uppercase leading-4 tracking-[1.5px]">
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
      v-if="emptyDay"
      class="z-[1] h-[134px] min-w-[290px]"
    >
      <span class="description text-primary-1 relative top-[53px] ml-[23px]">{{ t('dashboard.no_service') }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { GuestDay } from '@/api/getInvitationData';
import GuestButton from '@/components/dashboard/GuestButton.vue';
import InformationButton from '@/components/dashboard/InformationButton.vue';
import MealData from '@/components/dashboard/MealData.vue';
import Slots from '@/components/dashboard/Slots.vue';
import VariationsData from '@/components/dashboard/VariationsData.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { translateWeekday } from 'tools/localeHelper';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ParticipantsListModal from './ParticipantsListModal.vue';

const { t, locale } = useI18n()
const openParticipantsModal = ref<boolean | null>(false);
const date = ref<string>();

const props = defineProps<{
  weekID?: string,
  dayID?: string,
  index?: number,
  guestData?: GuestDay | undefined
}>();

const day = props.guestData ? props.guestData.guestData : dashboardStore.getDay(props.weekID, props.dayID);
const weekday = computed(() => translateWeekday(day.date, locale));
const emptyDay = Object.keys(day.meals).length === 0;

async function closeParticipantsModal() {
  openParticipantsModal.value = false;
}
function openModal(){
  openParticipantsModal.value = true;
  // format date (2023-12-23) without time stamp
  date.value = day.date.date.split(' ')[0];
}
</script>

<style>
.day-shadow {
  box-shadow: 0 4px 0 hsla(0,0%,100%,.46),0 15px 35px rgba(216,225,233,.8);
}
</style>