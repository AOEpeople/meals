<template>
  <p>{{ displayText }}</p>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  text: string;
}>();

const displayText = computed(() => `Hello ${props.text}`);
</script>
