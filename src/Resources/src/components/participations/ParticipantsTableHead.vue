<template>
  <thead>
    <tr class="align-top">
      <th>Name</th>
      <th>Kombi</th>
      <th
        v-for="meal in mealsList"
        :key="meal"
      >
        {{ meal }}
      </th>
    </tr>
  </thead>
</template>

<script setup lang="ts">
import { getShowParticipations } from '@/api/getShowParticipations';
import { onMounted, ref } from 'vue';

const { loadShowParticipations, getListOfMeals } = getShowParticipations();

const mealsList = ref<string[]>([]);

onMounted(async () => {
  await loadShowParticipations();
  mealsList.value = getListOfMeals();
});

</script>