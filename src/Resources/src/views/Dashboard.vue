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

<script setup>
import {useProgress} from '@marcoschulte/vue3-progress'
import {dashboardStore} from '@/stores/dashboardStore'
import DashboardWeekTabs from "@/components/dashboard/DashboardWeekTabs.vue";
import DashboardWeekAll from "@/components/dashboard/DashboardWeekAll.vue";
import PrintLink from "@/views/PrintLink.vue";
import {userDataStore} from "@/stores/userDataStore";

const progress = useProgress().start()

await dashboardStore.fillStore()
const weeks = dashboardStore.getWeeks()

const isAllowedToPrint = userDataStore.roleAllowsRoute('PrintableList')
progress.finish()
</script>