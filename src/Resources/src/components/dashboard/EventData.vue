<template>
  <div
    class="grid w-full grid-cols-1 grid-rows-[minmax(0,2fr)_auto_minmax(0,1fr)]"
  >
    <BannerSpacer>
      <EventIcon
        class="w-[20px] self-center"
        :size="20"
      />
      <span
        class="text-[14px] font-bold leading-[20px] tracking-[0.5px] text-primary-1"
      >
        {{ t('event.event') }}
      </span>
      <EventIcon
        class="w-[20px] self-center"
        :size="20"
      />
    </BannerSpacer>
    <div
      class="flex w-full flex-row items-center px-[15px]"
    >
      <span class="mr-[5px] inline-block grow self-center break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note">
        {{ getEventById(day.event.eventId)?.title }}
      </span>
      <EventPopup
        class="justify-self-end"
        :event-title="getEventById(day.event.eventId)?.title"
        :date="day.date.date"
      />
      <ParticipationCounter
        class="mx-[5px] justify-self-end"
        :limit="0"
        :mealCSS="!day.isLocked ? 'bg-primary-4' : 'bg-[#80909F]'"
      >
        {{ day.event?.participations }}
      </ParticipationCounter>
      <CheckBox
        class="justify-self-end"
        :isActive="new Date(day.date.date) > new Date()"
        :isChecked="day.event?.isParticipating ?? false"
        @click="day.event?.isParticipating === false ? joinEvent(day.date.date) : leaveEvent(day.date.date)"
      />
    </div>
    <div />
  </div>
</template>

<script setup lang="ts">
import { Day } from '@/api/getDashboardData';
import { useEvents } from '@/stores/eventsStore';
import ParticipationCounter from '../menuCard/ParticipationCounter.vue';
import CheckBox from '../misc/CheckBox.vue';
import EventIcon from '../misc/EventIcon.vue';
import BannerSpacer from '../misc/BannerSpacer.vue';
import { useI18n } from 'vue-i18n';
import EventPopup from '@/components/eventParticipation/EventPopup.vue';

defineProps<{
  day: Day
}>();

const { t } = useI18n();
const { getEventById, joinEvent, leaveEvent } = useEvents();
</script>