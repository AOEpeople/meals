<template>
  <div class="text-center">
    <h2 class="m-0">
      {{ t('dashboard.' + index) }}
    </h2>
    <p class="description text-primary">
      {{ (start + ' - ' + end).replaceAll(',', '') }}
    </p>
  </div>
  <div
    id="weekly-menu"
    class="grid"
  >
    <Day
      v-for="(day, dayID, d_index) in days"
      :key="dayID"
      :weekID="weekID"
      :dayID="dayID"
      :index="index + '_' + d_index"
      class="mb-[2.5rem]"
    />
  </div>
</template>

<script setup>
import Day from '@/components/dashboard/Day.vue'
import { useI18n } from "vue-i18n"
import { useProgress } from '@marcoschulte/vue3-progress'
import {dashboardStore} from "@/stores/dashboardStore"

const progress = useProgress().start()
const { t, locale } = useI18n()

const props = defineProps(['weekID', 'index'])
const week = dashboardStore.getWeek(props.weekID)
const days = dashboardStore.getDays(props.weekID)

let start = new Date(week.startDate.date)
start = start.toLocaleDateString(locale.value, { weekday: 'short', month: 'numeric', day: 'numeric' })

let end = new Date(week.endDate.date)
end = end.toLocaleDateString(locale.value, { weekday: 'short', month: 'numeric', day: 'numeric' })

setTimeout(function () {
  progress.finish()
}, 500)
</script>
