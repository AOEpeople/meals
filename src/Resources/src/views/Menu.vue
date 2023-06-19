<template>
  <Weeks />
</template>

<script setup lang="ts">
import Weeks from '@/components/weeks/Weeks.vue';
import { useProgress } from '@marcoschulte/vue3-progress';
import { onMounted } from 'vue';

onMounted(() => {
  const progress = useProgress().start();

  progress.finish();
})
</script>