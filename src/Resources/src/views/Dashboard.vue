<template>
  <DashboardWeekTabs
    v-if="width < 1200"
    :weeks="weeks"
  />
  <DashboardWeekAll
    v-else
    :weeks="weeks"
  />
  <PrintLink
    v-if="showPrinkLink"
    class="mr-[27px] text-right print:hidden"
  />
</template>

<script setup lang="ts">
import { useWindowSize } from '@vueuse/core';
import { useProgress } from '@marcoschulte/vue3-progress';
import { dashboardStore } from '@/stores/dashboardStore';
import DashboardWeekTabs from '@/components/dashboard/DashboardWeekTabs.vue';
import DashboardWeekAll from '@/components/dashboard/DashboardWeekAll.vue';
import PrintLink from '@/views/PrintLink.vue';
import { userDataStore } from '@/stores/userDataStore';
import { computed, onMounted, ref } from 'vue';
import type { Dictionary } from '@/types/types';
import type { Week } from '@/api/getDashboardData';
import { useEvents } from '@/stores/eventsStore';
import useEventsBus from '@/tools/eventBus';
import type { EventParticipationResponse } from '@/api/postJoinEvent';

const { width } = useWindowSize();

const weeks = ref<Dictionary<Week>>({});
const { fetchEvents } = useEvents();
const { receive } = useEventsBus();

const showPrinkLink = computed(() => {
  return (
    userDataStore.roleAllowsRoute('PrintableList') &&
    Object.keys(weeks.value).length > 0 &&
    Object.values(weeks.value)[0].isEnabled &&
    dashboardStore.getToday()?.isEnabled
  );
});

onMounted(async () => {
  const progress = useProgress().start();

  await dashboardStore.fillStore();
  weeks.value = dashboardStore.getWeeks();
  await fetchEvents();

  progress.finish();
});

receive<EventParticipationResponse>('eventParticipationUpdate', (participationUpdate) => {
  dashboardStore.setIsParticipatingEvent(participationUpdate.participationId, participationUpdate.isParticipating);
});
</script>
