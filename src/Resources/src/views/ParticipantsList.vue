<template>
  <ParticipationsTable />
  <MealOverview class="mt-10" />
</template>


<script setup lang="ts">
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';
import MealOverview from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { onMounted } from 'vue';

const { loadShowParticipations } = getShowParticipations();

onMounted(async () => {
  await loadShowParticipations();
});

</script>