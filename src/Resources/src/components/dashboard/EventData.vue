<template>
  <div
    class="flex w-full flex-row items-center px-[15px] py-[13px]"
  >
    <div class="grid w-full grid-cols-[24px_minmax(0,1fr)] gap-1">
      <EventIcon
        class="w-[24px] self-center"
      />
      <span class="mr-[5px] inline-block grow self-center break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note">
        {{ getEventById(day.event.eventId)?.title }}
      </span>
    </div>
    <ParticipationCounter
      class="justify-self-end"
      :limit="0"
      :mealCSS="new Date(day.date.date) > new Date() ? 'bg-primary-4' : 'bg-[#80909F]'"
    >
      {{ day.event?.participations }}
    </ParticipationCounter>
    <CheckBox
      class="justify-self-end"
      :isActive="new Date(day.date.date) > new Date()"
      :isChecked="day.event?.isParticipating ?? false"
    />
  </div>
</template>

<script setup lang="ts">
import { Day } from '@/api/getDashboardData';
import { useEvents } from '@/stores/eventsStore';
import ParticipationCounter from '../menuCard/ParticipationCounter.vue';
import CheckBox from '../misc/CheckBox.vue';
import EventIcon from '../misc/EventIcon.vue';

defineProps<{
  day: Day
}>();

const { getEventById } = useEvents();
</script>