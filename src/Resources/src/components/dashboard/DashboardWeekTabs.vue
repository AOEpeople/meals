<template>
  <tabs
    v-model="selectedTab"
    class="tabs mb-5 justify-center gap-4"
  >
    <tab
      v-for="(weekID, index) in Object.keys(weeks)"
      :key="`t${String(weekID)}`"
      :val="weekID"
      :label="t('dashboard.' + index)"
      :indicator="true"
      :class="{ '!border-b-[3px] !border-primary': weekID === selectedTab }"
      class="cursor-pointer pb-2"
    >
      <div
        :class="[weekID === selectedTab ? 'text-primary' : 'text-[#36404A]']"
        class="tab text-note"
      >
        {{ t('dashboard.' + index) }}
      </div>
    </tab>
  </tabs>
  <tab-panels
    v-model="selectedTab"
    :animate="true"
    :swipeable="true"
  >
    <tab-panel
      v-for="(weekID, index) in Object.keys(weeks)"
      :key="`tp${String(weekID)}`"
      :val="weekID"
    >
      <WeekComp
        :weekID="String(weekID)"
        :index="index"
      />
    </tab-panel>
  </tab-panels>
</template>
<script setup lang="ts">
import { Tabs, Tab, TabPanels, TabPanel } from 'vue3-tabs';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import WeekComp from './Week.vue';
import { type Dictionary } from '@/types/types';
import { type Week } from '@/api/getDashboardData';

const props = defineProps<{
  weeks: Dictionary<Week>;
}>();

const { t } = useI18n();

const selectedTab = ref(Object.keys(props.weeks)[0]);
</script>
