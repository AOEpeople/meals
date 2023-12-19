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
    v-if="isAllowedToPrint"
    class="mr-[27px] text-right"
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

const weeks = ref<Dictionary<Week>>({});
const isAllowedToPrint = ref(false);
const { fetchEvents } = useEvents();

onMounted(async () => {
  const progress = useProgress().start();

  await dashboardStore.fillStore();
  weeks.value = dashboardStore.getWeeks();
  await fetchEvents();

  isAllowedToPrint.value = userDataStore.roleAllowsRoute('PrintableList');
  progress.finish();
});
</script>