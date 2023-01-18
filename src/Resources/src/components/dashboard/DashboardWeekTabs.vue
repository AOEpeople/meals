<template>
  <tabs
    v-model="selectedTab"
    class="tabs mb-5 justify-center gap-4"
  >
    <tab
      v-for="(week, weekID, index) in weeks"
      :key="`t${weekID}`"
      :val="weekID"
      :label="t('dashboard.' + index)"
      :indicator="true"
      :class="{'!border-b-[3px] !border-primary': weekID === selectedTab}"
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
      v-for="(week, weekID, index) in weeks"
      :key="`tp${weekID}`"
      :val="weekID"
    >
      <Week
        :weekID="weekID"
        :index="index"
      />
    </tab-panel>
  </tab-panels>
</template>
<script setup>
import { Tabs, Tab, TabPanels, TabPanel } from 'vue3-tabs';
import {useI18n} from "vue-i18n"
import {ref} from "vue"
import Week from "./Week.vue";

const props = defineProps(['weeks'])

const { t } = useI18n()

const selectedTab = ref(Object.keys(props.weeks)[0])
</script>
