<template>
  <div class="text-center">
    <h2 class="m-0">{{ t('dashboard.' + index) }}</h2>
    <p class="description text-primary">{{t('Mon')}} 04.10. - {{ t('Fri') }} 08.10.</p>
  </div>
  <div class="grid" id="weekly-menu">
    <Day v-for="(day, dayID) in days"
         :weekID="weekID"
         :dayID="dayID"
         :key="dayID"
         class="mb-[2.5rem]"
    />
  </div>
</template>

<script setup>
import Day from '@/components/dashboard/Day.vue'
import { useI18n } from "vue-i18n"
import { useProgress } from '@marcoschulte/vue3-progress'
import {dashboardStore} from "@/store/dashboardStore"

const progress = useProgress().start()

const props = defineProps(['weekID', 'index'])
const days = dashboardStore.getDays(props.weekID)

const { t } = useI18n()

setTimeout(function () {
  progress.finish()
}, 500)
</script>
