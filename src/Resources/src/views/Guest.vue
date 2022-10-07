<template>
  <div v-if="error === false">
    <div>
      <h2>Lunch at AOE</h2>
      <p>As our guest, we would like to invite you to lunch in the AOE Eatery. To order a meal, register no later than one day before your visit to AOE with your personal information and choose the desired dish. Your order will be forwarded to our cooks.</p>
    </div>
    <GuestSelection :invitation="invitation"/>
    <form>
      <div class="grid grid-rows-4 mx-auto mt-10">
        <input type="text" placeholder="Your first name">
        <input type="text" placeholder="Your last name">
        <input type="text" placeholder="Your company">
        <button type="submit" class="btn-primary">
          {{ error }}
        </button>
      </div>
    </form>
  </div>
  <div v-if="error === true">
    <span>error</span>
  </div>
</template>

<script setup>
import { useProgress } from '@marcoschulte/vue3-progress'
import { useRoute } from 'vue-router'
import { useInvitationData } from '@/hooks/getInvitationData'
import GuestSelection from '@/components/guest/GuestSelection.vue';
const progress = useProgress().start()

const route = useRoute()
const { invitation, error } = await useInvitationData(route.params.hash)
console.log(invitation)
setTimeout(function () {
    progress.finish()
}, 500)
</script>

<style scoped>
input {
  @apply bg-white border-[2px] border-solid border-[#CAD6E1] rounded-[100px] h-12
}
</style>