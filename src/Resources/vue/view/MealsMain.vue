<template>
  <div class="text-center">
    <h2 class="m-0">Current Week</h2>
    <p class="description text-primary">Mon 04.10. - Fri 08.10.</p>
  </div>
  <div class="grid gap-y-10" id="weekly-menu">
    <Day v-for="day in week"
         :day="day"
         :key="day.name"
    />
  </div>
</template>

<script setup>
import Day from '@/components/content/Day.vue'

var meal1 = {
  name: 'Asian carrot soup',
  description: 'With coconut milk, ginger and lemongrass',
  participations: 28,
  active: true,
}

var meal2 = {
  name: 'Eggplants "Greek style"',
  description: 'Oven-made with tsatsiki + salad buffet',
  participations: 54,
  active: false,
}

var kombi = {
  name: 'Kombi',
  description: '',
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
  name: 'Thuesday',
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
