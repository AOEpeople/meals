<template>
  <div
    class="mx-auto grid h-auto min-h-[153px] max-w-[414px] grid-cols-[auto_minmax(0,1fr)] grid-rows-[minmax(0,1fr)_auto] rounded bg-white shadow-day sm:max-w-none"
  >
    <div
      class="relative col-span-1 col-start-1 row-span-2 row-start-1 grid w-[24px] grid-rows-[24px_minmax(0,1fr)_24px] justify-center gap-2 rounded-l-[5px] py-[2px] print:bg-primary-2"
      :class="[day?.isLocked || !day?.isEnabled || (emptyDay && !isEventDay) ? 'bg-[#80909F]' : 'bg-primary-2']"
    >
      <InformationButton
        v-if="!emptyDay"
        :dayID="dayID"
        :index="index"
        class="hover: row-start-1 size-[24px] cursor-pointer p-1 text-center"
        @click="openModal"
      />
      <span
        class="row-start-2 rotate-180 place-self-center text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]"
        :class="day?.isLocked || emptyDay ? '' : 'pb-[0px]'"
      >
        {{ weekday }}
      </span>
      <GuestButton
        v-if="!day?.isLocked && !emptyDay && day?.isEnabled && dayID"
        :dayID="dayID"
        :index="index ?? 0"
        :invitation="Invitation.MEAL"
        :icon-white="true"
        class="row-start-3 w-[24px] pl-[3px] text-center"
      />
      <ParticipantsListModal
        v-if="openParticipantsModal"
        :openParticipantsModal="openParticipantsModal"
        :date="date"
        :weekday="weekday"
        :dateString="dateString"
        @close-dialog="closeParticipantsModal"
      />
    </div>
    <div
      v-if="!emptyDay && day?.isEnabled"
      class="z-[1] col-start-2 row-start-1 flex min-w-[290px] flex-1 flex-col"
    >
      <div
        v-if="day?.slotsEnabled"
        class="flex h-[54px] items-center border-b-2 px-[15px] print:hidden"
      >
        <span class="mr-2 inline-block text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
          {{ t('dashboard.slot.timeslot') }}
        </span>
        <Slots
          :dayID="dayID"
          :day="day"
          class="sm:w-64"
        />
      </div>
      <div
        v-for="(meal, mealID) in day?.meals"
        :key="mealID"
        class="mx-[15px] border-b-[0.7px] last:border-b-0"
        :class="
          isEventDay
            ? 'pb-[13px] pt-[13px] last:pb-0 last:pt-[21px] print:pt-2 print:last:pb-2'
            : 'py-[13px] print:py-2'
        "
      >
        <VariationsData
          v-if="meal.variations && weekID && dayID && day"
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
          :day="day"
          :meal="meal"
        />
        <MealData
          v-else-if="day"
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
          :day="day"
          :meal="meal"
        />
      </div>
    </div>
    <div
      v-if="emptyDay || !day?.isEnabled"
      class="z-[1] col-start-2 row-start-1 grid h-full min-w-[290px] items-center"
      :class="isEventDay ? 'pt-[24px]' : ''"
    >
      <span class="description relative ml-[15px] text-primary-1">
        {{ t('dashboard.no_service') }}
      </span>
    </div>
    <EventData
      v-if="isEventDay && day && dayID"
      class="col-start-2 row-start-2 print:hidden"
      :day="day"
      :dayId="dayID"
    />
  </div>
</template>

<script setup lang="ts">
import GuestButton from '@/components/dashboard/GuestButton.vue';
import InformationButton from '@/components/dashboard/InformationButton.vue';
import MealData from '@/components/dashboard/MealData.vue';
import Slots from '@/components/dashboard/Slots.vue';
import VariationsData from '@/components/dashboard/VariationsData.vue';
import { Invitation } from '@/enums/Invitation';
import { dashboardStore } from '@/stores/dashboardStore';
import { translateWeekday } from '@/tools/localeHelper';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EventData from './EventData.vue';
import ParticipantsListModal from './ParticipantsListModal.vue';

const { t, locale } = useI18n();
const openParticipantsModal = ref<boolean | null>(false);

const props = defineProps<{
  weekID?: string;
  dayID?: string;
  index?: number;
}>();

const day = dashboardStore.getDay(props.weekID ?? -1, props.dayID ?? -1);
const weekday = computed(() => {
  if (day !== undefined) {
    return translateWeekday(day.date, locale);
  }
  return 'unknown';
});
const emptyDay = Object.keys(day?.meals ?? {}).length === 0;
const isEventDay = day?.events !== null;
// const eventId = day?.events[Object.keys(day.events)[0][0]].eventId !== null;
const date = computed(() => {
  if (day === null || day === undefined) {
    return '';
  }
  // format date (2023-12-23) without time stamp
  return day.date.date.split(' ')[0];
});
const dateString = computed(() => {
  if (day) {
    return new Date(Date.parse(day.date.date)).toLocaleDateString(locale.value, {
      weekday: 'long',
      month: 'numeric',
      day: 'numeric'
    });
  }
  return new Date().toLocaleDateString(locale.value, {
    weekday: 'long',
    month: 'numeric',
    day: 'numeric'
  });
});

async function closeParticipantsModal() {
  openParticipantsModal.value = false;
}

function openModal() {
  openParticipantsModal.value = true;
}
</script>

<style>
.day-shadow {
  box-shadow:
    0 4px 0 hsla(0, 0%, 100%, 0.46),
    0 15px 35px rgba(216, 225, 233, 0.8);
}
</style>
