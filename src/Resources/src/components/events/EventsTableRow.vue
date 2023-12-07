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
      {{ event.public }}
    </td>
    <td
      colspan="1"
      class="w-[40%]"
    >
      !! Hier könnten ihre Aktionen stehen !!
    </td>
  </LazyTableRow>
</template>

<script setup lang="ts">
import LazyTableRow from '../misc/LazyTableRow.vue';
import { Event } from '@/stores/eventsStore';

defineProps<{
  event: Event
}>();
</script>