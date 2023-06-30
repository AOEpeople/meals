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
          :value="date"
          type="datetime-local"
          class="w-full rounded-full border-2 border-solid border-[#CAD6E1] px-4 py-2 text-center text-[14px] text-[#9CA3AF]"
          @change="event => date = new Date((event.target as HTMLInputElement).value)"
        >
      </label>
    </template>
  </Popover>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import Popover from '../misc/Popover.vue';
import { CalendarIcon } from '@heroicons/vue/solid';
import { useI18n } from 'vue-i18n';
import { DateTime } from '@/api/getDashboardData';

const { t } = useI18n();

const props = defineProps<{
  lockDate: DateTime
}>();

// TODO: str repr
const date = ref<Date>(new Date());

watch(
  date,
  () => console.log(`Date changed: ${date.value}`)
);
</script>