<template>
  <div
    class="inline-flex divide-x-2"
  >
    <div
      v-for="(week, weekID, index) in weeks"
      :key="index"
      class="w-[585px] flex-initial"
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

defineProps(['weeks'])

</script>
