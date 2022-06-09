<template>
  <div class="w-max-screen-aoe w-3/4 mx-auto">
    <h2 class="w-max-screen-aoe w-3/4 mx-auto">Lunch at AOE</h2>
    <p class="w-max-screen-aoe w-3/4 mx-auto">As our guest, we would like to invite you to lunch in the AOE Eatery. To order a meal, register no later than one day before your visit to AOE with your personal information and choose the desired dish. Your order will be forwarded to our cooks.</p>
  </div>
  <Day :day="wednesday" />
  <form>
    <div class="grid grid-rows-4 mx-auto mt-10">
      <input type="text" placeholder="Your first name">
      <input type="text" placeholder="Your last name">
      <input type="text" placeholder="Your company">
      <button type="submit" class="btn-primary">
        Submitsvewvewvewrvtv
      </button>
    </div>
  </form>
</template>

<script setup>
import Day from '@/components/content/Day.vue'

var meal1 = {
  name: 'Meal1',
  description: '',
  participations: 42,
  active: false,
}

var meal2 = {
  name: 'Meal2',
  description: 'testing',
  participations: 25,
  active: true,
}

var kombi = {
  name: 'Kombi',
  description: '',
  participations: 3,
  active: false,
}
var wednesday = {
  name: 'Wednesday',
  meal1: meal1,
  meal2: meal2,
  kombi: kombi,
}

</script>

<style scoped>
input {
  @apply bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12
}
</style>