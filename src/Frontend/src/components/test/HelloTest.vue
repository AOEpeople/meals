<template>
  <h1>{{ header }}</h1>
  <TextTest :text="name" />
</template>

<script setup lang="ts">
import TextTest from './TextTest.vue';

defineProps<{
  name: string;
  header: string;
}>();
</script>
