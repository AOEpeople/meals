<template>
  <div>
    <MenuInput
      v-model="selectedDishOne"
    />
    <MenuInput />
  </div>
</template>

<script setup lang="ts">
import MenuInput from '@/components/menu/MenuInput.vue';
import { Dish } from '@/stores/dishesStore';
import { ref } from 'vue';

const selectedDishOne = ref<Dish | null>(null);
const selectedDishTwo = ref<Dish | null>(null);
</script>