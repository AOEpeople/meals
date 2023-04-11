
import MealOverview from '@/components/participations/MealOverview.vue';

<template>
  <MealsList />
  <ParticipationsTable />
  <MealOverview />
</template>


<script setup lang="ts">
import { usePrintableListData } from '@/api/getPrintableListData';
import { useI18n } from 'vue-i18n';
import MealsList from '@/components/participations/MealsList.vue';
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';
import MealOverview from '@/components/participations/MealOverview.vue';



</script>