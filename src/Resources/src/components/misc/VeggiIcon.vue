<template>
  <div
    class="my-auto grid aspect-square content-center"
  >
    <img
      v-if="diet === Diet.VEGETARIAN"
      class="object-cover"
      src="../../../images/vegetarian.png"
      alt="vegetarian"
    />
    <img
      v-else-if="diet === Diet.VEGAN"
      class="object-cover"
      src="../../../images/vegan.png"
      alt="vegan"
    />
  </div>
</template>

<script setup lang="ts">
import { Diet } from '@/enums/Diet';

defineProps<{
  diet: Diet
}>();
</script>