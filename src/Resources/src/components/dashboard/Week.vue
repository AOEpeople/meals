<template>
  <div class="text-center">
    <h2 class="m-0 text-primary-2 print:hidden">
      {{ t('dashboard.' + index) }}
    </h2>
    <p class="description text-primary print:hidden">
      {{ (startLocale + ' - ' + endLocale).replaceAll(',', '') }}
    </p>
  </div>
  <div
    v-if="Object.keys(days).length === 0 || !dashboardStore.getWeek(weekID).isEnabled"
    class="mb-20 text-center text-[18px] tracking-[1.5px] text-[#acbdc7]"
  >
    <img
      class="mx-auto mb-8 mt-16 block"
      src="../../../images/empty_week.png"
      alt="no week"
    />
    <span>{{ t('dashboard.no_week') }}</span>
  </div>
  <div
    v-else
    id="weekly-menu"
    class="grid"
  >
    <Day
      v-for="(day, dayID, d_index) in days"
      :key="dayID"
      :weekID="String(weekID)"
      :dayID="String(dayID)"
      :index="d_index"
      class="mb-10 w-[98%] sm:w-3/4 print:mb-3"
    />
  </div>
</template>

<script setup lang="ts">
import Day from '@/components/dashboard/Day.vue';
import { useI18n } from 'vue-i18n';
import { useProgress } from '@marcoschulte/vue3-progress';
import { dashboardStore } from '@/stores/dashboardStore';
import { computed } from 'vue';

const progress = useProgress().start();
const { t, locale } = useI18n();

const props = defineProps<{
  weekID: number | string;
  index: number;
}>();

const week = dashboardStore.getWeek(props.weekID);
const days = dashboardStore.getDays(props.weekID);

const startDate = new Date(week.startDate.date);
const startLocale = computed(() =>
  startDate.toLocaleDateString(locale.value, { weekday: 'short', month: 'numeric', day: 'numeric' })
);

const endDate = new Date(week.endDate.date);
const endLocale = computed(() =>
  endDate.toLocaleDateString(locale.value, { weekday: 'short', month: 'numeric', day: 'numeric' })
);

setTimeout(function () {
  progress.finish();
}, 500);
</script>
