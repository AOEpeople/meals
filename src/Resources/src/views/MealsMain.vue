<template>
  <div class="text-center">
    <h2 class="m-0">{{ t('currentWeek') }}</h2>
    <p class="description text-primary">{{t('Mon')}} 04.10. - {{ t('Fri') }} 08.10.</p>
  </div>
  <div class="grid gap-y-10" id="weekly-menu">
    <Day v-for="day in week"
         :day="day"
         :key="day.name"
    />
  </div>
</template>

<script setup>
import Day from '@/components/mealsMain/Day.vue'
import { useI18n } from "vue-i18n";

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

var week = [
  monday,
  thuesday,
  wednesday,
  thursday,
  friday,
]

</script>
