<template>
  <div class="mb-8 grid w-full grid-cols-[minmax(0,2fr)_minmax(0,1fr)] grid-rows-[minmax(0,1fr)_30px] gap-1">
    <h2 class="col-start-1 row-span-1 row-start-1 m-0 w-full self-center justify-self-start">
      {{ `${ t('menu.header')} #${calendarWeek} (${getFormattedDateRange()})` }}
    </h2>
    <ParticipantsLink
      class="col-start-1 row-start-2 justify-self-start"
    />
    <EnableWeek
      :week="week"
      class="col-start-2 row-start-1 justify-self-end"
    />
    <NotifyButton
      :week="week"
      class="col-start-2 row-start-2 justify-self-end"
    />
  </div>
</template>

<script setup lang="ts">
import { WeekDTO } from '@/interfaces/DayDTO';
import NotifyButton from '@/components/menu/NotifyButton.vue';
import EnableWeek from '@/components/menu/EnableWeek.vue';
import { useI18n } from 'vue-i18n';
import ParticipantsLink from '@/components/menu/ParticipantsLink.vue';

const { t, locale } = useI18n();

const props = defineProps<{
  week: WeekDTO,
  dateRange: string[],
  calendarWeek: number
}>();

function getFormattedDateRange() {
  const start = new Date(props.dateRange[0]).toLocaleDateString(locale.value, { day: '2-digit', month: '2-digit' });
  const end = new Date(props.dateRange[1]).toLocaleDateString(locale.value, { day: '2-digit', month: '2-digit' });
  return `${start} - ${end}`;
}
</script>