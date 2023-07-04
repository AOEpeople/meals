<template>
  <Popover>
    <template #button="{ open }">
      <CalendarIcon
        class="h-5 w-5 cursor-pointer text-white"
      />
    </template>
    <template #panel="{ close }">
      <label class="flex flex-col gap-2 p-2">
        <span class="w-full self-center truncate text-left text-xs font-medium text-[#173D7A]">
          {{ t('menu.lockDate') }}
        </span>
        <input
          :value="getLockDateAsStrRepr(new Date(lockDate.date))"
          type="datetime-local"
          class="w-full rounded-full border-2 border-solid border-[#CAD6E1] px-4 py-2 text-center text-[14px] text-[#9CA3AF]"
          @change="event => lockDate.date = convertDateReprToLockDayFormat((event.target as HTMLInputElement).value)"
        >
      </label>
    </template>
  </Popover>
</template>

<script setup lang="ts">
import Popover from '../misc/Popover.vue';
import { CalendarIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';
import { DateTime } from '@/api/getDashboardData';

const { t } = useI18n();

defineProps<{
  lockDate: DateTime
}>();

// output format: 2023-07-19T12:00
function getLockDateAsStrRepr(date: Date) {
  return new Date(date.setTime(date.getTime() + (2*60*60*1000)))
    .toISOString()
    .substring(0, 16);
}

// output format: 2023-07-13 16:00:00.000000
function convertDateReprToLockDayFormat(date: string) {
  return date.replaceAll(' ', 'T') + ':00.000000';
}
</script>