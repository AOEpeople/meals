<template>
  <div class="inline-flex divide-x-2 print:block print:h-screen">
    <div
      v-for="(weekID, index) in Object.keys(weeks)"
      :key="index"
      class="w-[585px] flex-initial print:mx-auto print:w-full print:last:hidden"
    >
      <WeekComp
        :weekID="weekID"
        :index="index"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { type Dictionary } from '@/types/types';
import WeekComp from './Week.vue';
import { type Week } from '@/api/getDashboardData';

defineProps<{
  weeks: Dictionary<Week>;
}>();
</script>
