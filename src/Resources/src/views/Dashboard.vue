<template>
  <tabs v-model="selectedTab" class="justify-center mb-5">

    <tab
        v-for="(week, index) in weeks"
        :key="`t${index}`"
        :val="week.id"
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
        v-for="(week, index) in weeks"
        :key="`tp${index}`"
        :val="week.id"
    >
      <Week :week="week" :index="index" />
    </tab-panel>
  </tab-panels>
</template>

<script setup>
import Week from '@/components/dashboard/Week.vue'
import { Tabs, Tab, TabPanels, TabPanel } from 'vue3-tabs';
import { useI18n } from "vue-i18n";
import { ref } from "vue";
import { useProgress } from '@marcoschulte/vue3-progress'
import { dashboardStore } from '@/store/dashboardStore'

const progress = useProgress().start()

await dashboardStore.fillStore()
const weeks = dashboardStore.getState().weeks

const { t } = useI18n();

const selectedTab = ref(weeks[0].id)

setTimeout(function () {
  progress.finish()
}, 500)
</script>