<template>
  <div
    class="mb-8 grid w-full grid-cols-3 gap-3 sm:grid-rows-[minmax(0,1fr)_30px] min-[900px]:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] min-[900px]:gap-1"
  >
    <h2
      class="col-span-3 col-start-1 row-span-1 row-start-1 m-0 w-full self-center justify-self-start max-[380px]:text-[24px] min-[900px]:col-span-1"
    >
      {{ `${t('menu.header')} #${calendarWeek} (${getFormattedDateRange()})` }}
    </h2>
    <ParticipantsLink
      v-if="create === false"
      :week-id="week.id"
      class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
    />
    <EnableWeek
      :week="week"
      class="col-span-3 row-start-3 justify-self-center sm:col-span-1 sm:col-start-2 sm:row-start-2 sm:justify-self-end min-[900px]:row-start-1"
    />
    <NotifyButton
      :week="week"
      class="col-span-3 row-start-4 justify-self-center sm:col-span-1 sm:col-start-3 sm:row-start-2 sm:justify-self-end sm:text-end min-[900px]:col-start-2"
    />
  </div>
</template>

<script setup lang="ts">
import { type WeekDTO } from '@/interfaces/DayDTO';
import NotifyButton from '@/components/menu/NotifyButton.vue';
import EnableWeek from '@/components/menu/EnableWeek.vue';
import { useI18n } from 'vue-i18n';
import ParticipantsLink from '@/components/menu/ParticipantsLink.vue';

const { t, locale } = useI18n();

const props = defineProps<{
  week: WeekDTO;
  dateRange: Date[];
  calendarWeek: number;
  create: boolean;
}>();

function getFormattedDateRange() {
  const start = props.dateRange[0].toLocaleDateString(locale.value, { day: '2-digit', month: '2-digit' });
  const end = props.dateRange[1].toLocaleDateString(locale.value, { day: '2-digit', month: '2-digit' });
  return `${start} - ${end}`;
}
</script>
