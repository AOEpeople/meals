<template>
  <MenuTableRow
    ref="row"
    :week-id="weekId"
    :class="editRow === true ? 'bg-green' : ''"
  >
    <template #firstCell>
      <td
        class="sticky left-0 cursor-pointer whitespace-nowrap border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] p-2 text-center text-[12px] hover:bg-slate-400"
        @click="editRow = !editRow"
      >
        {{ participant }}
      </td>
    </template>
    <template #dayMeals="{ dayId, meals }">
      <MenuTableData
        v-for="meal, mealIndex in meals"
        :key="`${meal.id}.${mealIndex}`"
        :edit="editRow"
        :participant="participant"
        :day-id="dayId"
        :week-id="weekId"
        :meal="meal"
      />
    </template>
  </MenuTableRow>
</template>

<script setup lang="ts">
import MenuTableRow from './MenuTableRow.vue';
import { ref, watch } from 'vue';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import MenuTableData from './MenuTableData.vue';

const editRow = ref<boolean>(false);
const row = ref<HTMLElement | null>(null);

defineProps<{
  weekId: number,
  participant: string
}>();

watch(
  editRow,
  () => (editRow.value === true) && useDetectClickOutside(row, () => editRow.value = false)
);
</script>