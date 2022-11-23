<template>
  <div class="divide-x-2 inline-flex">
    <div
      v-for="(week, weekID, index) in weeks"
      class=""
    >
      <Week
        :weekID="weekID"
        :index="index"
      />
    </div>
  </div>
</template>

<script setup>
import Week from "./Week.vue";

const props = defineProps(['weeks'])


</script>

<style scoped>

</style>