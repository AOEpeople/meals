<template>
  <MealsList />
</template>


<script setup>
import { usePrintableListData } from '@/api/getPrintableListData';
import { useProgress } from '@marcoschulte/vue3-progress';
import { useI18n } from 'vue-i18n';
import MealsList from '@/components/participations/MealsList.vue';


const { listData, error } = await usePrintableListData();
</script>