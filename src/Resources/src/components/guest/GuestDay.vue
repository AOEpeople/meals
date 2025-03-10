<template>
  <div
    class="mx-auto grid h-auto min-h-[153px] max-w-[414px] grid-cols-[auto_minmax(0,1fr)] grid-rows-[minmax(0,1fr)_auto] rounded bg-white shadow-day sm:max-w-none"
  >
    <div
      class="relative col-span-1 col-start-1 row-span-2 row-start-1 grid w-[24px] grid-rows-[24px_minmax(0,1fr)_24px] justify-center gap-2 rounded-l-[5px] py-[2px] print:bg-primary-2"
      :class="[guestData.isLocked || !guestData.isEnabled || emptyDay ? 'bg-[#80909F]' : 'bg-primary-2']"
    >
      <span
        class="row-start-2 rotate-180 place-self-center text-center text-[11px] font-bold uppercase leading-4 tracking-[3px] text-white [writing-mode:vertical-lr]"
        :class="guestData.isLocked || emptyDay ? '' : 'pb-[0px]'"
      >
        {{ weekday }}
      </span>
    </div>
    <div
      v-if="!emptyDay && guestData.isEnabled"
      class="z-[1] col-start-2 row-start-1 flex min-w-[290px] flex-1 flex-col"
    >
      <div
        v-if="guestData.slotsEnabled"
        class="flex h-[54px] items-center border-b-2 px-[15px] print:hidden"
      >
        <span class="mr-2 inline-block text-[11px] font-bold uppercase leading-4 tracking-[1.5px] text-primary">
          {{ t('dashboard.slot.timeslot') }}
        </span>
        <Slots
          :dayID="undefined"
          :day="guestData"
          class="sm:w-64"
        />
      </div>
      <div
        v-for="(meal, mealID) in guestData.meals"
        :key="mealID"
        class="mx-[15px] border-b-[0.7px] py-[13px] last:border-b-0 print:py-2"
      >
        <GuestVariation
          v-if="meal.variations && Object.values(meal.variations).length > 0"
          :meal="meal"
          :meal-id="mealID"
          :chosen-meals="chosenMeals"
        />
        <GuestMeal
          v-else
          :meals="guestData.meals"
          :mealId="mealID"
          :chosen-meals="chosenMeals"
        />
      </div>
    </div>
    <div
      v-if="emptyDay || !guestData.isEnabled"
      class="z-[1] col-start-2 row-start-1 grid h-full min-w-[290px] items-center"
    >
      <span class="description relative ml-[15px] text-primary-1">
        {{ t('dashboard.no_service') }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { type GuestDay } from '@/api/getInvitationData';
import { useI18n } from 'vue-i18n';
import { translateWeekday } from '@/tools/localeHelper';
import { computed } from 'vue';
import Slots from '@/components/dashboard/Slots.vue';
import GuestMeal from '@/components/guest/GuestMeal.vue';
import GuestVariation from '@/components/guest/GuestVariation.vue';

const { t, locale } = useI18n();

const props = defineProps<{
  guestData: GuestDay;
  chosenMeals: string[];
}>();

const weekday = computed(() => translateWeekday(props.guestData.date, locale));
const emptyDay = Object.keys(props.guestData.meals).length === 0;
</script>
