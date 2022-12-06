<template>
  <DashboardWeekTabs
    v-if="$screen.width < 1200"
    :weeks="weeks"
  />
  <DashboardWeekAll
    v-else
    :weeks="weeks"
  />
  <router-link to="/print/participations">
    <span>print</span>
  </router-link>
</template>

<script setup>
import {useProgress} from '@marcoschulte/vue3-progress'
import {dashboardStore} from '@/stores/dashboardStore'
import DashboardWeekTabs from "@/components/dashboard/DashboardWeekTabs.vue";
import DashboardWeekAll from "@/components/dashboard/DashboardWeekAll.vue";

const progress = useProgress().start()

await dashboardStore.fillStore()
const weeks = dashboardStore.getWeeks()

progress.finish()
</script>