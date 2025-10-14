<template>
  <Popover
    :translate-x-max="'-5%'"
    :translate-x-min="'0%'"
  >
    <template #button>
      <CalendarIcon
        class="size-5 cursor-pointer"
        :class="isStandardLockDate ? 'text-white' : 'text-highlight'"
      />
    </template>
    <template #panel="{ close }">
      <div class="flex flex-col gap-2">
        <div class="flex flex-row rounded-t-lg bg-primary-2 px-1 py-2">
          <span class="grow place-self-center font-bold uppercase leading-4 tracking-[3px] text-white">
            {{ t('menu.lock') }}
          </span>
          <XCircleIcon
            class="size-8 cursor-pointer self-end text-white transition-transform hover:scale-[120%] hover:text-[#FAFAFA]"
            @click="close()"
          />
        </div>
        <label class="flex flex-col gap-2 p-2">
          <span class="w-full self-center truncate text-left text-xs font-medium text-[#173D7A]">
            {{ t('menu.lockDate') }}
          </span>
          <VueDatePicker
            v-model="date"
            :enable-time-picker="true"
            :time-picker-inline="true"
            :clearable="false"
            :format="locale.includes('de') ? 'dd.MM.yyyy HH:mm' : 'MM/dd/yyyy HH:mm'"
            auto-apply
          />
        </label>
      </div>
    </template>
  </Popover>
</template>

<script setup lang="ts">
import Popover from '../misc/Popover.vue';
import { CalendarIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';
import { type DateTime } from '@/api/getDashboardData';
import { XCircleIcon } from '@heroicons/vue/solid';
import VueDatePicker from '@vuepic/vue-datepicker';
import { computed } from 'vue';

const { t, locale } = useI18n();

const props = defineProps<{
  lockDate: DateTime;
  isStandardLockDate: boolean;
}>();

const date = computed({
  get() {
    return props.lockDate.date;
  },
  set(value: Date | string) {
    if (typeof value !== 'string') {
      props.lockDate.date = convertDateReprToLockDayFormat(getLockDateAsStrRepr(value));
    }
  }
});

// output format: 2023-07-19T12:00
function getLockDateAsStrRepr(date: Date) {
  const timezoneMillisOffset = date.getTimezoneOffset() * 60 * 1000;
  date.setTime(date.getTime() - timezoneMillisOffset);
  return date.toISOString().substring(0, 16);
}

// output format: 2023-07-13 16:00:00.000000
function convertDateReprToLockDayFormat(date: string) {
  return date.replace(' ', 'T') + ':00.000000';
}
</script>
