<template>
  <tabs v-model="selectedTab" class="justify-center mb-5">
    <tab
        v-for="(week, weekID, index) in weeks"
        :key="`t${weekID}`"
        :val="weekID"
        :label="t('dashboard.' + index)"
        :indicator="true"
        class="cursor-pointer"
    />
  </tabs>
  <tab-panels
      v-model="selectedTab"
      :animate="true"
      :swipeable="$screen.width <= 1200"
  >
    <tab-panel
        v-for="(week, weekID, index) in weeks"
        :key="`tp${weekID}`"
        :val="weekID"
    >
      <Week :weekID="weekID" :index="index"/>
    </tab-panel>
  </tab-panels>
</template>

<script setup>
import Week from '@/components/dashboard/Week.vue'
import { Tabs, Tab, TabPanels, TabPanel } from 'vue3-tabs'
import { useI18n } from "vue-i18n"
import {ref, watch} from "vue"
import { useProgress } from '@marcoschulte/vue3-progress'
import { dashboardStore } from '@/store/dashboardStore'

const progress = useProgress().start()

await dashboardStore.fillStore()
const weeks = dashboardStore.getWeeks()

const { t } = useI18n()

const selectedTab = ref(Object.keys(weeks)[0])

setTimeout(function () {
  progress.finish()
}, 500)
</script>