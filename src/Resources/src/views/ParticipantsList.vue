<template>
  <MealsList />
</template>


<script setup lang="ts">
import { usePrintableListData } from '@/api/getPrintableListData';
import { useProgress } from '@marcoschulte/vue3-progress';
import { useI18n } from 'vue-i18n';
import MealsList from '@/components/participations/MealsList.vue';



</script>