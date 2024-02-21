<template>
  <DashboardWeekTabs
    v-if="$screen.width < 1200"
    :weeks="weeks"
  />
  <DashboardWeekAll
    v-else
    :weeks="weeks"
  />
  <PrintLink
    v-if="userDataStore.roleAllowsRoute('PrintableList')"
    class="mr-[27px] text-right print:hidden"
  />
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { dashboardStore } from '@/stores/dashboardStore';
import DashboardWeekTabs from '@/components/dashboard/DashboardWeekTabs.vue';
import DashboardWeekAll from '@/components/dashboard/DashboardWeekAll.vue';
import PrintLink from '@/views/PrintLink.vue';
import { userDataStore } from '@/stores/userDataStore';
import { onMounted, ref } from 'vue';
import { Dictionary } from 'types/types';
import { Week } from '@/api/getDashboardData';
import { useEvents } from '@/stores/eventsStore';
import useEventsBus from '@/tools/eventBus';
import { EventParticipationResponse } from '@/api/postJoinEvent';

const weeks = ref<Dictionary<Week>>({});
const { fetchEvents } = useEvents();
const { receive } = useEventsBus();

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
