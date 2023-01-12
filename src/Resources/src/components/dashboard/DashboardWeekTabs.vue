<template>
  <tabs
    v-model="selectedTab"
    class="mb-5 justify-center"
  >
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

<style scoped>
.tabs {
  font-size: 14px;
  line-height: 20px;
  font-weight: 300;
  color: #173D7A;
}
.active-tab {
  border-bottom-color: #173D7A;
  border-bottom-width: 3px;
  margin-bottom: 0;
}
.active {
  border-bottom-color: #173D7A;
  border-bottom-width: 3px;
  margin-bottom: 0;
}
.tab {
  padding-right: 0;
  padding-left: 0;
}
</style>