<template>
<tabs v-model="selectedTab" class="justify-center mb-5">

  <tab
      v-for="(week, index) in weeks"
      :key="`t${index}`"
      :val="week.number"
      :label="t(week.label)"
      indicator="true" />

</tabs>
<tab-panels
    v-model="selectedTab"
    :animate="true"
    :swipeable="$screen.width <= 1200"
>
  <tab-panel
      v-for="(week, i) in weeks"
      :key="`tp${i}`"
      :val="week.number"
  >
    <Week :week="week" />
  </tab-panel>
</tab-panels>
</template>

<script setup>
import Week from '@/components/dashboard/Week.vue'
import { Tabs, Tab, TabPanels, TabPanel } from 'vue3-tabs';
import { useI18n } from "vue-i18n";
import {ref} from "vue";

const { t } = useI18n();

var meal1 = {
  title_en: 'Asian carrot soup',
  title_de: 'Asiatische Karottensuppe',
  description_en: 'With coconut milk, ginger and lemongrass',
  description_de: 'Mit Kokusnuss Milch, Ingwer und Zitronengras',
  participations: 28,
  active: true,
}

var meal2 = {
  title_de: 'Aubergine "Griechischer Art"',
  title_en: 'Eggplants "Greek style"',
  description_de: 'aus dem Ofen mit Tzatziki + Salat Büffet',
  description_en: 'Oven-made with tsatsiki + salad buffet',
  participations: 54,
  active: false,
}

var kombi = {
  title_de: 'Kombi Gericht',
  title_en: 'Kombi Meal',
  description_de: '',
  description_en: '',
  participations: 3,
  active: false,
}

var monday = {
  name: 'Monday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}
var thuesday = {
  name: 'Tuesday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}
var wednesday = {
  name: 'Wednesday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}
var thursday = {
  name: 'Thursday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}
var friday = {
  name: 'Friday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}

var week1 = {
  number: 0,
  label: 'dashboard.current',
  value: [
    monday,
    thuesday,
    wednesday,
    thursday,
    friday,
  ]}

var week2 = {
  number: 1,
  label: 'dashboard.next',
  value: [
    monday,
    thuesday,
    wednesday,
    thursday,
    friday,
  ]}

var weeks = [week1, week2];
const selectedTab = ref(weeks[0].number)
</script>