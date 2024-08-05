<template>
  <LazyTableRow
    class="border-b-2 border-gray-200"
    :min-height="40"
  >
    <td
      colspan="1"
      class="w-1/2 py-2"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ event.title }}
      </span>
    </td>
    <td
      colspan="1"
      class="w-[10%] text-[12px] xl:text-[18px]"
    >
      <div
        v-if="event.public === true"
        data-cy="checkIcon"
      >
        <CheckIcon class="aspect-square size-8" />
      </div>
      <div
        v-else
        data-cy="xIcon"
      >
        <XIcon class="aspect-square size-8" />
      </div>
    </td>
    <td
      colspan="1"
      class="w-2/5"
    >
      <EventsActions :event="event" />
    </td>
  </LazyTableRow>
</template>

<script setup lang="ts">
import LazyTableRow from '../misc/LazyTableRow.vue';
import { type Event } from '@/stores/eventsStore';
import { CheckIcon, XIcon } from '@heroicons/vue/outline';
import EventsActions from './EventsActions.vue';

defineProps<{
  event: Event;
}>();
</script>
