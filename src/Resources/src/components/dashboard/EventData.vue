<template>
  <div class="grid w-full grid-cols-1 grid-rows-[repeat(2,minmax(0,1fr))]">
    <BannerSpacer
      class="!h-fit"
    >
      <EventIcon
        class="w-[20px] self-center"
        :size="20"
      />
      <span class="text-[14px] font-bold leading-[20px] tracking-[0.5px] text-primary-1">
        {{ t('event.event') }}
      </span>
      <EventIcon
        class="w-[20px] self-center"
        :size="20"
      />
    </BannerSpacer>
    <div
      v-for="(event, key) in day.events"
      :key="key"
      :class="
      twoEvents
      ? 'pb-2'
      :'pb-0'
      "
    >
      <div class="flex w-full flex-col items-center px-[15px] min-[380px]:flex-row">
        <span
          class="inline-block grow self-start break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 max-[380px]:basis-9/12 min-[380px]:self-center min-[380px]:text-note"
        >
        {{ event.event.title }}
        </span>
        <div class="flex w-fit flex-row items-center gap-1 self-end justify-self-end max-[380px]:basis-3/12">
          <GuestButton
            v-if="!day.isLocked && day.events.isPublic"
            :dayID="dayId"
            :index="0"
            :invitation="Invitation.EVENT"
            :icon-white="false"
            class="col-start-1 w-[24px] text-center"
          />
          <EventPopup
            :event-title="event.event.title"
            :date="day.date.date"
          />
          <ParticipationCounter
            :limit="0"
            :mealCSS="!day.isLocked ? 'bg-primary-4' : 'bg-[#80909F]'"
          >
            {{ event?.participations }}
          </ParticipationCounter>
          <CheckBox
            :isActive="new Date(day.date.date) > new Date()"
            :isChecked="event?.isParticipating ?? false"
            @click="handleClick"
          />
        </div>
      </div>
      <div />
    </div>
  </div>
</template>

<script setup lang="ts">
import { type Day } from '@/api/getDashboardData';
import { useEvents } from '@/stores/eventsStore';
import ParticipationCounter from '../menuCard/ParticipationCounter.vue';
import CheckBox from '../misc/CheckBox.vue';
import EventIcon from '../misc/EventIcon.vue';
import BannerSpacer from '../misc/BannerSpacer.vue';
import { useI18n } from 'vue-i18n';
import EventPopup from '@/components/eventParticipation/EventPopup.vue';
import GuestButton from './GuestButton.vue';
import { Invitation } from '@/enums/Invitation';
import { useLockRequests } from '@/services/useLockRequests';

const props = defineProps<{
  day: Day;
  dayId: string;
}>();

const twoEvents: boolean = props.day?.events !== undefined && Object.keys(props.day?.events).length === 2;
console.log('Events am Tag '+JSON.stringify(props.day.events));
const { t } = useI18n();
const { joinEvent, leaveEvent } = useEvents();
const { addLock, isLocked, removeLock } = useLockRequests();

async function handleClick() {
  if (isLocked(props.dayId) === true || isEventPast() === true) {
    return;
  }
  addLock(props.dayId);
  if (props.day.events.EventParticipation?.isParticipating === false) {
    await joinEvent(props.day.date.date, props.day.events.EventParticipation.eventId);
  } else {
    await leaveEvent(props.day.date.date, props.day.events.EventParticipation.eventId);
  }
  removeLock(props.dayId);
}

function isEventPast() {
  const eventLockDate = new Date(props.day.date.date).setHours(17, 0);
  const now = Date.now();
  const isPast = eventLockDate < now;
  return isPast;
}
</script>

