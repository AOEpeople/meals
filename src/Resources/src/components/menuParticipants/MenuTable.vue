<template>
  <table
    v-if="loaded === true"
    class="border-separate border-spacing-0"  
  >
    <MenuTableHead :week-id="weekId" />
    <MenuTableBody :week-id="weekId" />
  </table>
</template>

<script setup lang="ts">
import { useWeeks } from '@/stores/weeksStore';
import { useParticipations } from '@/stores/participationsStore';
import { useDishes } from '@/stores/dishesStore';
import { onMounted, ref } from 'vue';
import MenuTableBody from '@/components/menuParticipants/MenuTableBody.vue';
import MenuTableHead from './MenuTableHead.vue';
import { useProgress } from '@marcoschulte/vue3-progress';


const props = defineProps<{
  weekId: number
}>();

const loaded = ref<boolean>(false);

const { fetchParticipations } = useParticipations(props.weekId);
const { getWeekById, isWeek, fetchWeeks } = useWeeks();
const { DishesState, fetchDishes } = useDishes();

onMounted(async () => {
  const progress = useProgress().start();

  if (isWeek(getWeekById(props.weekId)) === false) {
    await fetchWeeks();
  }
  if (DishesState.dishes.length === 0) {
    await fetchDishes();
  }

  await fetchParticipations();

  progress.finish();
  loaded.value = true;
});
</script>