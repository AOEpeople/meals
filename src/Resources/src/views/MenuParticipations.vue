<template>
  <div>
    <h2>Menu Participations</h2>
    <h4>{{ `For Week with ID: ${week}` }}</h4>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  week: string;
}>();

</script>