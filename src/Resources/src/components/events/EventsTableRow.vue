<template>
  <LazyTableRow
    class="border-b-2 border-gray-200"
    :min-height="40"
  >
    <td
      colspan="1"
      class="w-[50%] py-2"
    >
      <span class="text-[12px] xl:text-[18px]">
        {{ event.title }}
      </span>
    </td>
    <td
      colspan="1"
      class="w-[10%] text-[12px] xl:text-[18px]"
    >
      <CheckIcon
        v-if="event.public === true"
        class="aspect-square h-8 w-8"
      />
      <XIcon
        v-else
        class="aspect-square h-8 w-8"
      />
    </td>
    <td
      colspan="1"
      class="w-[40%]"
    >
      <EventsActions
        :event="event"
      />
    </td>
  </LazyTableRow>
</template>

<script setup lang="ts">
import LazyTableRow from '../misc/LazyTableRow.vue';
import { Event } from '@/stores/eventsStore';
import { CheckIcon, XIcon } from '@heroicons/vue/outline';
import EventsActions from './EventsActions.vue';

defineProps<{
  event: Event
}>();
</script>