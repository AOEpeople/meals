<template>
  <div>{{ `Menu\n-for the Week with ID: ${week}-` }}</div>
</template>

<script setup lang="ts">
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted } from 'vue';

defineProps<{
  week: number
}>();

onMounted(() => {
  const progress = useProgress().start();

  progress.finish();
})
</script>